<?php

namespace LARAVEL\Controllers\Admin;

use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use LARAVEL\Core\Support\Facades\File;
use LARAVEL\Core\Support\Facades\Func;
use LARAVEL\Helpers\IcondenimCrawler;
use LARAVEL\Models\GalleryModel;
use LARAVEL\Models\ProductCatModel;
use LARAVEL\Models\ProductListModel;
use LARAVEL\Models\ProductModel;
use LARAVEL\Models\ProductPropertiesModel;
use LARAVEL\Models\PropertiesListModel;
use LARAVEL\Models\PropertiesModel;
use LARAVEL\Traits\TraitSave;

class ProductCrawlerController
{
    use TraitSave;

    private $configType;

    public function __construct()
    {
        $this->configType = json_decode(json_encode(config('type')))->product;
    }

    protected function crawlerSettings(): array
    {
        $settings = (array) (config('type.crawler.icondenim') ?? []);

        return [
            'active' => (bool) ($settings['active'] ?? true),
            'enabled' => (bool) ($settings['enabled'] ?? true),
            'allow_custom_source' => (bool) ($settings['allow_custom_source'] ?? true),
            'allow_product_url' => (bool) ($settings['allow_product_url'] ?? true),
            'allow_collection_url' => (bool) ($settings['allow_collection_url'] ?? true),
            'allow_fetch_all' => (bool) ($settings['allow_fetch_all'] ?? true),
            'fetch_all_default' => (bool) ($settings['fetch_all_default'] ?? true),
            'allow_history' => (bool) ($settings['allow_history'] ?? true),
            'history_show_limit' => max(0, (int) ($settings['history_show_limit'] ?? 15)),
            'history_store_limit' => max(1, (int) ($settings['history_store_limit'] ?? 30)),
            'default_batch_size' => max(1, (int) ($settings['default_batch_size'] ?? 10)),
            'max_batch_size' => max(1, (int) ($settings['max_batch_size'] ?? 50)),
            'default_variant_quantity' => max(0, (int) ($settings['default_variant_quantity'] ?? 10)),
            'collection_max_pages' => max(1, (int) ($settings['collection_max_pages'] ?? 200)),
            'collection_default_url' => trim((string) ($settings['collection_default_url'] ?? 'https://icondenim.com/collections/tat-ca-san-pham')),
        ];
    }

    public function man($com, $act, $type, Request $request)
    {
        $crawlerSettings = $this->crawlerSettings();
        $productManUrl = url('admin', ['com' => 'product', 'act' => 'man', 'type' => $type]);

        if (empty($crawlerSettings['active'])) {
            return transfer('Chức năng crawl ICONDENIM hiện đang ẩn.', false, $productManUrl);
        }

        $defaultCrawler = new IcondenimCrawler($crawlerSettings['collection_default_url']);
        $sourceInput = !empty($crawlerSettings['allow_custom_source']) ? trim((string) ($request->query('source_url') ?? '')) : '';
        $source = $this->resolveCrawlerSource($sourceInput, $defaultCrawler->getCollectionUrl(), $crawlerSettings);
        $fetchAll = $this->shouldFetchAllCrawlerProducts($request, $crawlerSettings);
        $batchSize = max(1, min((int) $crawlerSettings['max_batch_size'], (int) ($request->query('limit') ?? $crawlerSettings['default_batch_size'])));
        $variantQuantity = max(0, (int) ($request->query('variant_quantity') ?? $crawlerSettings['default_variant_quantity']));
        $summary = [
            'imported' => 0,
            'skipped' => 0,
            'errors' => 0,
            'pending' => 0,
        ];
        $results = [];

        if ($request->query('run')) {
            if (empty($crawlerSettings['enabled'])) {
                $summary['errors'] = 1;
                $results[] = [
                    'status' => 'error',
                    'name' => '',
                    'code' => '',
                    'message' => 'Chức năng crawl ICONDENIM đang tắt trong config/type.php.',
                ];
            } elseif (!($source['valid'] ?? false)) {
                $summary['errors'] = 1;
                $results[] = [
                    'status' => 'error',
                    'name' => '',
                    'code' => '',
                    'message' => (string) ($source['message'] ?? 'Link crawl không hợp lệ.'),
                ];
            } else {
                [$summary, $results] = $this->importIcondenimBatch($type, $batchSize, $variantQuantity, $source, $fetchAll, $crawlerSettings);
            }

            $this->storeCrawlerHistory($source, $summary, $results, $fetchAll, $batchSize, $variantQuantity, $crawlerSettings);
        }

        $sourceDisplayUrl = (string) ($source['display_url'] ?? $defaultCrawler->getCollectionUrl());
        $sourceMode = (string) ($source['mode'] ?? 'collection');
        $sourceInputValue = ($sourceInput !== '' && ($source['valid'] ?? false)) ? $sourceDisplayUrl : $sourceInput;
        $history = !empty($crawlerSettings['allow_history'])
            ? $this->loadCrawlerHistory((int) $crawlerSettings['history_show_limit'])
            : [];

        return view('product.crawler.man', compact(
            'batchSize',
            'variantQuantity',
            'summary',
            'results',
            'type',
            'sourceInputValue',
            'sourceDisplayUrl',
            'sourceMode',
            'fetchAll',
            'history',
            'crawlerSettings'
        ));
    }

    public function toggle($com, $act, $type, Request $request)
    {
        $enabled = (string) $request->input('enabled', '0') === '1';
        $redirectUrl = url('admin', ['com' => 'product-crawler', 'act' => 'man', 'type' => $type]);
        $productManUrl = url('admin', ['com' => 'product', 'act' => 'man', 'type' => $type]);

        if (empty($this->crawlerSettings()['active'])) {
            return transfer('Chức năng crawl ICONDENIM hiện đang ẩn.', false, $productManUrl);
        }

        if (!$this->updateCrawlerEnabledSetting('icondenim', $enabled)) {
            return transfer('Không thể cập nhật trạng thái crawler trong config/type.php.', false, $redirectUrl);
        }

        if (function_exists('opcache_invalidate')) {
            @opcache_invalidate($this->crawlerConfigFilePath(), true);
        }

        return transfer(
            $enabled ? 'Đã bật chức năng crawl ICONDENIM.' : 'Đã tắt chức năng crawl ICONDENIM.',
            true,
            $redirectUrl
        );
    }

