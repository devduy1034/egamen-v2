<?php

namespace LARAVEL\Services;

use Illuminate\Support\Facades\Http;
use LARAVEL\DatabaseCore\Schema\Blueprint;
use LARAVEL\Models\ProductModel;
use LARAVEL\Models\PropertiesListModel;

class ProductSemanticSearchService
{
    private static bool $schemaChecked = false;

    public function searchProductScores(string $query, string $lang, array $filters = []): array
    {
        if (!$this->isEnabled() || trim($query) === '') {
            return [];
        }

        try {
            $this->ensureEmbeddingsTableExists();
            $this->syncProductEmbeddings($lang);

            $queryEmbedding = $this->createEmbedding($this->buildQueryDocument($query, $filters));
            if (empty($queryEmbedding)) {
                return [];
            }

            return $this->rankIndexedProducts($queryEmbedding, $lang, $filters);
        } catch (\Throwable $e) {
            return [];
        }
    }

    private function isEnabled(): bool
    {
        $enabled = config('ai_search.enabled', true);
        $apiKey = trim((string) config('type.openai.key', ''));

        return (bool) $enabled && $apiKey !== '';
    }

    private function ensureEmbeddingsTableExists(): void
    {
        if (self::$schemaChecked) {
            return;
        }

        $schema = app('capsule')->schema();
        if (!$schema->hasTable('product_embeddings')) {
            $schema->create('product_embeddings', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('product_id');
                $table->string('locale', 10)->default('vi');
                $table->string('embedding_model', 120);
                $table->unsignedInteger('embedding_dimensions')->default(384);
                $table->double('embedding_norm')->nullable();
                $table->unsignedBigInteger('product_updated_at')->nullable();
                $table->string('source_hash', 64);
                $table->longText('source_text')->nullable();
                $table->longText('embedding');
                $table->timestamp('synced_at')->nullable();
                $table->timestamps();

                $table->unique(['product_id', 'locale'], 'product_embeddings_product_locale_unique');
                $table->index(['locale', 'embedding_model', 'embedding_dimensions'], 'product_embeddings_lookup_index');
            });
        }