    protected function importIcondenimBatch(string $type, int $limit = 10, int $variantQuantity = 10, array $source = [], bool $fetchAll = true, array $crawlerSettings = []): array
    {
        $summary = [
            'imported' => 0,
            'skipped' => 0,
            'errors' => 0,
            'pending' => 0,
        ];
        $results = [];
        $crawlerSettings = !empty($crawlerSettings) ? $crawlerSettings : $this->crawlerSettings();
        $source = !empty($source) ? $source : $this->resolveCrawlerSource('', (string) $crawlerSettings['collection_default_url'], $crawlerSettings);

        if (!($source['valid'] ?? false)) {
            $summary['errors'] = 1;
            $results[] = [
                'status' => 'error',
                'name' => '',
                'code' => '',
                'message' => (string) ($source['message'] ?? 'Link crawl không hợp lệ.'),
            ];

            return [$summary, $results];
        }

        if (($source['mode'] ?? 'collection') === 'product') {
            return $this->importIcondenimProductByUrl($type, (string) ($source['url'] ?? ''), $variantQuantity, $crawlerSettings);
        }

        $crawler = new IcondenimCrawler((string) ($source['url'] ?? $crawlerSettings['collection_default_url']));
        $pendingLimit = ($fetchAll && !empty($crawlerSettings['allow_fetch_all'])) ? null : $limit;
        $maxPages = !empty($crawlerSettings['collection_max_pages']) ? (int) $crawlerSettings['collection_max_pages'] : 200;

        try {
            $pendingProducts = $crawler->collectPendingProducts($pendingLimit, function ($slug) use ($type) {
                return $this->productExistsByCrawlerSlug($type, $slug);
            }, $maxPages);
        } catch (\Throwable $e) {
            $summary['errors'] = 1;
            $results[] = [
                'status' => 'error',
                'name' => '',
                'code' => '',
                'message' => $e->getMessage(),
            ];

            return [$summary, $results];
        }

        $summary['pending'] = count($pendingProducts);

        if (empty($pendingProducts)) {
            $results[] = [
                'status' => 'skip',
                'name' => '',
                'code' => '',
                'message' => 'Không còn sản phẩm mới để import.',
            ];

            return [$summary, $results];
        }

        foreach ($pendingProducts as $pendingProduct) {
            try {
                $payload = $crawler->parseProduct($pendingProduct['url']);
                $previewImageUrls = array_values(array_filter((array) ($pendingProduct['preview_image_urls'] ?? [])));

                if (!empty($previewImageUrls)) {
                    $payload['image_urls'] = array_values(array_unique(array_merge(
                        $previewImageUrls,
                        array_values(array_filter((array) ($payload['image_urls'] ?? [])))
                    )));
                }

                if ($this->productExistsByCrawlerCodeOrSlug($type, $payload['code'], $payload['source_slug'])) {
                    $summary['skipped']++;
                    $results[] = [
                        'status' => 'skip',
                        'name' => $payload['name'],
                        'code' => $payload['code'],
                        'message' => 'Sản phẩm đã tồn tại, tự động bỏ qua.',
                    ];

                    continue;
                }

                $result = DB::transaction(function () use ($payload, $type, $variantQuantity) {
                    return $this->importIcondenimProduct($payload, $type, $variantQuantity);
                });

                $summary['imported']++;
                $results[] = $result;
            } catch (\Throwable $e) {
                $summary['errors']++;
                $results[] = [
                    'status' => 'error',
                    'name' => $pendingProduct['slug'] ?? '',
                    'code' => '',
                    'message' => $e->getMessage(),
                ];
            }
        }

        return [$summary, $results];
    }

    protected function crawlerConfigFilePath(): string
    {
        return dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'type.php';
    }

    protected function updateCrawlerEnabledSetting(string $crawlerKey, bool $enabled): bool
    {
        $path = $this->crawlerConfigFilePath();

        if (!is_file($path) || !is_readable($path) || !is_writable($path)) {
            return false;
        }

        $contents = file_get_contents($path);

        if (!is_string($contents) || $contents === '') {
            return false;
        }

        $crawlerBlock = $this->extractConfigArrayBlock($contents, 'crawler');

        if ($crawlerBlock === null) {
            return false;
        }

        $crawlerBody = substr(
            $contents,
            $crawlerBlock['body_start'],
            $crawlerBlock['body_end'] - $crawlerBlock['body_start']
        );

        if (!is_string($crawlerBody)) {
            return false;
        }

        $targetBlock = $this->extractConfigArrayBlock($crawlerBody, $crawlerKey);

        if ($targetBlock === null) {
            return false;
        }

        $targetBody = substr(
            $crawlerBody,
            $targetBlock['body_start'],
            $targetBlock['body_end'] - $targetBlock['body_start']
        );

        if (!is_string($targetBody)) {
            return false;
        }

        $replacementValue = $enabled ? 'true' : 'false';
        $updatedTargetBody = preg_replace(
            "/('enabled'\\s*=>\\s*)(true|false)/",
            '$1' . $replacementValue,
            $targetBody,
            1,
            $replaceCount
        );

        if (!is_string($updatedTargetBody)) {
            return false;
        }

        if ($replaceCount === 0) {
            $lineBreak = str_contains($contents, "\r\n") ? "\r\n" : "\n";
            $itemIndent = $this->detectArrayItemIndent($crawlerBody, (int) $targetBlock['open_bracket']);
            $updatedTargetBody = $lineBreak . $itemIndent . "'enabled' => " . $replacementValue . ',' . $targetBody;
        }

        $updatedCrawlerBody = substr($crawlerBody, 0, $targetBlock['body_start'])
            . $updatedTargetBody
            . substr($crawlerBody, $targetBlock['body_end']);

        $updatedContents = substr($contents, 0, $crawlerBlock['body_start'])
            . $updatedCrawlerBody
            . substr($contents, $crawlerBlock['body_end']);

        return file_put_contents($path, $updatedContents, LOCK_EX) !== false;
    }

    protected function extractConfigArrayBlock(string $source, string $key): ?array
    {
        $pattern = "/'" . preg_quote($key, '/') . "'\\s*=>\\s*\\[/";

        if (!preg_match($pattern, $source, $matches, PREG_OFFSET_CAPTURE)) {
            return null;
        }

        $fullMatch = $matches[0] ?? null;

        if (!is_array($fullMatch) || !isset($fullMatch[0], $fullMatch[1])) {
            return null;
        }

        $matchedText = (string) $fullMatch[0];
        $matchedOffset = (int) $fullMatch[1];
        $openBracketOffset = $matchedOffset + strlen($matchedText) - 1;
        $closeBracketOffset = $this->findMatchingSquareBracket($source, $openBracketOffset);

        if ($closeBracketOffset === null) {
            return null;
        }

        return [
            'match_start' => $matchedOffset,
            'match_end' => $matchedOffset + strlen($matchedText),
            'open_bracket' => $openBracketOffset,
            'close_bracket' => $closeBracketOffset,
            'body_start' => $openBracketOffset + 1,
            'body_end' => $closeBracketOffset,
        ];
    }

    protected function findMatchingSquareBracket(string $source, int $openBracketOffset): ?int
    {
        $length = strlen($source);
        $depth = 0;
        $inSingleQuote = false;
        $inDoubleQuote = false;
        $inLineComment = false;
        $inBlockComment = false;

        for ($index = $openBracketOffset; $index < $length; $index++) {
            $char = $source[$index];
            $nextChar = $index + 1 < $length ? $source[$index + 1] : '';

            if ($inLineComment) {
                if ($char === "\n") {
                    $inLineComment = false;
                }

                continue;
            }

            if ($inBlockComment) {
                if ($char === '*' && $nextChar === '/') {
                    $inBlockComment = false;
                    $index++;
                }

                continue;
            }

            if ($inSingleQuote) {
                if ($char === '\\') {
                    $index++;
                    continue;
                }

                if ($char === "'") {
                    $inSingleQuote = false;
                }

                continue;
            }

            if ($inDoubleQuote) {
                if ($char === '\\') {
                    $index++;
                    continue;
                }

                if ($char === '"') {
                    $inDoubleQuote = false;
                }

                continue;
            }

            if ($char === '/' && $nextChar === '/') {
                $inLineComment = true;
                $index++;
                continue;
            }

            if ($char === '/' && $nextChar === '*') {
                $inBlockComment = true;
                $index++;
                continue;
            }

            if ($char === "'") {
                $inSingleQuote = true;
                continue;
            }

            if ($char === '"') {
                $inDoubleQuote = true;
                continue;
            }

            if ($char === '[') {
                $depth++;
                continue;
            }

            if ($char === ']') {
                $depth--;

                if ($depth === 0) {
                    return $index;
                }
            }
        }

        return null;
    }

    protected function detectArrayItemIndent(string $source, int $openBracketOffset): string
    {
        $beforeBracket = substr($source, 0, $openBracketOffset);
        $lineStart = strrpos($beforeBracket, "\n");
        $lineStart = $lineStart === false ? 0 : $lineStart + 1;
        $line = substr($source, $lineStart, $openBracketOffset - $lineStart);

        if (!is_string($line)) {
            return '    ';
        }

        preg_match('/^\s*/', $line, $matches);

        return ((string) ($matches[0] ?? '')) . '    ';
    }

    protected function importIcondenimProductByUrl(string $type, string $productUrl, int $variantQuantity = 10, array $crawlerSettings = []): array
    {
        $crawlerSettings = !empty($crawlerSettings) ? $crawlerSettings : $this->crawlerSettings();
        $crawler = new IcondenimCrawler((string) $crawlerSettings['collection_default_url']);
        $summary = [
            'imported' => 0,
            'skipped' => 0,
            'errors' => 0,
            'pending' => 0,
        ];
        $results = [];

        if ($productUrl === '') {
            $summary['errors'] = 1;
            $results[] = [
                'status' => 'error',
                'name' => '',
                'code' => '',
                'message' => 'Link sản phẩm không hợp lệ.',
            ];

            return [$summary, $results];
        }

        try {
            $payload = $crawler->parseProduct($productUrl);
            $previewImageUrls = $crawler->findProductPreviewImages(
                (string) ($payload['source_slug'] ?? ''),
                !empty($crawlerSettings['collection_max_pages']) ? (int) $crawlerSettings['collection_max_pages'] : 200
            );

            if (!empty($previewImageUrls)) {
                $payload['image_urls'] = array_values(array_unique(array_merge(
                    $previewImageUrls,
                    array_values(array_filter((array) ($payload['image_urls'] ?? [])))
                )));
            }
        } catch (\Throwable $e) {
            $summary['errors'] = 1;
            $results[] = [
                'status' => 'error',
                'name' => '',
                'code' => '',
                'message' => $e->getMessage(),
            ];

            return [$summary, $results];
        }

        if ($this->productExistsByCrawlerCodeOrSlug($type, $payload['code'], $payload['source_slug'])) {
            $summary['skipped'] = 1;
            $results[] = [
                'status' => 'skip',
                'name' => $payload['name'],
                'code' => $payload['code'],
                'message' => 'Sản phẩm đã tồn tại, tự động bỏ qua.',
            ];

            return [$summary, $results];
        }

        $summary['pending'] = 1;

        try {
            $result = DB::transaction(function () use ($payload, $type, $variantQuantity) {
                return $this->importIcondenimProduct($payload, $type, $variantQuantity);
            });

            $summary['imported'] = 1;
            $results[] = $result;
        } catch (\Throwable $e) {
            $summary['errors'] = 1;
            $results[] = [
                'status' => 'error',
                'name' => $payload['name'] ?? '',
                'code' => $payload['code'] ?? '',
                'message' => $e->getMessage(),
            ];
        }

        return [$summary, $results];
    }