        self::$schemaChecked = true;
    }

    private function syncProductEmbeddings(string $lang): void
    {
        $indexedCount = (int) app('db')->table('product_embeddings')
            ->where('locale', $lang)
            ->where('embedding_model', $this->embeddingModel())
            ->where('embedding_dimensions', $this->embeddingDimensions())
            ->count();

        $limit = $indexedCount === 0
            ? $this->bootstrapBatchSize()
            : $this->syncBatchSize();

        if ($limit <= 0) {
            return;
        }

        $products = $this->fetchProductsNeedingSync($lang, $limit);
        if ($products->isEmpty()) {
            return;
        }

        $payloads = [];
        foreach ($products as $product) {
            $document = $this->buildProductDocument($product, $lang);
            if ($document === '') {
                continue;
            }

            $payloads[] = [
                'product' => $product,
                'document' => $document,
                'source_hash' => hash('sha256', $document),
            ];
        }

        if (empty($payloads)) {
            return;
        }

        foreach (array_chunk($payloads, max(1, $this->embeddingRequestBatchSize())) as $chunk) {
            $documents = array_values(array_map(static function ($item) {
                return $item['document'];
            }, $chunk));

            $embeddings = $this->createEmbeddings($documents);
            if (count($embeddings) !== count($chunk)) {
                continue;
            }

            $now = date('Y-m-d H:i:s');
            $rows = [];

            foreach ($chunk as $index => $item) {
                $embedding = $embeddings[$index] ?? null;
                if (!is_array($embedding) || empty($embedding)) {
                    continue;
                }

                $rows[] = [
                    'product_id' => (int) $item['product']['id'],
                    'locale' => $lang,
                    'embedding_model' => $this->embeddingModel(),
                    'embedding_dimensions' => $this->embeddingDimensions(),
                    'embedding_norm' => $this->vectorNorm($embedding),
                    'product_updated_at' => $this->productUpdatedTimestamp($item['product']),
                    'source_hash' => $item['source_hash'],
                    'source_text' => $item['document'],
                    'embedding' => json_encode($embedding),
                    'synced_at' => $now,
                    'updated_at' => $now,
                    'created_at' => $now,
                ];
            }

            if (!empty($rows)) {
                app('db')->table('product_embeddings')->upsert(
                    $rows,
                    ['product_id', 'locale'],
                    ['embedding_model', 'embedding_dimensions', 'embedding_norm', 'product_updated_at', 'source_hash', 'source_text', 'embedding', 'synced_at', 'updated_at']
                );
            }
        }
    }

    private function fetchProductsNeedingSync(string $lang, int $limit)
    {
        $ids = app('db')->table('product as p')
            ->leftJoin('product_embeddings as pe', function ($join) use ($lang) {
                $join->on('pe.product_id', '=', 'p.id')
                    ->where('pe.locale', '=', $lang)
                    ->where('pe.embedding_model', '=', $this->embeddingModel())
                    ->where('pe.embedding_dimensions', '=', $this->embeddingDimensions());
            })
            ->where('p.type', 'san-pham')
            ->whereRaw("FIND_IN_SET(?, p.status)", ['hienthi'])
            ->where(function ($query) {
                $query->whereNull('pe.product_id')
                    ->orWhereRaw('COALESCE(pe.product_updated_at, 0) < COALESCE(p.date_updated, p.date_created, 0)');
            })
            ->orderByRaw('CASE WHEN pe.product_id IS NULL THEN 0 ELSE 1 END ASC')
            ->orderByRaw('COALESCE(p.date_updated, p.date_created, 0) DESC')
            ->orderBy('p.id', 'desc')
            ->limit($limit)
            ->pluck('p.id')
            ->map(static function ($value) {
                return (int) $value;
            })
            ->filter()
            ->values()
            ->all();

        if (empty($ids)) {
            return collect();
        }

        $products = ProductModel::select(
            'id',
            'type',
            'name' . $lang,
            'desc' . $lang,
            'slug' . $lang,
            'properties',
            'id_brand',
            'id_list',
            'id_cat',
            'id_item',
            'id_sub',
            'date_created',
            'date_updated'
        )->with([
            'getBrand',
            'getCategoryList',
            'getCategoryCat',
            'getCategoryItem',
            'getCategorySub',
        ])->whereIn('id', $ids)->get()->keyBy('id');

        return collect($ids)->map(function ($id) use ($products) {
            return $products->get($id);
        })->filter()->values();
    }

    private function rankIndexedProducts(array $queryEmbedding, string $lang, array $filters): array
    {
        $queryNorm = $this->vectorNorm($queryEmbedding);
        if ($queryNorm <= 0.0) {
            return [];
        }

        $rows = app('db')->table('product_embeddings as pe')
            ->join('product as p', 'p.id', '=', 'pe.product_id')
            ->select('pe.product_id', 'pe.embedding', 'pe.embedding_norm')
            ->where('pe.locale', $lang)
            ->where('pe.embedding_model', $this->embeddingModel())
            ->where('pe.embedding_dimensions', $this->embeddingDimensions())
            ->where('p.type', 'san-pham')
            ->whereRaw("FIND_IN_SET(?, p.status)", ['hienthi']);

        $this->applyPriceFilter($rows, $filters, 'CASE WHEN p.sale_price > 0 THEN p.sale_price ELSE p.regular_price END');

        $scores = [];
        foreach ($rows->get() as $row) {
            $storedEmbedding = json_decode((string) ($row->embedding ?? ''), true);
            if (!is_array($storedEmbedding) || empty($storedEmbedding)) {
                continue;
            }

            $score = $this->cosineSimilarity($queryEmbedding, $storedEmbedding, $queryNorm, (float) ($row->embedding_norm ?? 0));
            if ($score < $this->minSemanticScore()) {
                continue;
            }

            $scores[(int) $row->product_id] = $score;
        }

        arsort($scores);

        return array_slice($scores, 0, max(1, $this->semanticResultLimit()), true);
    }

    private function buildQueryDocument(string $query, array $filters): string
    {
        $parts = [trim($query)];

        foreach (['category', 'color', 'size', 'style', 'material', 'occasion'] as $key) {
            $value = trim((string) ($filters[$key] ?? ''));
            if ($value !== '') {
                $parts[] = $key . ': ' . $value;
            }
        }

        if (($filters['min_price'] ?? null) !== null) {
            $parts[] = 'min_price: ' . (int) $filters['min_price'];
        }

        if (($filters['max_price'] ?? null) !== null) {
            $parts[] = 'max_price: ' . (int) $filters['max_price'];
        }

        return implode("\n", array_values(array_filter($parts)));
    }

    private function buildProductDocument($product, string $lang): string
    {
        $name = trim((string) ($product['name' . $lang] ?? ''));
        $description = $this->cleanText((string) ($product['desc' . $lang] ?? ''));
        $slug = trim((string) ($product['slug' . $lang] ?? ''));
        $brand = trim((string) data_get($product, 'getBrand.name' . $lang, ''));
        $categories = array_values(array_filter([
            trim((string) data_get($product, 'getCategoryList.name' . $lang, '')),
            trim((string) data_get($product, 'getCategoryCat.name' . $lang, '')),
            trim((string) data_get($product, 'getCategoryItem.name' . $lang, '')),
            trim((string) data_get($product, 'getCategorySub.name' . $lang, '')),
        ]));
        $propertyTags = $this->resolveProductPropertyTags($product, $lang);

        $parts = array_values(array_filter([
            $name,
            $brand !== '' ? 'brand: ' . $brand : '',
            !empty($categories) ? 'categories: ' . implode(', ', array_unique($categories)) : '',
            !empty($propertyTags) ? 'attributes: ' . implode(', ', $propertyTags) : '',
            $description !== '' ? 'description: ' . $description : '',
            $slug !== '' ? 'slug: ' . $slug : '',
        ]));

        return implode("\n", $parts);
    }

    private function resolveProductPropertyTags($product, string $lang): array
    {
        $propertyIds = array_values(array_unique(array_filter(array_map('intval', explode(',', (string) ($product['properties'] ?? ''))))));
        if (empty($propertyIds)) {
            return [];
        }

        try {
            $query = PropertiesListModel::select('type', 'id', 'name' . $lang)
                ->where('type', 'san-pham')
                ->whereRaw("FIND_IN_SET(?,status)", ['cart']);

            if (!empty(config('type.categoriesProperties'))) {
                $query->whereRaw("FIND_IN_SET(?,id_list)", [(string) ($product['id_list'] ?? '')]);
            }

            $groups = $query->orderBy('numb', 'asc')->get();
            $labels = [];

            foreach ($groups as $group) {
                $propertyQuery = $group->getProperties()->whereIn('id', $propertyIds);

                try {
                    $properties = (clone $propertyQuery)
                        ->orderBy('number', 'asc')
                        ->orderBy('numb', 'asc')
                        ->orderBy('id', 'asc')
                        ->get();
                } catch (\Throwable $e) {
                    $properties = $propertyQuery
                        ->orderBy('numb', 'asc')
                        ->orderBy('id', 'asc')
                        ->get();
                }

                foreach ($properties as $property) {
                    $label = trim((string) ($property['name' . $lang] ?? ''));
                    if ($label !== '') {
                        $labels[] = $label;
                    }
                }
            }

            return array_values(array_unique($labels));
        } catch (\Throwable $e) {
            return [];
        }
    }

    private function createEmbeddings(array $inputs): array
    {
        $response = Http::withToken((string) config('type.openai.key'))
            ->acceptJson()
            ->timeout($this->httpTimeout())
            ->post('https://api.openai.com/v1/embeddings', [
                'model' => $this->embeddingModel(),
                'input' => array_values($inputs),
                'dimensions' => $this->embeddingDimensions(),
                'encoding_format' => 'float',
            ]);

        if (!$response->successful()) {
            return [];
        }

        $vectors = [];
        foreach ((array) data_get($response->json(), 'data', []) as $item) {
            $embedding = data_get($item, 'embedding');
            if (is_array($embedding) && !empty($embedding)) {
                $vectors[] = array_map('floatval', $embedding);
            }
        }

        return $vectors;
    }

    private function createEmbedding(string $input): array
    {
        $vectors = $this->createEmbeddings([$input]);

        return $vectors[0] ?? [];
    }

    private function cosineSimilarity(array $left, array $right, float $leftNorm, float $rightNorm): float
    {
        if ($leftNorm <= 0.0 || $rightNorm <= 0.0) {
            return 0.0;
        }

        $length = min(count($left), count($right));
        if ($length === 0) {
            return 0.0;
        }

        $dot = 0.0;
        for ($i = 0; $i < $length; $i++) {
            $dot += ((float) $left[$i]) * ((float) $right[$i]);
        }

        return $dot / ($leftNorm * $rightNorm);
    }

    private function vectorNorm(array $vector): float
    {
        $sum = 0.0;
        foreach ($vector as $value) {
            $sum += ((float) $value) * ((float) $value);
        }

        return $sum > 0.0 ? sqrt($sum) : 0.0;
    }

    private function cleanText(string $value): string
    {
        $text = html_entity_decode(strip_tags($value), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/\s+/u', ' ', $text) ?? $text;

        return trim($text);
    }

    private function productUpdatedTimestamp($product): int
    {
        return (int) ($product['date_updated'] ?? $product['date_created'] ?? 0);
    }

    private function applyPriceFilter($builder, array $filters, string $priceField): void
    {
        $min = $filters['min_price'] ?? null;
        $max = $filters['max_price'] ?? null;

        if ($min === null && $max === null) {
            return;
        }

        if ($min !== null && $max !== null) {
            $builder->whereRaw($priceField . ' BETWEEN ? AND ?', [$min, $max]);
            return;
        }

        if ($min !== null) {
            $builder->whereRaw($priceField . ' >= ?', [$min]);
        }

        if ($max !== null) {
            $builder->whereRaw($priceField . ' <= ?', [$max]);
        }
    }

    private function embeddingModel(): string
    {
        return trim((string) config('ai_search.embedding_model', 'text-embedding-3-small'));
    }

    private function embeddingDimensions(): int
    {
        return max(64, (int) config('ai_search.embedding_dimensions', 384));
    }

    private function semanticResultLimit(): int
    {
        return max(1, (int) config('ai_search.semantic_result_limit', 60));
    }

    private function bootstrapBatchSize(): int
    {
        return max(0, (int) config('ai_search.bootstrap_batch_size', 120));
    }

    private function syncBatchSize(): int
    {
        return max(0, (int) config('ai_search.sync_batch_size', 16));
    }

    private function embeddingRequestBatchSize(): int
    {
        return max(1, (int) config('ai_search.embedding_request_batch_size', 24));
    }

    private function httpTimeout(): int
    {
        return max(10, (int) config('ai_search.http_timeout', 45));
    }

    private function minSemanticScore(): float
    {
        return (float) config('ai_search.min_semantic_score', 0.18);
    }
}