    protected function shouldFetchAllCrawlerProducts(Request $request, array $crawlerSettings = []): bool
    {
        $crawlerSettings = !empty($crawlerSettings) ? $crawlerSettings : $this->crawlerSettings();

        if (empty($crawlerSettings['allow_fetch_all'])) {
            return false;
        }

        $defaultValue = !empty($crawlerSettings['fetch_all_default']) ? '1' : '0';
        $value = strtolower(trim((string) ($request->query('fetch_all') ?? $defaultValue)));

        return !in_array($value, ['0', 'false', 'off', 'no'], true);
    }

    protected function crawlerHistoryStoragePath(): string
    {
        $dir = dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'caches' . DIRECTORY_SEPARATOR . 'crawler_history';

        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }

        return $dir . DIRECTORY_SEPARATOR . 'icondenim_product_imports.json';
    }

    protected function loadCrawlerHistory(int $limit = 15): array
    {
        $path = $this->crawlerHistoryStoragePath();

        if (!is_file($path)) {
            return [];
        }

        $payload = json_decode((string) file_get_contents($path), true);

        if (!is_array($payload)) {
            return [];
        }

        usort($payload, static function (array $left, array $right): int {
            return (int) ($right['created_at'] ?? 0) <=> (int) ($left['created_at'] ?? 0);
        });

        if ($limit > 0) {
            $payload = array_slice($payload, 0, $limit);
        }

        return array_values($payload);
    }

    protected function storeCrawlerHistory(
        array $source,
        array $summary,
        array $results,
        bool $fetchAll,
        int $limit,
        int $variantQuantity,
        array $crawlerSettings = []
    ): void {
        $crawlerSettings = !empty($crawlerSettings) ? $crawlerSettings : $this->crawlerSettings();

        if (empty($crawlerSettings['allow_history'])) {
            return;
        }

        $path = $this->crawlerHistoryStoragePath();
        $history = $this->loadCrawlerHistory(0);
        $history[] = [
            'id' => uniqid('crawler_', true),
            'created_at' => time(),
            'created_at_text' => Carbon::now()->format('d/m/Y H:i:s'),
            'source_url' => (string) ($source['display_url'] ?? $source['url'] ?? ''),
            'source_mode' => (string) ($source['mode'] ?? ''),
            'fetch_all' => $fetchAll,
            'limit' => $limit,
            'variant_quantity' => $variantQuantity,
            'summary' => [
                'imported' => (int) ($summary['imported'] ?? 0),
                'skipped' => (int) ($summary['skipped'] ?? 0),
                'errors' => (int) ($summary['errors'] ?? 0),
                'pending' => (int) ($summary['pending'] ?? 0),
            ],
            'message' => $this->buildCrawlerHistoryMessage($summary, $results),
            'status' => $this->resolveCrawlerHistoryStatus($summary),
        ];

        usort($history, static function (array $left, array $right): int {
            return (int) ($right['created_at'] ?? 0) <=> (int) ($left['created_at'] ?? 0);
        });

        $history = array_slice($history, 0, (int) $crawlerSettings['history_store_limit']);

        file_put_contents($path, json_encode(array_values($history), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), LOCK_EX);
    }

    protected function buildCrawlerHistoryMessage(array $summary, array $results): string
    {
        if (count($results) === 1) {
            return trim((string) ($results[0]['message'] ?? ''));
        }

        $parts = [];

        if (!empty($summary['imported'])) {
            $parts[] = 'Import ' . (int) $summary['imported'];
        }

        if (!empty($summary['skipped'])) {
            $parts[] = 'Bỏ qua ' . (int) $summary['skipped'];
        }

        if (!empty($summary['errors'])) {
            $parts[] = 'Lỗi ' . (int) $summary['errors'];
        }

        if (empty($parts)) {
            return 'Không có thay đổi.';
        }

        return implode(' | ', $parts);
    }

    protected function resolveCrawlerHistoryStatus(array $summary): string
    {
        if ((int) ($summary['errors'] ?? 0) > 0 && (int) ($summary['imported'] ?? 0) === 0) {
            return 'error';
        }

        if ((int) ($summary['imported'] ?? 0) > 0) {
            return 'success';
        }

        return 'skip';
    }

    protected function resolveCrawlerSource(
        string $sourceInput,
        string $defaultCollectionUrl = 'https://icondenim.com/collections/tat-ca-san-pham',
        array $crawlerSettings = []
    ): array {
        $crawlerSettings = !empty($crawlerSettings) ? $crawlerSettings : $this->crawlerSettings();
        $sourceInput = trim($sourceInput);

        if (empty($crawlerSettings['allow_custom_source'])) {
            $sourceInput = '';
        }

        if ($sourceInput === '') {
            return [
                'valid' => true,
                'mode' => 'collection',
                'url' => $defaultCollectionUrl,
                'display_url' => $defaultCollectionUrl,
                'message' => '',
            ];
        }

        $normalizedUrl = $this->normalizeCrawlerSourceUrl($sourceInput);

        if ($normalizedUrl === '') {
            return [
                'valid' => false,
                'mode' => '',
                'url' => '',
                'display_url' => $sourceInput,
                'message' => 'Link crawl chỉ hỗ trợ đường dẫn /products/... hoặc /collections/... của icondenim.com.',
            ];
        }

        $mode = $this->detectCrawlerSourceMode($normalizedUrl);

        if ($mode === 'product' && empty($crawlerSettings['allow_product_url'])) {
            return [
                'valid' => false,
                'mode' => 'product',
                'url' => '',
                'display_url' => $normalizedUrl,
                'message' => 'Link sản phẩm đang bị tắt trong config/type.php.',
            ];
        }

        if ($mode === 'collection' && empty($crawlerSettings['allow_collection_url'])) {
            return [
                'valid' => false,
                'mode' => 'collection',
                'url' => '',
                'display_url' => $normalizedUrl,
                'message' => 'Link collection đang bị tắt trong config/type.php.',
            ];
        }

        return [
            'valid' => true,
            'mode' => $mode,
            'url' => $normalizedUrl,
            'display_url' => $normalizedUrl,
            'message' => '',
        ];
    }

    protected function normalizeCrawlerSourceUrl(string $sourceInput): string
    {
        $sourceUrl = trim($sourceInput);

        if ($sourceUrl === '') {
            return '';
        }

        if (str_starts_with($sourceUrl, '//')) {
            $sourceUrl = 'https:' . $sourceUrl;
        } elseif (str_starts_with($sourceUrl, '/')) {
            $sourceUrl = 'https://icondenim.com' . $sourceUrl;
        } elseif (preg_match('/^(products|collections)\//i', $sourceUrl)) {
            $sourceUrl = 'https://icondenim.com/' . ltrim($sourceUrl, '/');
        } elseif (preg_match('/^(?:www\.)?icondenim\.com/i', $sourceUrl)) {
            $sourceUrl = 'https://' . $sourceUrl;
        }

        if (!preg_match('/^https?:\/\//i', $sourceUrl)) {
            return '';
        }

        $parts = parse_url($sourceUrl);
        $host = strtolower((string) ($parts['host'] ?? ''));

        if (!in_array($host, ['icondenim.com', 'www.icondenim.com'], true)) {
            return '';
        }

        $path = '/' . trim((string) ($parts['path'] ?? ''), '/');
        $path = preg_replace('#/+#', '/', $path);

        if (!is_string($path) || $path === '/') {
            return '';
        }

        if (preg_match('~^/products/([^/]+)$~i', $path, $matches)) {
            return 'https://icondenim.com/products/' . trim((string) ($matches[1] ?? ''));
        }

        if (preg_match('~^/collections/([^/]+)$~i', $path, $matches)) {
            return 'https://icondenim.com/collections/' . trim((string) ($matches[1] ?? ''));
        }

        return '';
    }

    protected function detectCrawlerSourceMode(string $sourceUrl): string
    {
        $path = strtolower((string) parse_url($sourceUrl, PHP_URL_PATH));

        if (str_contains($path, '/products/')) {
            return 'product';
        }

        return 'collection';
    }

    protected function importIcondenimProduct(array $payload, string $type, int $variantQuantity = 10): array
    {
        $imageUrls = array_values(array_filter((array) ($payload['image_urls'] ?? [])));
        $imageFileMap = $this->downloadCrawlerImages($imageUrls, $payload['source_slug'] ?? 'product');
        $imageFiles = [];

        foreach ($imageUrls as $imageUrl) {
            $fileName = trim((string) ($imageFileMap[$imageUrl] ?? ''));

            if ($fileName === '' || in_array($fileName, $imageFiles, true)) {
                continue;
            }

            $imageFiles[] = $fileName;
        }
        $photo = $imageFiles[0] ?? '';
        $icon = $imageFiles[1] ?? $photo;
        $category = $this->resolveCrawlerCategoryIds($type, $payload);

        $colorList = $this->ensureCrawlerPropertyList($type, 'mau', 'Màu');
        $sizeList = $this->ensureCrawlerPropertyList($type, 'size', 'Size');

        $colorIds = [];
        $colorIdMap = [];
        $sizeIds = [];

        foreach ((array) ($payload['colors'] ?? []) as $colorName) {
            $colorName = trim((string) $colorName);
            $colorId = $this->ensureCrawlerPropertyValue($type, (int) $colorList->id, $colorName);
            if ($colorId > 0) {
                $colorIds[] = $colorId;
                $colorIdMap[$this->normalizeCrawlerColorKey($colorName)] = $colorId;
            }
        }

        foreach ((array) ($payload['sizes'] ?? []) as $sizeName) {
            $sizeIds[] = $this->ensureCrawlerPropertyValue($type, (int) $sizeList->id, trim((string) $sizeName));
        }

        $colorIds = array_values(array_filter(array_unique($colorIds)));
        $sizeIds = array_values(array_filter(array_unique($sizeIds)));

        $properties = [];
        $listProperties = [];

        if (!empty($colorIds)) {
            $properties = array_merge($properties, $colorIds);
            $listProperties[] = (int) $colorList->id;
        }

        if (!empty($sizeIds)) {
            $properties = array_merge($properties, $sizeIds);
            $listProperties[] = (int) $sizeList->id;
        }

        $descriptionText = trim((string) ($payload['description_text'] ?? ''));
        $descriptionHtml = trim((string) ($payload['description_html'] ?? ''));
        $descriptionSeo = $descriptionText !== '' ? mb_substr($descriptionText, 0, 180) : $payload['name'];

        $data = [
            'namevi' => $payload['name'],
            'slugvi' => $payload['source_slug'],
            'code' => $payload['code'],
            'id_list' => (int) ($category['id_list'] ?? 0),
            'id_cat' => (int) ($category['id_cat'] ?? 0),
            'regular_price' => (int) ($payload['regular_price'] ?? 0),
            'sale_price' => (int) ($payload['sale_price'] ?? 0),
            'discount' => (int) ($payload['discount'] ?? 0),
            'descvi' => $descriptionText !== '' ? htmlspecialchars($descriptionText) : '',
            'contentvi' => $descriptionHtml !== '' ? htmlspecialchars($descriptionHtml) : '',
            'photo' => $photo,
            'icon' => $icon,
            'properties' => !empty($properties) ? implode(',', $properties) : '',
            'list_properties' => !empty($listProperties) ? implode(',', $listProperties) : '',
            'options' => json_encode([
                'crawler' => [
                    'source' => 'icondenim',
                    'source_url' => $payload['source_url'],
                    'source_slug' => $payload['source_slug'],
                    'category' => [
                        'source' => $category['source'] ?? '',
                        'list_name' => $category['list_name'] ?? '',
                        'list_slug' => $category['list_slug'] ?? '',
                        'id_list' => (int) ($category['id_list'] ?? 0),
                        'cat_name' => $category['cat_name'] ?? '',
                        'cat_slug' => $category['cat_slug'] ?? '',
                        'id_cat' => (int) ($category['id_cat'] ?? 0),
                    ],
                    'image_urls' => $payload['image_urls'],
                    'color_image_map' => $payload['color_image_map'] ?? [],
                    'imported_at' => Carbon::now()->toIso8601String(),
                ],
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'status' => 'hienthi',
            'type' => $type,
            'date_created' => time(),
            'date_updated' => time(),
            'date_publish' => Carbon::now()->toDateTimeString(),
            'view' => 0,
        ];

        $product = ProductModel::create($data);

        $dataSeo = [
            'titlevi' => $payload['name'],
            'keywordsvi' => $payload['name'],
            'descriptionvi' => $descriptionSeo,
            'meta' => json_encode([
                'metaindex' => 'index',
                'metaorder' => '',
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ];

        $this->insertSeo('product', 'save', $type, $product->id, $dataSeo);
        $this->insertSlug('product', 'save', $type, $product->id, [
            'slugvi' => $payload['source_slug'],
            'namevi' => $payload['name'],
        ], $this->crawlerWebController());

        $galleryIds = [];
        $galleryIdByFile = [];

        foreach ($imageFiles as $index => $fileName) {
            $gallery = GalleryModel::create([
                'id_parent' => $product->id,
                'photo' => $fileName,
                'numb' => $index + 1,
                'type' => $type,
                'type_parent' => $type,
                'com' => 'product',
                'status' => 'hienthi',
            ]);

            $galleryIds[] = (int) $gallery->id;
            $galleryIdByFile[$fileName] = (int) $gallery->id;
        }

        $colorPhotoIds = $this->mapCrawlerColorPhotoIds(
            (array) ($payload['color_image_map'] ?? []),
            $colorIdMap,
            $imageFileMap,
            $galleryIdByFile
        );
        $variantPhotoId = (int) ($galleryIds[0] ?? 0);

        $this->createCrawlerVariants(
            productId: (int) $product->id,
            productCode: (string) $payload['code'],
            regularPrice: (int) ($payload['regular_price'] ?? 0),
            salePrice: (int) ($payload['sale_price'] ?? 0),
            discount: (int) ($payload['discount'] ?? 0),
            colorIds: $colorIds,
            sizeIds: $sizeIds,
            colorPhotoIds: $colorPhotoIds,
            variantQuantity: $variantQuantity,
            variantPhotoId: $variantPhotoId
        );

        return [
            'status' => 'success',
            'name' => $payload['name'],
            'code' => $payload['code'],
            'message' => 'Import thành công.',
        ];
    }

    protected function resolveCrawlerCategoryIds(string $type, array $payload): array
    {
        $category = array_filter((array) ($payload['category'] ?? []), static fn($value) => $value !== '' && $value !== null);
        $list = $this->ensureCrawlerProductList($type, $category);
        $cat = $this->ensureCrawlerProductCat($type, (int) ($list->id ?? 0), $category);

        if (!empty($cat) && !empty($cat->id_list)) {
            $linkedList = ProductListModel::where('id', $cat->id_list)
                ->where('type', $type)
                ->first();

            if (!empty($linkedList)) {
                $list = $linkedList;
            }
        }

        return [
            'id_list' => (int) ($list->id ?? ($cat->id_list ?? 0)),
            'id_cat' => (int) ($cat->id ?? 0),
            'list_name' => (string) ($list->namevi ?? ($category['list_name'] ?? '')),
            'list_slug' => (string) ($list->slugvi ?? ($category['list_slug'] ?? '')),
            'cat_name' => (string) ($cat->namevi ?? ($category['cat_name'] ?? '')),
            'cat_slug' => (string) ($cat->slugvi ?? ($category['cat_slug'] ?? '')),
            'source' => (string) ($category['source'] ?? ''),
        ];
    }

    protected function ensureCrawlerProductList(string $type, array $category): ?ProductListModel
    {
        $slug = trim((string) ($category['list_slug'] ?? ''));
        $name = trim((string) ($category['list_name'] ?? ''));

        if ($slug === '' && $name === '') {
            return null;
        }

        $list = ProductListModel::where('type', $type)
            ->where(function ($query) use ($slug, $name) {
                if ($slug !== '') {
                    $query->where('slugvi', $slug);
                }

                if ($name !== '') {
                    if ($slug !== '') {
                        $query->orWhere('namevi', $name);
                    } else {
                        $query->where('namevi', $name);
                    }
                }
            })
            ->orderBy('id', 'asc')
            ->first();

        if (!empty($list)) {
            return $list;
        }

        $slug = $slug !== '' ? $slug : Func::changeTitle($name);
        $name = $name !== '' ? $name : ucwords(str_replace('-', ' ', $slug));

        $list = ProductListModel::create([
            'type' => $type,
            'namevi' => $name,
            'slugvi' => $slug,
            'descvi' => $name,
            'status' => 'hienthi',
            'numb' => 1,
            'date_created' => time(),
            'date_updated' => time(),
            'date_publish' => Carbon::now()->toDateTimeString(),
        ]);

        $this->insertCrawlerCategorySeoAndSlug('product-list', $type, (int) $list->id, $name, $slug);

        return $list;
    }

    protected function ensureCrawlerProductCat(string $type, int $listId, array $category): ?ProductCatModel
    {
        $slug = trim((string) ($category['cat_slug'] ?? ''));
        $name = trim((string) ($category['cat_name'] ?? ''));

        if ($slug === '' && $name === '') {
            return null;
        }

        $query = ProductCatModel::where('type', $type);

        if ($listId > 0) {
            $query->where('id_list', $listId);
        }

        $cat = $query->where(function ($query) use ($slug, $name) {
            if ($slug !== '') {
                $query->where('slugvi', $slug);
            }

            if ($name !== '') {
                if ($slug !== '') {
                    $query->orWhere('namevi', $name);
                } else {
                    $query->where('namevi', $name);
                }
            }
        })
            ->orderBy('id', 'asc')
            ->first();

        if (empty($cat) && $slug !== '') {
            $cat = ProductCatModel::where('type', $type)
                ->where('slugvi', $slug)
                ->orderBy('id', 'asc')
                ->first();
        }

        if (!empty($cat)) {
            return $cat;
        }

        if ($listId <= 0) {
            return null;
        }

        $slug = $slug !== '' ? $slug : Func::changeTitle($name);
        $name = $name !== '' ? $name : ucwords(str_replace('-', ' ', $slug));

        $cat = ProductCatModel::create([
            'type' => $type,
            'id_list' => $listId,
            'namevi' => $name,
            'slugvi' => $slug,
            'descvi' => $name,
            'status' => 'hienthi',
            'numb' => 1,
            'date_created' => time(),
            'date_updated' => time(),
            'date_publish' => Carbon::now()->toDateTimeString(),
        ]);

        $this->insertCrawlerCategorySeoAndSlug('product-cat', $type, (int) $cat->id, $name, $slug);

        return $cat;
    }

    protected function insertCrawlerCategorySeoAndSlug(string $com, string $type, int $id, string $name, string $slug): void
    {
        if ($id <= 0) {
            return;
        }

        $categoryConfig = null;

        if ($com === 'product-list') {
            $categoryConfig = $this->configType->$type->categories->list ?? null;
        } elseif ($com === 'product-cat') {
            $categoryConfig = $this->configType->$type->categories->cat ?? null;
        }

        if (!empty($categoryConfig?->seo_categories)) {
            $this->insertSeo($com, 'save', $type, $id, [
                'titlevi' => $name,
                'keywordsvi' => $name,
                'descriptionvi' => mb_substr($name, 0, 180),
                'meta' => json_encode([
                    'metaindex' => 'index',
                    'metaorder' => '',
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            ]);
        }

        if (!empty($categoryConfig?->slug_categories) && $slug !== '') {
            $this->insertSlug($com, 'save', $type, $id, [
                'slugvi' => $slug,
                'namevi' => $name,
            ], $this->crawlerWebController());
        }
    }

    protected function crawlerWebController(): string
    {
        return '\\LARAVEL\\Controllers\\Web\\ProductController';
    }

    protected function ensureCrawlerPropertyList(string $type, string $slug, string $name)
    {
        $list = PropertiesListModel::where('type', $type)
            ->where('slugvi', $slug)
            ->first();

        if (!empty($list)) {
            return $list;
        }

        return PropertiesListModel::create([
            'type' => $type,
            'slugvi' => $slug,
            'namevi' => $name,
            'status' => 'hienthi,cart,search',
            'numb' => 1,
            'date_created' => time(),
            'date_updated' => time(),
        ]);
    }

    protected function ensureCrawlerPropertyValue(string $type, int $listId, string $name): int
    {
        if ($name === '') {
            return 0;
        }

        $slug = Func::changeTitle($name);

        $property = PropertiesModel::where('type', $type)
            ->where('id_list', $listId)
            ->where(function ($query) use ($slug, $name) {
                $query->where('slugvi', $slug)
                    ->orWhere('namevi', $name);
            })
            ->first();

        if (!empty($property)) {
            return (int) $property->id;
        }

        $property = PropertiesModel::create([
            'type' => $type,
            'id_list' => $listId,
            'slugvi' => $slug,
            'namevi' => $name,
            'status' => 'hienthi',
            'numb' => 1,
            'date_created' => time(),
            'date_updated' => time(),
        ]);

        return (int) $property->id;
    }

    protected function createCrawlerVariants(
        int $productId,
        string $productCode,
        int $regularPrice,
        int $salePrice,
        int $discount,
        array $colorIds,
        array $sizeIds,
        array $colorPhotoIds = [],
        int $variantQuantity = 10,
        int $variantPhotoId = 0
    ): void {
        $combinations = [];

        if (!empty($colorIds) && !empty($sizeIds)) {
            foreach ($colorIds as $colorId) {
                foreach ($sizeIds as $sizeId) {
                    $combinations[] = [$colorId, $sizeId];
                }
            }
        } elseif (!empty($colorIds)) {
            foreach ($colorIds as $colorId) {
                $combinations[] = [$colorId];
            }
        } elseif (!empty($sizeIds)) {
            foreach ($sizeIds as $sizeId) {
                $combinations[] = [$sizeId];
            }
        }

        if (empty($combinations)) {
            return;
        }

        $propertyNames = PropertiesModel::whereIn('id', array_merge($colorIds, $sizeIds))
            ->pluck('namevi', 'id');

        foreach ($combinations as $index => $propertyIds) {
            $propertyIds = array_values(array_filter(array_map('intval', $propertyIds)));

            if (empty($propertyIds)) {
                continue;
            }

            $resolvedPhotoId = $variantPhotoId;
            foreach ($propertyIds as $propertyId) {
                if (!empty($colorPhotoIds[$propertyId])) {
                    $resolvedPhotoId = (int) $colorPhotoIds[$propertyId];
                    break;
                }
            }

            ProductPropertiesModel::create([
                'id_parent' => $productId,
                'id_properties' => implode(',', $propertyIds),
                'namevi' => $this->buildCrawlerVariantLabel($propertyIds, $propertyNames->toArray()),
                'regular_price' => $regularPrice,
                'sale_price' => $salePrice,
                'discount' => $discount,
                'number' => $index + 1,
                'code' => $productCode . '-' . str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT),
                'status' => $variantQuantity > 0 ? 'active' : 'inactive',
                'quantity' => $variantQuantity,
                'id_photo' => $resolvedPhotoId,
            ]);
        }
    }

    protected function buildCrawlerVariantLabel(array $propertyIds, array $propertyNames): string
    {
        $names = [];

        foreach ($propertyIds as $propertyId) {
            if (!empty($propertyNames[$propertyId])) {
                $names[] = $propertyNames[$propertyId];
            }
        }

        return implode('--', $names);
    }

    protected function normalizeCrawlerColorKey(string $value): string
    {
        return Func::changeTitle(trim((string) $value));
    }

    protected function mapCrawlerColorPhotoIds(
        array $colorImageMap,
        array $colorIdMap,
        array $imageFileMap,
        array $galleryIdByFile
    ): array {
        $mapped = [];

        foreach ($colorImageMap as $item) {
            if (!is_array($item)) {
                continue;
            }

            $colorName = trim((string) ($item['color_name'] ?? ''));
            $imageUrl = trim((string) ($item['image_url'] ?? ''));
            $colorKey = $this->normalizeCrawlerColorKey($colorName);

            if ($colorKey === '' || empty($colorIdMap[$colorKey]) || empty($imageFileMap[$imageUrl])) {
                continue;
            }

            $fileName = $imageFileMap[$imageUrl];

            if (empty($galleryIdByFile[$fileName])) {
                continue;
            }

            $mapped[(int) $colorIdMap[$colorKey]] = (int) $galleryIdByFile[$fileName];
        }

        return $mapped;
    }

    protected function productExistsByCrawlerSlug(string $type, string $slug): bool
    {
        return ProductModel::where('type', $type)
            ->where('slugvi', $slug)
            ->exists();
    }

    protected function productExistsByCrawlerCodeOrSlug(string $type, string $code, string $slug): bool
    {
        return ProductModel::where('type', $type)
            ->where(function ($query) use ($code, $slug) {
                if ($code !== '') {
                    $query->where('code', $code);
                }

                if ($slug !== '') {
                    if ($code !== '') {
                        $query->orWhere('slugvi', $slug);
                    } else {
                        $query->where('slugvi', $slug);
                    }
                }
            })
            ->exists();
    }

    protected function downloadCrawlerImages(array $imageUrls, string $slug): array
    {
        $files = [];
        $groupedUrls = [];

        foreach (array_values(array_filter($imageUrls)) as $position => $imageUrl) {
            $normalizedUrl = trim((string) $imageUrl);

            if ($normalizedUrl === '') {
                continue;
            }

            $identity = $this->normalizeCrawlerImageIdentity($normalizedUrl);
            $identity = $identity !== '' ? $identity : strtolower($normalizedUrl);

            if (!isset($groupedUrls[$identity])) {
                $groupedUrls[$identity] = [
                    'position' => $position,
                    'urls' => [],
                ];
            }

            if (!in_array($normalizedUrl, $groupedUrls[$identity]['urls'], true)) {
                $groupedUrls[$identity]['urls'][] = $normalizedUrl;
            }
        }

        uasort($groupedUrls, static function (array $left, array $right): int {
            return ((int) ($left['position'] ?? 0)) <=> ((int) ($right['position'] ?? 0));
        });

        $downloadIndex = 0;

        foreach ($groupedUrls as $group) {
            $urls = (array) ($group['urls'] ?? []);

            usort($urls, function (string $left, string $right): int {
                $scoreCompare = $this->scoreCrawlerImageUrl($right) <=> $this->scoreCrawlerImageUrl($left);

                if ($scoreCompare !== 0) {
                    return $scoreCompare;
                }

                return strlen($left) <=> strlen($right);
            });

            if (empty($urls)) {
                continue;
            }

            $fileName = '';

            foreach ($urls as $candidateUrl) {
                $fileName = $this->downloadCrawlerImage($candidateUrl, $slug, $downloadIndex + 1);

                if ($fileName !== '') {
                    break;
                }
            }

            if ($fileName === '') {
                continue;
            }

            $downloadIndex++;

            foreach ($urls as $originalUrl) {
                $files[$originalUrl] = $fileName;
            }
        }

        return $files;
    }

    protected function normalizeCrawlerImageIdentity(string $imageUrl): string
    {
        $path = rawurldecode((string) parse_url($imageUrl, PHP_URL_PATH));

        if ($path === '') {
            return '';
        }

        $path = strtolower(str_replace('\\', '/', $path));
        $directory = trim((string) dirname($path), './');
        $filename = strtolower((string) pathinfo($path, PATHINFO_FILENAME));
        $extension = strtolower((string) pathinfo($path, PATHINFO_EXTENSION));

        if ($filename === '') {
            return trim($path, '/');
        }

        do {
            $strippedFilename = preg_replace(
                '/_(?:\d+x\d+|pico|icon|thumb|small|compact|medium|large|grande|xlarge|master|original)(?:@\dx)?$/i',
                '',
                $filename
            );

            if (!is_string($strippedFilename) || $strippedFilename === $filename) {
                break;
            }

            $filename = $strippedFilename;
        } while (true);

        $identity = trim(($directory !== '' ? $directory . '/' : '') . $filename, '/');

        if ($extension !== '') {
            $identity .= '.' . $extension;
        }

        return $identity;
    }

    protected function scoreCrawlerImageUrl(string $imageUrl): int
    {
        $path = strtolower((string) parse_url($imageUrl, PHP_URL_PATH));
        $score = 0;

        if (preg_match('/\/products\//i', $path)) {
            $score += 20;
        }

        if (preg_match('/_original\.(jpg|jpeg|png|webp)$/i', $path)) {
            $score += 30;
        } elseif (!preg_match('/_(?:\d+x\d+|pico|icon|thumb|small|compact|medium|large|grande|xlarge|master|original)(?:@\dx)?\.(jpg|jpeg|png|webp)$/i', $path)) {
            $score += 24;
        } elseif (preg_match('/_master\.(jpg|jpeg|png|webp)$/i', $path)) {
            $score += 18;
        } elseif (preg_match('/_(?:xlarge|grande|large)\.(jpg|jpeg|png|webp)$/i', $path)) {
            $score += 12;
        } elseif (preg_match('/_(?:medium|compact|small|thumb|icon|pico)\.(jpg|jpeg|png|webp)$/i', $path)) {
            $score += 6;
        }

        if (preg_match('/(?:cdn|product)\.hstatic\.net/i', $imageUrl)) {
            $score += 4;
        }

        return $score;
    }

    protected function downloadCrawlerImage(string $imageUrl, string $slug, int $index): string
    {
        $ch = curl_init($imageUrl);

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => 45,
            CURLOPT_CONNECTTIMEOUT => 20,
            CURLOPT_MAXREDIRS => 5,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36',
        ]);

        $binary = curl_exec($ch);
        $statusCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($binary === false || $statusCode >= 400 || empty($binary)) {
            return '';
        }

        $fileName = $this->makeCrawlerFilename($slug, $imageUrl, $index);
        File::put(upload('product', $fileName, true), $binary);

        return $fileName;
    }

    protected function makeCrawlerFilename(string $slug, string $imageUrl, int $index): string
    {
        $path = (string) parse_url($imageUrl, PHP_URL_PATH);
        $extension = strtolower((string) pathinfo($path, PATHINFO_EXTENSION));
        $extension = in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true) ? $extension : 'jpg';

        return Func::changeTitle($slug) . '-' . substr(md5($imageUrl), 0, 12) . '-' . $index . '-' . time() . '.' . $extension;
    }
}
