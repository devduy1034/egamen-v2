<?php

namespace LARAVEL\Controllers\Web;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use LARAVEL\Controllers\Controller;
use LARAVEL\Core\Support\Facades\Func;
use LARAVEL\Core\Support\Str;
use LARAVEL\Models\ProductCatModel;
use LARAVEL\Models\ProductItemModel;
use LARAVEL\Models\ProductListModel;
use LARAVEL\Models\ProductModel;
use LARAVEL\Models\ProductSubModel;
use LARAVEL\Models\PropertiesListModel;
use LARAVEL\Models\PropertiesModel;
use LARAVEL\Services\ProductSemanticSearchService;

class SmartSearchController extends Controller
{
    private const DEFAULT_PER_PAGE = 36;
    private const DEFAULT_MAX_PER_PAGE = 72;
    private const DEFAULT_RESULT_LIMIT = 180;
    private const SEARCH_CANDIDATE_LIMIT = 120;
    private const CHAT_RESULT_LIMIT = 6;
    private const CHAT_MAX_QUERY_LENGTH = 160;

    public function search(Request $request)
    {
        $csrfToken = trim((string) ($request->csrf_token ?? $request->_token ?? $request->header('X-CSRF-TOKEN', '')));
        if ($csrfToken === '') {
            return response()->json([
                'success' => false,
                'message' => 'Thiếu CSRF token.',
            ], 419);
        }

        $query = trim((string) $request->input('query', ''));
        $maxQueryLength = 120;
        if ($query === '') {
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng nhập nội dung tìm kiếm.',
            ], 422);
        }
        if (mb_strlen($query, 'UTF-8') > $maxQueryLength) {
            return response()->json([
                'success' => false,
                'message' => 'Câu tìm kiếm quá dài. Vui lòng nhập ngắn hơn.',
            ], 422);
        }

        $filters = $this->parseFilters($query);
        $pagination = $this->resolvePagination($request);
        $products = $this->searchProducts($query, $filters, $pagination['result_limit']);
        $total = $products->count();
        $pagedProducts = $products->forPage($pagination['page'], $pagination['per_page'])->values();
        $totalPages = $total > 0 ? (int) ceil($total / $pagination['per_page']) : 1;

        return response()->json([
            'success' => true,
            'source' => (string) ($filters['_source'] ?? 'fallback'),
            'query' => $query,
            'filters' => array_filter($filters, static function ($value, $key) {
                return $key !== '_source';
            }, ARRAY_FILTER_USE_BOTH),
            'count' => $total,
            'page' => $pagination['page'],
            'per_page' => $pagination['per_page'],
            'total_pages' => $totalPages,
            'has_prev' => $pagination['page'] > 1,
            'has_next' => $pagination['page'] < $totalPages,
            'message' => $total === 0
                ? 'Không tìm thấy sản phẩm phù hợp.'
                : 'Đã tìm thấy ' . $products->count() . ' sản phẩm.',
            'products' => $pagedProducts,
        ]);
    }

    public function quickSearch(Request $request)
    {
        $query = trim((string) $request->input('q', ''));
        if (mb_strlen($query) < 1) {
            return response()->json(['products' => []]);
        }

        // Fast search without AI - just product name/keyword matching
        $products = $this->searchCurrentProducts($query, []);
        $results = [];

        foreach ($products->take(15) as $product) {
            $results[] = [
                'id' => $product['id'],
                'name' => $product['name'],
                'price' => $product['price_text'] ?? '',
                'image' => $product['image'],
                'url' => $product['url'],
            ];
        }

        return response()->json(['products' => $results]);
    }

    public function chatProducts(Request $request)
    {
        $query = trim((string) ($request->input('message', $request->input('q', ''))));
        if ($query === '') {
            return response()->json([
                'status' => 'invalid_request',
                'message' => 'Vui lòng nhập nhu cầu sản phẩm.',
                'data' => [],
            ], 422);
        }

        if (mb_strlen($query, 'UTF-8') > self::CHAT_MAX_QUERY_LENGTH) {
            return response()->json([
                'status' => 'invalid_request',
                'message' => 'Tin nhắn quá dài. Vui lòng nhập dưới 160 ký tự.',
                'data' => [],
            ], 422);
        }

        if ($this->isGreetingMessage($query)) {
            return response()->json([
                'status' => 'success',
                'message' => 'Em chào Anh/Chị. Anh/Chị muốn tìm sản phẩm gì để em hỗ trợ ngay ạ?',
                'data' => [],
            ]);
        }

        if (!$this->isInStoreSalesScope($query)) {
            return response()->json([
                'status' => 'out_of_scope',
                'message' => 'Em chỉ hỗ trợ câu hỏi mua sắm sản phẩm trên website.',
                'data' => [],
            ]);
        }

        $useAiExtraction = $this->shouldUseAiExtractionForChat($query);
        $filters = $this->parseFilters($query, $useAiExtraction, $this->getChatEntityPrompt());
        $products = $this->searchProducts($query, $filters, self::CHAT_RESULT_LIMIT, false);

        $mapped = $products->map(function ($product) {
            return [
                'id' => (int) ($product['id'] ?? 0),
                'name' => (string) ($product['name'] ?? ''),
                'price' => (int) ($product['price'] ?? 0),
                'image_url' => (string) ($product['image'] ?? ''),
                'product_url' => (string) ($product['url'] ?? '#'),
                'product_html' => (string) ($product['html'] ?? ''),
            ];
        })->filter(static function ($item) {
            return !empty($item['id']) && $item['name'] !== '';
        })->values();

        if ($mapped->isEmpty()) {
            return response()->json([
                'status' => 'no_products',
                'message' => 'Chưa tìm thấy sản phẩm phù hợp. Vui lòng liên hệ tư vấn viên.',
                'data' => [],
                'filters' => array_filter($filters, static function ($value, $key) {
                    return $key !== '_source';
                }, ARRAY_FILTER_USE_BOTH),
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Đây là các sản phẩm phù hợp.',
            'data' => $mapped,
            'filters' => array_filter($filters, static function ($value, $key) {
                return $key !== '_source';
            }, ARRAY_FILTER_USE_BOTH),
        ]);
    }

    private function resolvePagination(Request $request): array
    {
        $defaultPerPage = max(1, (int) config('ai_search.search_per_page', self::DEFAULT_PER_PAGE));
        $maxPerPage = max($defaultPerPage, (int) config('ai_search.search_max_per_page', self::DEFAULT_MAX_PER_PAGE));
        $maxResults = max($maxPerPage, (int) config('ai_search.search_result_limit', self::DEFAULT_RESULT_LIMIT));

        $perPage = (int) $request->input('per_page', $defaultPerPage);
        if ($perPage <= 0) {
            $perPage = $defaultPerPage;
        }
        $perPage = min($maxPerPage, $perPage);

        $maxPage = max(1, (int) ceil($maxResults / max(1, $perPage)));
        $page = max(1, (int) $request->input('page', 1));
        $page = min($maxPage, $page);

        return [
            'page' => $page,
            'per_page' => $perPage,
            'result_limit' => $maxResults,
        ];
    }

    private function isInStoreSalesScope(string $query): bool
    {
        $normalized = $this->normalizeSearchText($query);
        if ($normalized === '') {
            return false;
        }

        $shoppingSignals = [
            'mua',
            'tim',
            'tu van',
            'goi y',
            'san pham',
            'con hang',
            'gia',
            'size',
            'mau',
            'ao',
            'quan',
            'dam',
            'vay',
            'khoac',
            'jacket',
            'shirt',
            'tshirt',
            'polo',
            'hoodie',
            'sweater',
            'jean',
            'kaki',
            'short',
            'outfit',
            'mix do',
            'phoi do',
            'non',
            'mu',
            'dep',
            'giay',
            'that lung',
            'balo',
            'tui',
            'vi',
        ];

        foreach ($shoppingSignals as $signal) {
            if ($this->textContainsSearchTerm($normalized, $signal)) {
                return true;
            }
        }

        return false;
    }

    private function isGreetingMessage(string $query): bool
    {
        $normalized = trim($this->normalizeSearchText($query));
        if ($normalized === '') {
            return false;
        }

        $greetings = [
            'hi',
            'hello',
            'helo',
            'hey',
            'xin chao',
            'chao',
            'chao shop',
            'chao ban',
            'chao ad',
            'alo',
        ];

        foreach ($greetings as $greeting) {
            if ($normalized === $greeting || $this->containsWholeTerm($normalized, $greeting)) {
                return true;
            }
        }

        if (preg_match('/^(xin|chao|hi|helo|hello|hey|alo)\b/u', $normalized) === 1) {
            return true;
        }

        return false;
    }

    private function shouldUseAiExtractionForChat(string $query): bool
    {
        $normalized = $this->normalizeSearchText($query);
        if ($normalized === '') {
            return false;
        }

        return str_contains($normalized, 'outfit')
            || str_contains($normalized, 'set do')
            || str_contains($normalized, 'phoi do')
            || str_contains($normalized, 'mix do')
            || str_contains($normalized, 'di lam')
            || str_contains($normalized, 'di choi')
            || str_contains($normalized, 'di bien')
            || str_contains($normalized, 'mua dong')
            || str_contains($normalized, 'mua he');
    }

    private function getChatEntityPrompt(): string
    {
        return <<<'PROMPT'
You are an e-commerce fashion assistant for products in table_product.
Task: extract product-search filters from a user message and return JSON only.

Scope:
- Only shopping/product search intent for this website.
- If the question is outside product shopping scope, set intent="out_of_scope".

Output schema:
{
  "intent":"product_search|out_of_scope|clarify",
  "filters":{
    "category":null|string,
    "color":null|string,
    "size":null|string,
    "style":null|string,
    "material":null|string,
    "occasion":null|string,
    "min_price":null|number,
    "max_price":null|number
  },
  "inferred_keywords":[]
}

Rules:
- Output JSON only, no markdown, no extra text.
- Unknown fields must be null, inferred_keywords default [].
- Normalize: category/color/style lower-case plain text; size uppercase (XS,S,M,L,XL,XXL,3XL).
- Price must be integer VND (e.g. "duoi 500k" => max_price: 500000).
PROMPT;
    }

    public function parseFilters(string $query, bool $useAI = true, ?string $customPrompt = null): array
    {
        $fallback = $this->normalizeFilters($this->heuristicFilters($query), $query, 'fallback');

        if (!$useAI) {
            return $fallback;
        }

        $apiKey = trim((string) env('GEMINI_API_KEY', config('type.openai.key', '')));
        if ($apiKey === '') {
            return $fallback;
        }

        try {
            $prompt = $customPrompt ?? <<<'PROMPT'
Bạn là AI Search Engine lõi của một website thời trang (dữ liệu từ table_product). 
Nhiệm vụ duy nhất của bạn là phân tích câu truy vấn của người dùng và chuyển đổi nó thành một chuỗi JSON hợp lệ. KHÔNG dùng markdown (không bọc trong ```json), KHÔNG giải thích, KHÔNG thêm bất kỳ văn bản nào ngoài chuỗi JSON.

Bạn cần xử lý 2 loại tìm kiếm:
1. Direct Search (Tìm trực tiếp): Người dùng gọi tên đích danh sản phẩm. VD: "áo thun nam đen size L dưới 400k".
2. Mission Search (Tìm theo ngữ cảnh): Người dùng tìm đồ theo mục đích, thời tiết, sự kiện (giống Walmart). VD: "chuẩn bị đồ đi Đà Lạt mùa đông", "outfit đi biển", "quà sinh nhật cho bạn trai".

Dựa vào câu của người dùng, hãy điền đầy đủ vào cấu trúc JSON sau (những trường không có thông tin hoặc không thể suy luận thì BẮT BUỘC để null, riêng mảng thì để []):

{
  "search_intent": "direct" hoặc "mission",
  "filters": {
    "category": "Danh mục chính (ví dụ: áo, quần, nón, dép, balo). Nếu là outfit/set đồ gồm nhiều loại, hãy để null",
    "color": "Màu sắc (viết thường tiếng Việt: đen, trắng, xanh, xám...)",
    "size": "Kích cỡ bằng chữ hoa (XS, S, M, L, XL, XXL...)",
    "style": "Kiểu dáng (oversize, slimfit, form rộng, basic, streetwear...)",
    "material": "Chất liệu (cotton, jean, kaki, len, nỉ, lụa...)",
    "occasion": "Hoàn cảnh sử dụng (đi làm, đi chơi, đi biển, mùa đông, mùa hè...)",
    "min_price": Số nguyên. (Ví dụ: "trên 300k" -> 300000. Nếu không nhắc tới -> null),
    "max_price": Số nguyên. (Ví dụ: "dưới 500k", "không quá 500 cành" -> 500000. Nếu không nhắc tới -> null)
  },
  "inferred_keywords": [
    "Mảng chứa 2-5 từ khóa sản phẩm cụ thể mà bạn suy luận ra. CỰC KỲ QUAN TRỌNG VỚI INTENT = 'mission'. Nếu người dùng tìm 'đồ đi Đà Lạt mùa đông', mảng này là ['áo khoác', 'áo len', 'quần dài', 'mũ len']. Nếu tìm 'đồ đi biển', mảng này là ['quần short', 'áo ba lỗ', 'dép', 'áo hoa']. Nếu intent là 'direct', có thể để [] hoặc điền tên chính xác món đồ."
  ]
}
PROMPT;

            $response = Http::acceptJson()
                ->timeout(25)
                ->post('https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=' . $apiKey, [
                    'contents' => [
                        [
                            'role' => 'user',
                            'parts' => [
                                ['text' => 'Câu truy vấn: "' . $query . '"']
                            ]
                        ]
                    ],
                    'systemInstruction' => [
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ],
                    'generationConfig' => [
                        'temperature' => 0.1,
                        'maxOutputTokens' => 220,
                        'responseMimeType' => 'application/json'
                    ]
                ]);

            if (!$response->successful()) {
                return $fallback;
            }

            $content = (string) data_get($response->json(), 'candidates.0.content.parts.0.text', '');
            $decoded = json_decode($content, true);
            if (!is_array($decoded) || !isset($decoded['filters'])) {
                return $fallback;
            }

            $filtersData = $decoded['filters'];
            $intent = $decoded['search_intent'] ?? 'direct';
            $keywords = $decoded['inferred_keywords'] ?? [];

            if ($intent === 'mission' && !empty($keywords)) {
                if (empty($filtersData['category'])) {
                    $filtersData['category'] = implode(' ', $keywords);
                } else {
                    $filtersData['category'] .= ' ' . implode(' ', $keywords);
                }
            }

            return $this->normalizeFilters($filtersData, $query, 'ai');
        } catch (\Throwable $e) {
            return $fallback;
        }
    }

    public function normalizeFilters(array $raw, string $query, string $source): array
    {
        $filters = [
            'category' => $this->normalizeText($raw['category'] ?? ''),
            'color' => $this->normalizeText($raw['color'] ?? ''),
            'size' => $this->normalizeText($raw['size'] ?? ''),
            'style' => $this->normalizeText($raw['style'] ?? ''),
            'material' => $this->normalizeText($raw['material'] ?? ''),
            'occasion' => $this->normalizeText($raw['occasion'] ?? ''),
            'min_price' => $this->normalizePrice($raw['min_price'] ?? null),
            'max_price' => $this->normalizePrice($raw['max_price'] ?? null),
            '_source' => $source,
        ];

        $filters = $this->applyQueryIntentOverrides($query, $filters);
        $queryPrice = $this->heuristicPriceBounds($query);
        if ($filters['min_price'] === null && $queryPrice['min_price'] !== null) {
            $filters['min_price'] = $queryPrice['min_price'];
        }
        if ($filters['max_price'] === null && $queryPrice['max_price'] !== null) {
            $filters['max_price'] = $queryPrice['max_price'];
        }

        return $filters;
    }

    private function applyQueryIntentOverrides(string $query, array $filters): array
    {
        $normalizedQuery = $this->normalizeSearchText($query);
        $category = $this->normalizeSearchText((string) ($filters['category'] ?? ''));

        $hasQuan = $this->containsWholeTerm($normalizedQuery, 'quan');
        $hasAo = $this->containsWholeTerm($normalizedQuery, 'ao');

        if ($hasQuan && !$hasAo) {
            $detectedCategory = $this->detectPhrase($query, [
                'quan short',
                'quan jean',
                'quan tay',
                'quan kaki',
                'quan jogger',
                'quan baggy',
                'quan lung',
            ]);
            $filters['category'] = $detectedCategory ?: 'quần';
        }

        if ($hasAo && !$hasQuan) {
            $detectedCategory = $this->detectPhrase($query, [
                'ao so mi',
                'ao thun',
                'ao polo',
                'ao khoac',
                'ao len',
                'ao vest',
                'ao blazer',
                'ao hoodie',
                'ao sweater',
            ]);
            $filters['category'] = $detectedCategory ?: 'áo';
        }

        $hasNon = $this->hasHatIntent($normalizedQuery);
        if ($hasNon && !$hasQuan && !$hasAo) {
            $filters['category'] = 'nón';
        }

        $hasDep = $this->hasFootwearIntent($normalizedQuery);
        if ($hasDep && !$hasQuan && !$hasAo && !$hasNon) {
            $filters['category'] = 'dép';
        }

        if ($this->isOutfitIntent($query, $filters) && !$hasQuan && !$hasAo && !$hasNon && !$hasDep) {
            $filters['category'] = 'outfit';
        }

        if ($hasQuan && $category !== '' && str_contains($category, 'ao') && !str_contains($category, 'quan')) {
            $filters['category'] = 'quần';
        }

        if ($hasAo && $category !== '' && str_contains($category, 'quan') && !str_contains($category, 'ao')) {
            $filters['category'] = 'áo';
        }

        if ($hasNon && $category !== '' && (str_contains($category, 'ao') || str_contains($category, 'quan')) && !str_contains($category, 'non')) {
            $filters['category'] = 'nón';
        }

        if ($hasDep && $category !== '' && (str_contains($category, 'ao') || str_contains($category, 'quan') || str_contains($category, 'non'))) {
            $filters['category'] = 'dép';
        }

        return $filters;
    }

    private function heuristicFilters(string $query): array
    {
        $normalized = trim($this->normalizeSearchText($query));
        $parts = preg_split('/\s+/u', $normalized) ?: [];
        $parts = array_values(array_filter(array_map('trim', $parts), static function ($value) {
            return $value !== '';
        }));

        return [
            'category' => $this->detectPhrase($query, [
                'ao so mi',
                'ao thun',
                'ao polo',
                'ao khoac',
                'quan jean',
                'quan tay',
                'quan kaki',
                'ao len',
                'ao vest',
                'ao blazer',
                'ao hoodie',
                'ao sweater',
                'do bo',
                'outfit',
                'nón',
                'non',
                'mũ',
                'nón lưỡi trai',
                'non luoi trai',
                'bucket hat',
                'baseball cap',
                'snapback',
                'beanie',
                'mũ lưỡi trai',
                'mu luoi trai',
                'mũ bucket',
                'mu bucket',
                'dép',
                'dep',
                'giày',
                'giay',
                'sandal',
                'slides',
                'slide',
                'slipper',
                'flip flop',
                'flipflop',
            ]),
            'color' => $this->detectPhrase($query, [
                'trang',
                'den',
                'xanh',
                'xam',
                'be',
                'nau',
                'do',
                'hong',
                'vang',
                'cam',
                'tim',
                'xanh navy',
            ]),
            'size' => $this->detectSize($query, $parts),
            'style' => $this->detectPhrase($query, [
                'oversize',
                'form rong',
                'form suông',
                'form slim',
                'basic',
                'streetwear',
                'smart casual',
            ]),
            'material' => $this->detectPhrase($query, [
                'cotton',
                'poly',
                'linen',
                'kaki',
                'jean',
                'denim',
                'nỉ',
                'nhiệt',
                'lụa',
            ]),
            'occasion' => $this->detectPhrase($query, [
                'di lam',
                'di choi',
                'di tiec',
                'mua he',
                'mua dong',
                'hang ngay',
                'cong so',
            ]),
            'min_price' => $this->heuristicPriceBounds($query)['min_price'],
            'max_price' => $this->heuristicPriceBounds($query)['max_price'],
        ];
    }

    private function detectPhrase(string $query, array $phrases): ?string
    {
        $normalizedQuery = $this->normalizeSearchText($query);
        foreach ($phrases as $phrase) {
            $needle = $this->normalizeSearchText($phrase);
            if ($needle === '') {
                continue;
            }

            if (str_contains($needle, ' ')) {
                if (str_contains($normalizedQuery, $needle)) {
                    return $phrase;
                }
                continue;
            }

            if ($this->containsWholeTerm($normalizedQuery, $needle)) {
                return $phrase;
            }
        }

        return null;
    }

    private function detectSize(string $query, array $tokens): ?string
    {
        $normalizedQuery = strtoupper($this->normalizeSearchText($query));
        $sizes = ['XS', 'S', 'M', 'L', 'XL', 'XXL', '2XL', '3XL'];
        foreach ($sizes as $size) {
            if (preg_match('/(?<![A-Z0-9])' . preg_quote($size, '/') . '(?![A-Z0-9])/i', $normalizedQuery) === 1) {
                return $size;
            }
        }

        foreach ($tokens as $token) {
            if (preg_match('/^(xs|s|m|l|xl|xxl|2xl|3xl)$/i', $token) === 1) {
                return strtoupper($token);
            }
        }

        return null;
    }

    private function heuristicPriceBounds(string $query): array
    {
        $normalized = strtolower($this->normalizeSearchText($query));
        $result = ['min_price' => null, 'max_price' => null];

        if (preg_match('/(?:duoi|less than|under)\s*(\d+(?:[.,]\d+)*)\s*(k|nghin|ngan|trieu|tr)?/u', $normalized, $matches)) {
            $result['max_price'] = $this->priceToInteger($matches[1], $matches[2] ?? null);
        }

        if (preg_match('/(?:tren|over|more than)\s*(\d+(?:[.,]\d+)*)\s*(k|nghin|ngan|trieu|tr)?/u', $normalized, $matches)) {
            $result['min_price'] = $this->priceToInteger($matches[1], $matches[2] ?? null);
        }

        if (preg_match('/(\d+(?:[.,]\d+)*)\s*(?:-|den|toi|to)\s*(\d+(?:[.,]\d+)*)\s*(k|nghin|ngan|trieu|tr)?/u', $normalized, $matches)) {
            $result['min_price'] = $this->priceToInteger($matches[1], $matches[3] ?? null);
            $result['max_price'] = $this->priceToInteger($matches[2], $matches[3] ?? null);
        }

        return $result;
    }

    private function priceToInteger(string $value, ?string $suffix = null): ?int
    {
        $digits = preg_replace('/[^\d]/', '', $value);
        if ($digits === '') {
            return null;
        }

        $price = (int) $digits;
        $suffix = strtolower((string) $suffix);
        if (in_array($suffix, ['k', 'nghin', 'ngan'], true)) {
            $price *= 1000;
        } elseif ($suffix === 'trieu' || $suffix === 'tr') {
            $price *= 1000000;
        }

        return $price;
    }

    private function normalizePrice(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_int($value)) {
            return $value;
        }

        if (is_float($value)) {
            return (int) round($value);
        }

        if (is_numeric($value)) {
            return (int) $value;
        }

        return $this->priceToInteger((string) $value);
    }

    private function normalizeText(mixed $value): string
    {
        $text = trim((string) $value);
        if ($text === '') {
            return '';
        }

        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/\s+/u', ' ', $text) ?? $text;

        return trim($text);
    }

    private function normalizeSearchText(mixed $value): string
    {
        $text = $this->normalizeText($value);
        $text = Func::utf8Convert($text);

        return strtolower($text);
    }

    public function searchProducts(string $query, array $filters, int $resultLimit, bool $useAI = true)
    {
        $categoryFamily = $this->detectCategoryFamily($query, $filters);
        $outfitIntent = $this->isOutfitIntent($query, $filters);
        $semanticResults = $useAI ? $this->searchSemanticProducts($query, $filters) : collect();
        $results = collect();
        $results = $results->merge($this->searchCurrentProducts($query, $filters));
        $results = $results->merge($semanticResults);
        $results = $this->refineSearchResults($results, $filters, $categoryFamily);

        if ($results->isEmpty() && $categoryFamily !== null) {
            $results = $this->searchProductsByFamilyFallback($query, $filters, $categoryFamily);
            $results = $results->merge($semanticResults);
            $results = $this->refineSearchResults($results, $filters, $categoryFamily);
        }

        if ($results->isEmpty() && $outfitIntent) {
            $aoFilters = $filters;
            $aoFilters['category'] = 'ao';
            $quanFilters = $filters;
            $quanFilters['category'] = 'quan';

            $results = collect()
                ->merge($this->searchProductsByFamilyFallback($query, $aoFilters, 'ao'))
                ->merge($this->searchProductsByFamilyFallback($query, $quanFilters, 'quan'))
                ->merge($semanticResults);

            $results = $this->refineSearchResults($results, $filters, null);
        }

        if ($categoryFamily !== null) {
            $results = $results->filter(function ($item) use ($categoryFamily) {
                if (!is_array($item)) {
                    return false;
                }

                $itemFamily = $this->detectItemCategoryFamily($item);
                if ($itemFamily !== null) {
                    return $itemFamily === $categoryFamily;
                }

                return $this->matchesCategoryFamily($item, $categoryFamily);
            })->values();
        }

        if ($outfitIntent) {
            $results = $this->filterOutfitResults($results, $query, $filters);
        }

        $results = $results
            ->sortByDesc(function ($item) use ($query, $filters) {
                return $this->scoreSearchResult($item, $query, $filters);
            })
            ->filter(static function ($item) {
                return is_array($item) && !empty($item['id']);
            })
            ->unique('dedupe_key')
            ->values()
            ->take(max(1, $resultLimit));

        return $results;
    }

    private function searchSemanticProducts(string $query, array $filters)
    {
        try {
            $semanticService = new ProductSemanticSearchService();
            $scores = $semanticService->searchProductScores($query, $this->lang, $filters);
            if (empty($scores)) {
                return collect();
            }

            $products = $this->loadCurrentProductsByIds(array_keys($scores));

            return $products->map(function ($product) use ($query, $filters, $scores) {
                $item = $this->normalizeCurrentProduct($product, $query, $filters);
                $item['semantic_score'] = (float) ($scores[(int) $item['id']] ?? 0);

                return $item;
            });
        } catch (\Throwable $e) {
            return collect();
        }
    }

    private function refineSearchResults($results, array $filters, ?string $categoryFamily)
    {
        $results = collect($results)->filter(static function ($item) {
            return is_array($item) && !empty($item['id']);
        })->values();

        if ($results->isEmpty()) {
            return $results;
        }

        $strictResults = $results->filter(function ($item) use ($filters, $categoryFamily) {
            return $this->matchesStructuredFilters($item, $filters, $categoryFamily, true);
        })->values();
        if ($strictResults->isNotEmpty()) {
            return $strictResults;
        }

        $categoryResults = $results->filter(function ($item) use ($filters, $categoryFamily) {
            return $this->matchesStructuredFilters($item, $filters, $categoryFamily, false);
        })->values();
        if ($categoryResults->isNotEmpty()) {
            return $categoryResults;
        }

        if ($categoryFamily !== null) {
            $familyResults = $results->filter(function ($item) use ($categoryFamily) {
                return is_array($item) && $this->matchesCategoryFamily($item, $categoryFamily);
            })->values();
            if ($familyResults->isNotEmpty()) {
                return $familyResults;
            }
        }

        return $results;
    }

    private function detectCategoryFamily(string $query, array $filters): ?string
    {
        $normalizedQuery = $this->normalizeSearchText($query);
        $normalizedCategory = $this->normalizeSearchText((string) ($filters['category'] ?? ''));
        $normalized = trim($normalizedQuery . ' ' . $normalizedCategory);
        if ($normalized === '' && $normalizedQuery === '') {
            return null;
        }

        $isOutfitQuery = $this->isOutfitIntent($query, $filters);
        $queryHasQuan = $this->containsWholeTerm($normalizedQuery, 'quan');
        $queryHasAo = $this->containsWholeTerm($normalizedQuery, 'ao');
        $queryHasNon = $this->hasHatIntent($normalizedQuery);
        $queryHasDep = $this->hasFootwearIntent($normalizedQuery);

        if ($queryHasQuan && !$queryHasAo) {
            return 'quan';
        }

        if ($queryHasAo && !$queryHasQuan) {
            return 'ao';
        }

        if ($queryHasNon && !$queryHasQuan && !$queryHasAo) {
            return 'non';
        }

        if ($queryHasDep && !$queryHasQuan && !$queryHasAo && !$queryHasNon) {
            return 'dep';
        }

        if ($isOutfitQuery) {
            return null;
        }

        $hasQuan = $this->containsWholeTerm($normalized, 'quan');
        $hasAo = $this->containsWholeTerm($normalized, 'ao');

        if ($hasQuan && !$hasAo) {
            return 'quan';
        }

        if ($hasAo && !$hasQuan) {
            return 'ao';
        }

        $hasNon = $this->hasHatIntent($normalized);

        if ($hasNon && !$hasQuan && !$hasAo) {
            return 'non';
        }

        $hasDep = $this->hasFootwearIntent($normalized);

        if ($hasDep && !$hasQuan && !$hasAo && !$hasNon) {
            return 'dep';
        }

        return null;
    }

    private function isOutfitIntent(string $query, array $filters = []): bool
    {
        $normalizedQuery = $this->normalizeSearchText($query);
        $normalizedCategory = $this->normalizeSearchText((string) ($filters['category'] ?? ''));
        $combined = trim($normalizedQuery . ' ' . $normalizedCategory);

        return str_contains($normalizedQuery, 'outfit')
            || str_contains($normalizedQuery, 'mix do')
            || str_contains($normalizedQuery, 'phoi do')
            || str_contains($normalizedQuery, 'set do')
            || str_contains($combined, 'outfit');
    }

    private function hasHatIntent(string $normalizedText): bool
    {
        return $this->containsWholeTerm($normalizedText, 'non')
            || $this->containsWholeTerm($normalizedText, 'mu')
            || str_contains($normalizedText, 'bucket hat')
            || str_contains($normalizedText, 'baseball cap')
            || str_contains($normalizedText, 'snapback')
            || str_contains($normalizedText, 'beanie')
            || str_contains($normalizedText, 'mu luoi trai')
            || str_contains($normalizedText, 'mu bucket');
    }

    private function hasFootwearIntent(string $normalizedText): bool
    {
        return $this->containsWholeTerm($normalizedText, 'dep')
            || $this->containsWholeTerm($normalizedText, 'giay')
            || str_contains($normalizedText, 'sandal')
            || str_contains($normalizedText, 'slides')
            || str_contains($normalizedText, 'slide')
            || str_contains($normalizedText, 'slipper')
            || str_contains($normalizedText, 'flip flop')
            || str_contains($normalizedText, 'flipflop');
    }

    private function hasAccessoryIntent(string $normalizedText): bool
    {
        return $this->hasHatIntent($normalizedText)
            || $this->hasFootwearIntent($normalizedText)
            || str_contains($normalizedText, 'that lung')
            || str_contains($normalizedText, 'belt')
            || str_contains($normalizedText, 'balo')
            || str_contains($normalizedText, 'tui')
            || str_contains($normalizedText, 'bag')
            || str_contains($normalizedText, 'vi')
            || str_contains($normalizedText, 'wallet')
            || str_contains($normalizedText, 'mat kinh')
            || str_contains($normalizedText, 'kinh')
            || str_contains($normalizedText, 'vo')
            || str_contains($normalizedText, 'tat')
            || str_contains($normalizedText, 'phu kien');
    }

    private function matchesCategoryFamily(array $item, string $family): bool
    {
        $nameText = $this->normalizeSearchText($item['name'] ?? '');
        $categoryText = $this->normalizeSearchText($item['category'] ?? '');
        $tagText = $this->normalizeSearchText(implode(' ', $item['tags'] ?? []));
        $haystack = trim($nameText . ' ' . $categoryText . ' ' . $tagText);
        $primaryHaystack = trim($nameText . ' ' . $categoryText . ' ' . $tagText);

        if ($haystack === '') {
            return false;
        }

        $includeTerms = match ($family) {
            'quan' => ['quan', 'jean', 'kaki', 'jogger', 'baggy', 'cargo', 'short', 'shorts', 'trousers', 'pants', 'pant'],
            'ao' => ['ao', 'shirt', 'somi', 'so mi', 'polo', 'hoodie', 'sweater', 'vest', 'blazer', 'len', 'khoac', 'cardigan', 'tee'],
            'non' => ['non', 'bucket hat', 'baseball cap', 'snapback', 'beanie', 'mu luoi trai', 'mu bucket'],
            'dep' => ['dep', 'giay', 'sandal', 'slides', 'slide', 'slipper', 'flip flop', 'flipflop'],
            default => [],
        };
        $excludeTerms = match ($family) {
            'quan' => ['ao', 'somi', 'so mi', 'polo', 'hoodie', 'sweater', 'vest', 'blazer', 'cardigan', 'shirt', 'tee', 'thun'],
            'ao' => ['quan', 'jean', 'kaki', 'jogger', 'baggy', 'cargo', 'short', 'shorts', 'trousers', 'pants', 'pant'],
            'non' => ['ao', 'quan', 'somi', 'so mi', 'shirt', 'polo', 'hoodie', 'sweater', 'vest', 'blazer', 'cardigan', 'jean', 'kaki', 'jogger', 'baggy', 'cargo', 'short', 'shorts', 'trousers', 'pants', 'pant'],
            'dep' => ['ao', 'quan', 'non', 'mu', 'somi', 'so mi', 'shirt', 'polo', 'hoodie', 'sweater', 'vest', 'blazer', 'cardigan', 'jean', 'kaki', 'jogger', 'baggy', 'cargo'],
            default => [],
        };

        if ($primaryHaystack !== '') {
            if ($this->containsAnyTerm($primaryHaystack, $excludeTerms)) {
                return false;
            }

            if ($this->containsAnyTerm($primaryHaystack, $includeTerms)) {
                return true;
            }
        }

        if ($this->containsAnyTerm($haystack, $excludeTerms)) {
            return false;
        }

        if (!$this->containsAnyTerm($haystack, $includeTerms)) {
            return false;
        }

        return true;
    }

    private function detectItemCategoryFamily(array $item): ?string
    {
        $nameText = $this->normalizeSearchText($item['name'] ?? '');
        $categoryText = $this->normalizeSearchText($item['category'] ?? '');
        $tagText = $this->normalizeSearchText(implode(' ', $item['tags'] ?? []));
        $haystack = trim($nameText . ' ' . $categoryText . ' ' . $tagText);
        if ($haystack === '') {
            return null;
        }

        $hasDep = $this->containsWholeTerm($haystack, 'dep')
            || $this->containsWholeTerm($haystack, 'giay')
            || str_contains($haystack, 'sandal')
            || str_contains($haystack, 'slides')
            || str_contains($haystack, 'slide')
            || str_contains($haystack, 'slipper')
            || str_contains($haystack, 'flip flop')
            || str_contains($haystack, 'flipflop');
        if ($hasDep) {
            return 'dep';
        }

        $hasNon = $this->containsWholeTerm($haystack, 'non')
            || $this->containsWholeTerm($haystack, 'mu')
            || $this->containsWholeTerm($haystack, 'cap')
            || str_contains($haystack, 'bucket hat')
            || str_contains($haystack, 'baseball cap')
            || str_contains($haystack, 'snapback')
            || str_contains($haystack, 'beanie')
            || str_contains($haystack, 'mu luoi trai')
            || str_contains($haystack, 'mu bucket');
        if ($hasNon) {
            return 'non';
        }

        $hasQuan = $this->containsWholeTerm($haystack, 'quan')
            || $this->containsAnyTerm($haystack, ['jean', 'kaki', 'jogger', 'baggy', 'cargo', 'short', 'shorts', 'trousers', 'pants', 'pant']);
        $hasAo = $this->containsWholeTerm($haystack, 'ao')
            || $this->containsAnyTerm($haystack, ['shirt', 'somi', 'so mi', 'polo', 'hoodie', 'sweater', 'vest', 'blazer', 'len', 'khoac', 'cardigan', 'tee']);

        if ($hasQuan && !$hasAo) {
            return 'quan';
        }

        if ($hasAo && !$hasQuan) {
            return 'ao';
        }

        if ($this->containsWholeTerm($nameText, 'quan')) {
            return 'quan';
        }

        if ($this->containsWholeTerm($nameText, 'ao')) {
            return 'ao';
        }

        if ($this->containsWholeTerm($categoryText, 'quan')) {
            return 'quan';
        }

        if ($this->containsWholeTerm($categoryText, 'ao')) {
            return 'ao';
        }

        if ($this->containsWholeTerm($nameText, 'dep') || $this->containsWholeTerm($nameText, 'giay')) {
            return 'dep';
        }

        if ($this->containsWholeTerm($categoryText, 'dep') || $this->containsWholeTerm($categoryText, 'giay')) {
            return 'dep';
        }

        return null;
    }

    private function matchesStructuredFilters(array $item, array $filters, ?string $categoryFamily, bool $includeAttributes): bool
    {
        if ($categoryFamily !== null) {
            $itemFamily = $this->detectItemCategoryFamily($item);
            if ($itemFamily !== null) {
                if ($itemFamily !== $categoryFamily) {
                    return false;
                }
            } elseif (!$this->matchesCategoryFamily($item, $categoryFamily)) {
                return false;
            }
        }

        $category = trim((string) ($filters['category'] ?? ''));
        if ($this->shouldRequireSpecificCategory($category) && !$this->itemMatchesCategoryTerm($item, $category)) {
            return false;
        }

        if (!$includeAttributes) {
            return true;
        }

        foreach (['color', 'size'] as $key) {
            $value = trim((string) ($filters[$key] ?? ''));
            if ($value !== '' && !$this->itemMatchesTerm($item, $value, [$key, 'tags', 'name', 'description'])) {
                return false;
            }
        }

        return true;
    }

    private function shouldRequireSpecificCategory(string $category): bool
    {
        $normalized = $this->normalizeSearchText($category);
        if ($normalized === '') {
            return false;
        }

        return !in_array($normalized, ['ao', 'quan', 'non', 'mu', 'outfit', 'mix do', 'phoi do', 'set do'], true);
    }

    private function isApparelItem(array $item): bool
    {
        $family = $this->detectItemCategoryFamily($item);
        if ($family === 'ao' || $family === 'quan') {
            return true;
        }

        $haystack = $this->normalizeSearchText(trim(
            (string) ($item['name'] ?? '') . ' ' .
                (string) ($item['category'] ?? '') . ' ' .
                implode(' ', $item['tags'] ?? [])
        ));

        return $this->containsAnyTerm($haystack, [
            'ao',
            'shirt',
            'so mi',
            'somi',
            'thun',
            'polo',
            'hoodie',
            'sweater',
            'blazer',
            'quan',
            'jean',
            'kaki',
            'jogger',
            'short',
            'pants',
            'trousers',
        ]);
    }

    private function isAccessoryItem(array $item): bool
    {
        $family = $this->detectItemCategoryFamily($item);
        if ($family === 'non' || $family === 'dep') {
            return true;
        }

        $haystack = $this->normalizeSearchText(trim(
            (string) ($item['name'] ?? '') . ' ' .
                (string) ($item['category'] ?? '') . ' ' .
                implode(' ', $item['tags'] ?? [])
        ));

        return $this->containsAnyTerm($haystack, [
            'non',
            'mu',
            'cap',
            'hat',
            'bucket',
            'dep',
            'giay',
            'sandal',
            'slides',
            'slipper',
            'that lung',
            'belt',
            'balo',
            'backpack',
            'tui',
            'bag',
            'vi',
            'wallet',
            'mat kinh',
            'kinh',
            'vo',
            'tat',
            'sock',
            'phu kien',
        ]);
    }

    private function filterOutfitResults($results, string $query, array $filters)
    {
        $collection = collect($results)->filter(static function ($item) {
            return is_array($item) && !empty($item['id']);
        })->values();

        if ($collection->isEmpty() || !$this->isOutfitIntent($query, $filters)) {
            return $collection;
        }

        $normalizedQuery = $this->normalizeSearchText($query);
        $hasSpecificItemIntent = $this->containsWholeTerm($normalizedQuery, 'ao')
            || $this->containsWholeTerm($normalizedQuery, 'quan')
            || $this->hasAccessoryIntent($normalizedQuery);

        if ($hasSpecificItemIntent) {
            return $collection;
        }

        $apparelOnly = $collection->filter(function ($item) {
            return is_array($item) && $this->isApparelItem($item);
        })->values();
        if ($apparelOnly->isNotEmpty()) {
            return $apparelOnly;
        }

        $withoutAccessories = $collection->filter(function ($item) {
            return is_array($item) && !$this->isAccessoryItem($item);
        })->values();
        if ($withoutAccessories->isNotEmpty()) {
            return $withoutAccessories;
        }

        return $collection;
    }

    private function itemMatchesTerm(array $item, string $term, array $fields): bool
    {
        foreach ($fields as $field) {
            $value = $field === 'tags'
                ? implode(' ', $item['tags'] ?? [])
                : ($item[$field] ?? '');

            if ($this->textContainsSearchTerm((string) $value, $term)) {
                return true;
            }
        }

        return false;
    }

    private function itemMatchesCategoryTerm(array $item, string $category): bool
    {
        $normalizedCategory = $this->normalizeSearchText($category);
        if (in_array($normalizedCategory, ['dep', 'giay', 'giay dep'], true)) {
            foreach ($this->expandSpecificCategoryTerms($category) as $term) {
                if ($this->itemMatchesTerm($item, $term, ['name', 'category', 'tags'])) {
                    return true;
                }
            }

            return false;
        }

        $terms = $this->expandSpecificCategoryTerms($category);
        foreach ($terms as $term) {
            if ($this->itemMatchesTerm($item, $term, ['name', 'category', 'tags', 'description'])) {
                return true;
            }
        }

        return false;
    }

    private function expandSpecificCategoryTerms(string $category): array
    {
        $normalized = $this->normalizeSearchText($category);
        $variants = [$category];

        $aliases = match ($normalized) {
            'ao thun' => ['ao thun', 'thun', 'tee', 't shirt', 'tshirt'],
            'ao so mi' => ['ao so mi', 'ao somi', 'so mi', 'somi', 'shirt'],
            'ao polo' => ['ao polo', 'polo'],
            'ao khoac' => ['ao khoac', 'khoac', 'jacket', 'windbreaker', 'blazer'],
            'ao len' => ['ao len', 'ao ni', 'sweater', 'cardigan'],
            'ao hoodie', 'hoodie' => ['ao hoodie', 'hoodie', 'sweatshirt'],
            'ao sweater', 'sweater' => ['ao sweater', 'sweater', 'sweatshirt'],
            'quan short' => ['quan short', 'short', 'shorts'],
            'quan jean' => ['quan jean', 'quan jeans', 'jean', 'jeans', 'denim'],
            'quan kaki' => ['quan kaki', 'kaki', 'chino'],
            'quan tay' => ['quan tay', 'trouser', 'trousers'],
            'non', 'non luoi trai', 'mu', 'mu luoi trai' => ['non', 'mu', 'cap', 'baseball cap', 'bucket hat', 'snapback', 'beanie'],
            'dep', 'giay', 'giay dep' => ['dep', 'giay', 'sandal', 'slides', 'slide', 'slipper', 'flip flop', 'flipflop'],
            default => [$category],
        };

        $variants = array_merge($variants, $aliases);

        return array_values(array_unique(array_filter(array_map('trim', $variants))));
    }

    private function containsAnyTerm(string $haystack, array $terms): bool
    {
        foreach ($terms as $term) {
            if ($this->textContainsSearchTerm($haystack, (string) $term)) {
                return true;
            }
        }

        return false;
    }

    private function textContainsSearchTerm(mixed $haystack, string $term): bool
    {
        $normalizedHaystack = $this->normalizeSearchText($haystack);
        $needle = $this->normalizeSearchText($term);
        if ($normalizedHaystack === '' || $needle === '') {
            return false;
        }

        if (str_contains($needle, ' ')) {
            return str_contains($normalizedHaystack, $needle);
        }

        return $this->containsWholeTerm($normalizedHaystack, $needle);
    }

    private function searchCurrentProducts(string $query, array $filters)
    {
        $categoryFamily = $this->detectCategoryFamily($query, $filters);
        $builder = $this->makeCurrentProductBaseQuery();

        $this->applyCurrentProductFilters($builder, $query, $filters, $categoryFamily);

        return $builder
            ->orderBy('numb', 'asc')
            ->orderBy('id', 'desc')
            ->limit(self::SEARCH_CANDIDATE_LIMIT)
            ->get()
            ->map(function ($product) use ($query, $filters) {
                return $this->normalizeCurrentProduct($product, $query, $filters);
            });
    }

    private function searchProductsByFamilyFallback(string $query, array $filters, string $categoryFamily)
    {
        $builder = $this->makeCurrentProductBaseQuery();

        $terms = $this->expandCategoryFamilyTerms($categoryFamily);
        $terms[] = $filters['category'] ?? '';
        $terms = array_values(array_unique(array_filter(array_map(function ($term) {
            return trim((string) $term);
        }, $terms))));

        $categoryIds = $this->findCurrentCategoryIds($filters, $categoryFamily);

        $builder->where(function ($subQuery) use ($terms, $categoryIds) {
            foreach ($terms as $term) {
                $searchTerm = $this->normalizeSearchText($term);
                if ($searchTerm === '') {
                    continue;
                }

                $subQuery->orWhere('name' . $this->lang, 'like', '%' . $searchTerm . '%')
                    ->orWhere('desc' . $this->lang, 'like', '%' . $searchTerm . '%')
                    ->orWhere('slug' . $this->lang, 'like', '%' . $searchTerm . '%');
            }

            if (!empty($categoryIds['list'])) {
                $subQuery->orWhereIn('id_list', $categoryIds['list']);
            }
            if (!empty($categoryIds['cat'])) {
                $subQuery->orWhereIn('id_cat', $categoryIds['cat']);
            }
            if (!empty($categoryIds['item'])) {
                $subQuery->orWhereIn('id_item', $categoryIds['item']);
            }
            if (!empty($categoryIds['sub'])) {
                $subQuery->orWhereIn('id_sub', $categoryIds['sub']);
            }
        });

        return $builder
            ->orderBy('numb', 'asc')
            ->orderBy('id', 'desc')
            ->limit(self::SEARCH_CANDIDATE_LIMIT)
            ->get()
            ->map(function ($product) use ($query, $filters) {
                return $this->normalizeCurrentProduct($product, $query, $filters);
            });
    }

    private function makeCurrentProductBaseQuery()
    {
        return ProductModel::select(
            'id',
            'name' . $this->lang,
            'desc' . $this->lang,
            'slug' . $this->lang,
            'photo',
            'icon',
            'regular_price',
            'sale_price',
            'discount',
            'type',
            'properties',
            'id_list',
            'id_cat',
            'id_item',
            'id_sub'
        )->with([
            'getPhotos' => function ($q) {
                $q->where('type', 'san-pham')->orderBy('numb', 'asc');
            },
            'getCategoryList',
            'getCategoryCat',
            'getCategoryItem',
            'getCategorySub',
        ])->where('type', 'san-pham')->whereRaw("FIND_IN_SET(?,status)", ['hienthi']);
    }

    private function loadCurrentProductsByIds(array $ids)
    {
        $ids = array_values(array_unique(array_map('intval', $ids)));
        if (empty($ids)) {
            return collect();
        }

        $builder = $this->makeCurrentProductBaseQuery();
        $products = $builder->whereIn('id', $ids)->get()->keyBy('id');

        return collect($ids)->map(function ($id) use ($products) {
            return $products->get($id);
        })->filter()->values();
    }

    private function applyCurrentProductFilters($builder, string $query, array $filters, ?string $categoryFamily = null): void
    {
        $priceField = 'CASE WHEN sale_price > 0 THEN sale_price ELSE regular_price END';
        $this->applyPriceFilter($builder, $priceField, $filters);

        $searchTerms = $this->collectSearchTerms($query, $filters, $categoryFamily);
        $propertyIds = $this->findCurrentPropertyIds($filters);
        $categoryIds = $this->findCurrentCategoryIds($filters, $categoryFamily);
        $exactFamilyIds = $this->resolveExactFamilyCategoryIds($categoryFamily);

        if (!empty($exactFamilyIds['list']) || !empty($exactFamilyIds['cat']) || !empty($exactFamilyIds['item']) || !empty($exactFamilyIds['sub'])) {
            $builder->where(function ($familyQuery) use ($exactFamilyIds) {
                if (!empty($exactFamilyIds['list'])) {
                    $familyQuery->orWhereIn('id_list', $exactFamilyIds['list']);
                }
                if (!empty($exactFamilyIds['cat'])) {
                    $familyQuery->orWhereIn('id_cat', $exactFamilyIds['cat']);
                }
                if (!empty($exactFamilyIds['item'])) {
                    $familyQuery->orWhereIn('id_item', $exactFamilyIds['item']);
                }
                if (!empty($exactFamilyIds['sub'])) {
                    $familyQuery->orWhereIn('id_sub', $exactFamilyIds['sub']);
                }
            });
        }

        if (empty($searchTerms) && empty($propertyIds) && empty($categoryIds)) {
            return;
        }

        $builder->where(function ($subQuery) use ($searchTerms, $propertyIds, $categoryIds) {
            foreach ($searchTerms as $term) {
                $subQuery->orWhere('name' . $this->lang, 'like', '%' . $term . '%')
                    ->orWhere('desc' . $this->lang, 'like', '%' . $term . '%')
                    ->orWhere('slug' . $this->lang, 'like', '%' . $term . '%');
            }

            if (!empty($propertyIds)) {
                foreach ($propertyIds as $propertyId) {
                    $subQuery->orWhereRaw('FIND_IN_SET(?, properties)', [$propertyId]);
                }
            }

            if (!empty($categoryIds['list'])) {
                $subQuery->orWhereIn('id_list', $categoryIds['list']);
            }
            if (!empty($categoryIds['cat'])) {
                $subQuery->orWhereIn('id_cat', $categoryIds['cat']);
            }
            if (!empty($categoryIds['item'])) {
                $subQuery->orWhereIn('id_item', $categoryIds['item']);
            }
            if (!empty($categoryIds['sub'])) {
                $subQuery->orWhereIn('id_sub', $categoryIds['sub']);
            }
        });
    }

    private function findCurrentPropertyIds(array $filters): array
    {
        $terms = array_values(array_filter([
            $filters['category'] ?? '',
            $filters['color'] ?? '',
            $filters['size'] ?? '',
            $filters['style'] ?? '',
            $filters['material'] ?? '',
            $filters['occasion'] ?? '',
        ]));

        $ids = [];
        foreach ($terms as $term) {
            $searchTerms = array_values(array_unique(array_filter([
                $term,
                $this->normalizeSearchText($term),
            ])));
            $matched = collect();
            foreach ($searchTerms as $searchTerm) {
                $matched = $matched->merge(
                    PropertiesModel::select('id')
                        ->where('name' . $this->lang, 'like', '%' . $searchTerm . '%')
                        ->orWhere('slug' . $this->lang, 'like', '%' . $searchTerm . '%')
                        ->pluck('id')
                );
            }
            $matched = $matched->unique()->values()->toArray();
            $ids = array_merge($ids, $matched);
        }

        return array_values(array_unique(array_map('intval', $ids)));
    }

    private function findCurrentCategoryIds(array $filters, ?string $categoryFamily = null): array
    {
        $categoryTerm = trim((string) ($filters['category'] ?? ''));
        if ($this->normalizeSearchText($categoryTerm) === 'outfit') {
            $categoryTerm = '';
        }

        $terms = array_values(array_filter(array_merge([
            $categoryTerm,
        ], $this->expandCategoryFamilyTerms($categoryFamily))));

        $ids = [
            'list' => [],
            'cat' => [],
            'item' => [],
            'sub' => [],
        ];

        $exactFamilyIds = $this->resolveExactFamilyCategoryIds($categoryFamily);
        foreach ($ids as $key => $values) {
            if (!empty($exactFamilyIds[$key])) {
                $ids[$key] = array_values(array_unique(array_merge($ids[$key], $exactFamilyIds[$key])));
            }
        }

        foreach ($terms as $term) {
            $searchTerms = array_values(array_unique(array_filter([
                $term,
                $this->normalizeSearchText($term),
            ])));
            $listIds = collect();
            $catIds = collect();
            $itemIds = collect();
            $subIds = collect();
            foreach ($searchTerms as $searchTerm) {
                $listIds = $listIds->merge(
                    ProductListModel::select('id')
                        ->where('name' . $this->lang, 'like', '%' . $searchTerm . '%')
                        ->orWhere('slug' . $this->lang, 'like', '%' . $searchTerm . '%')
                        ->pluck('id')
                );
                $catIds = $catIds->merge(
                    ProductCatModel::select('id')
                        ->where('name' . $this->lang, 'like', '%' . $searchTerm . '%')
                        ->orWhere('slug' . $this->lang, 'like', '%' . $searchTerm . '%')
                        ->pluck('id')
                );
                $itemIds = $itemIds->merge(
                    ProductItemModel::select('id')
                        ->where('name' . $this->lang, 'like', '%' . $searchTerm . '%')
                        ->orWhere('slug' . $this->lang, 'like', '%' . $searchTerm . '%')
                        ->pluck('id')
                );
                $subIds = $subIds->merge(
                    ProductSubModel::select('id')
                        ->where('name' . $this->lang, 'like', '%' . $searchTerm . '%')
                        ->orWhere('slug' . $this->lang, 'like', '%' . $searchTerm . '%')
                        ->pluck('id')
                );
            }

            $ids['list'] = array_merge($ids['list'], $listIds->unique()->values()->toArray());
            $ids['cat'] = array_merge($ids['cat'], $catIds->unique()->values()->toArray());
            $ids['item'] = array_merge($ids['item'], $itemIds->unique()->values()->toArray());
            $ids['sub'] = array_merge($ids['sub'], $subIds->unique()->values()->toArray());
        }

        if ($categoryFamily === 'dep') {
            $ids['list'] = [];
        }

        return [
            'list' => array_values(array_unique(array_map('intval', $ids['list']))),
            'cat' => array_values(array_unique(array_map('intval', $ids['cat']))),
            'item' => array_values(array_unique(array_map('intval', $ids['item']))),
            'sub' => array_values(array_unique(array_map('intval', $ids['sub']))),
        ];
    }

    private function resolveExactFamilyCategoryIds(?string $categoryFamily): array
    {
        if ($categoryFamily === null) {
            return [
                'list' => [],
                'cat' => [],
                'item' => [],
                'sub' => [],
            ];
        }

        $familySlug = match ($categoryFamily) {
            'quan' => 'quan',
            'ao' => 'ao',
            'non' => 'non',
            'dep' => 'dep',
            default => '',
        };

        if ($familySlug === '') {
            return [
                'list' => [],
                'cat' => [],
                'item' => [],
                'sub' => [],
            ];
        }

        $ids = [
            'list' => [],
            'cat' => [],
            'item' => [],
            'sub' => [],
        ];

        foreach (['list' => ProductListModel::class, 'cat' => ProductCatModel::class, 'item' => ProductItemModel::class, 'sub' => ProductSubModel::class] as $key => $modelClass) {
            try {
                $query = $modelClass::select('id')->where('slug' . $this->lang, 'like', '%' . $familySlug . '%');
                $found = $query->pluck('id')->map(static function ($value) {
                    return (int) $value;
                })->toArray();
                $ids[$key] = array_values(array_unique(array_merge($ids[$key], $found)));
            } catch (\Throwable $e) {
                // Ignore lookup failures and fall back to text matching.
            }
        }

        return $ids;
    }

    private function normalizeCurrentProduct($product, string $query, array $filters): array
    {
        $price = $this->effectiveCurrentPrice($product);
        $name = trim((string) ($product['name' . $this->lang] ?? ''));
        $description = trim((string) ($product['desc' . $this->lang] ?? ''));
        $slug = trim((string) ($product['slug' . $this->lang] ?? ''));
        $image = $this->resolveCurrentProductImage($product);
        $category = $this->resolveCurrentCategoryLabel($product);
        $tags = $this->resolveCurrentProductTags($product, $category);

        return [
            'id' => (int) $product['id'],
            'source' => 'current',
            'dedupe_key' => 'current:' . (int) $product['id'],
            'name' => $name,
            'category' => $category,
            'color' => $this->resolveCurrentProductPropertyValue($product, ['mau', 'color']),
            'size' => $this->resolveCurrentProductPropertyValue($product, ['size', 'kich-co', 'kich-thuoc']),
            'style' => $this->resolveCurrentProductPropertyValue($product, ['kieu', 'style', 'form']),
            'material' => $this->resolveCurrentProductPropertyValue($product, ['chat-lieu', 'material']),
            'occasion' => $this->resolveCurrentProductPropertyValue($product, ['hoan-canh', 'occasion', 'phong-cach']),
            'price' => (int) $price,
            'price_text' => Func::formatMoney($price),
            'image' => $image,
            'url' => $slug !== '' ? url('slugweb', ['slug' => $slug]) : '#',
            'description' => $description,
            'tags' => $tags,
            'semantic_score' => 0.0,
            'html' => $this->renderCurrentProductHtml($product),
        ];
    }

    private function effectiveCurrentPrice($product): int
    {
        $salePrice = (int) ($product['sale_price'] ?? 0);
        $regularPrice = (int) ($product['regular_price'] ?? 0);

        return $salePrice > 0 ? $salePrice : $regularPrice;
    }

    private function resolveCurrentProductImage($product): string
    {
        $photo = trim((string) ($product['photo'] ?? ''));
        $photos = $product->getPhotos ?? collect();

        if ($photos instanceof \Illuminate\Database\Eloquent\Relations\HasMany) {
            $photos = $photos->get();
        }

        if ($photos instanceof \Illuminate\Support\Collection) {
            $firstGallery = trim((string) data_get($photos->first(), 'photo', ''));
            if ($firstGallery !== '') {
                $photo = $firstGallery;
            }
        }

        return $photo !== '' ? upload('product', $photo) : '';
    }

    private function resolveCurrentCategoryLabel($product): string
    {
        $relations = [
            'getCategorySub',
            'getCategoryItem',
            'getCategoryCat',
            'getCategoryList',
        ];

        foreach ($relations as $relation) {
            $name = trim((string) data_get($product, $relation . '.name' . $this->lang, ''));
            if ($name !== '') {
                return $name;
            }
        }

        return '';
    }

    private function resolveCurrentProductTags($product, string $category = ''): array
    {
        $tags = array_values(array_filter([
            trim((string) $category),
        ]));

        $propertyTags = $this->resolveCurrentProductPropertyTags($product);
        $tags = array_merge($tags, $propertyTags);

        return array_values(array_unique(array_filter(array_map(static function ($value) {
            return trim((string) $value);
        }, $tags))));
    }

    private function resolveCurrentProductPropertyTags($product): array
    {
        $propertyIds = array_values(array_unique(array_filter(array_map('intval', explode(',', (string) ($product['properties'] ?? ''))))));
        if (empty($propertyIds)) {
            return [];
        }

        try {
            $query = PropertiesListModel::select('type', 'id', 'name' . $this->lang)
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
                    $label = trim((string) ($property['name' . $this->lang] ?? ''));
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

    private function resolveCurrentProductPropertyValue($product, array $keywords): string
    {
        $labels = $this->resolveCurrentProductPropertyTags($product);
        if (empty($labels)) {
            return '';
        }

        $normalizedKeywords = array_values(array_filter(array_map([$this, 'normalizeSearchText'], $keywords)));
        foreach ($labels as $label) {
            $normalizedLabel = $this->normalizeSearchText($label);
            foreach ($normalizedKeywords as $keyword) {
                if ($keyword !== '' && str_contains($normalizedLabel, $keyword)) {
                    return $label;
                }
            }
        }

        return '';
    }

    private function renderCurrentProductHtml($product): string
    {
        try {
            return app()->make('view')->render('component.itemProduct', [
                'product' => $product,
                'lang' => $this->lang,
                'sluglang' => $this->sluglang,
            ]);
        } catch (\Throwable $e) {
            return '';
        }
    }

    private function applyPriceFilter($builder, string $priceField, array $filters): void
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

    private function collectSearchTerms(string $query, array $filters, ?string $categoryFamily = null): array
    {
        $categoryTerm = trim((string) ($filters['category'] ?? ''));
        if ($this->normalizeSearchText($categoryTerm) === 'outfit') {
            $categoryTerm = '';
        }

        $terms = array_filter([
            $categoryTerm,
            $filters['color'] ?? '',
            $filters['size'] ?? '',
            $filters['style'] ?? '',
            $filters['material'] ?? '',
            $filters['occasion'] ?? '',
        ]);

        $queryTerm = trim($query);
        if ($queryTerm !== '') {
            $terms[] = $queryTerm;
        }

        $terms = array_merge($terms, $this->expandCategoryFamilyTerms($categoryFamily));

        $expanded = [];
        foreach ($terms as $term) {
            $term = trim((string) $term);
            if ($term === '') {
                continue;
            }
            $expanded[] = $term;
            $normalizedTerm = $this->normalizeText($term);
            if ($categoryFamily === null || mb_strlen($normalizedTerm, 'UTF-8') > 3) {
                $expanded[] = $normalizedTerm;
            }
        }

        $expanded = array_values(array_unique(array_filter($expanded, static function ($value) {
            return trim((string) $value) !== '';
        })));

        return $expanded;
    }

    private function expandCategoryFamilyTerms(?string $family): array
    {
        return match ($family) {
            'quan' => ['quần', 'quan', 'quần short', 'quan short', 'quần jean', 'quan jean', 'quần tây', 'quan tay', 'quần kaki', 'quan kaki', 'quần jogger', 'quan jogger', 'quần baggy', 'quan baggy', 'quần cargo', 'quan cargo', 'short', 'shorts', 'pants', 'trousers', 'pant'],
            'ao' => ['áo', 'ao', 'áo sơ mi', 'ao so mi', 'áo thun', 'ao thun', 'áo polo', 'ao polo', 'áo khoác', 'ao khoac', 'áo len', 'ao len', 'hoodie', 'sweater', 'vest', 'blazer', 'cardigan', 'tee'],
            'non' => ['nón', 'non', 'mũ', 'nón lưỡi trai', 'non luoi trai', 'mũ lưỡi trai', 'mu luoi trai', 'nón bucket', 'non bucket', 'mũ bucket', 'mu bucket', 'bucket hat', 'baseball cap', 'snapback', 'beanie'],
            'dep' => ['dép', 'dep', 'giày', 'giay', 'sandal', 'slides', 'slide', 'slipper', 'flip flop', 'flipflop'],
            default => [],
        };
    }

    private function containsWholeTerm(string $text, string $term): bool
    {
        $needle = $this->normalizeSearchText($term);
        if ($needle === '') {
            return false;
        }

        return preg_match('/(^|\s)' . preg_quote($needle, '/') . '(\s|$)/u', $text) === 1;
    }

    private function scoreSearchResult(array $item, string $query, array $filters): int
    {
        $queryText = $this->normalizeSearchText($query);
        $nameText = $this->normalizeSearchText($item['name'] ?? '');
        $categoryText = $this->normalizeSearchText($item['category'] ?? '');
        $descriptionText = $this->normalizeSearchText($item['description'] ?? '');
        $tagText = $this->normalizeSearchText(implode(' ', $item['tags'] ?? []));
        $haystack = trim($nameText . ' ' . $categoryText . ' ' . $descriptionText . ' ' . $tagText);
        $score = 0;
        $categoryFamily = $this->detectCategoryFamily($query, $filters);

        if ($queryText !== '' && str_contains($haystack, $queryText)) {
            $score += 120;
        }

        $semanticScore = max(0, (float) ($item['semantic_score'] ?? 0));
        if ($semanticScore > 0) {
            $score += (int) round($semanticScore * (int) config('ai_search.semantic_weight', 180));
        }

        foreach (['category' => 60, 'color' => 25, 'size' => 25, 'style' => 20, 'material' => 15, 'occasion' => 30] as $key => $weight) {
            $term = (string) ($filters[$key] ?? '');
            if ($term !== '' && $this->itemMatchesTerm($item, $term, [$key, 'category', 'name', 'tags', 'description'])) {
                $score += $weight;
            }
        }

        if ($categoryText !== '') {
            $categoryTerm = (string) ($filters['category'] ?? '');
            $normalizedCategoryTerm = $this->normalizeSearchText($categoryTerm);
            if ($categoryTerm !== '' && $this->itemMatchesCategoryTerm(['category' => $item['category'] ?? '', 'name' => '', 'tags' => [], 'description' => ''], $categoryTerm)) {
                $score += 100;
            }
            if ($categoryTerm !== '' && $this->itemMatchesCategoryTerm(['category' => '', 'name' => $item['name'] ?? '', 'tags' => [], 'description' => ''], $categoryTerm)) {
                $score += 70;
            }
            if ($categoryTerm !== '' && $this->itemMatchesCategoryTerm(['category' => '', 'name' => '', 'tags' => $item['tags'] ?? [], 'description' => $item['description'] ?? ''], $categoryTerm)) {
                $score += 20;
            }

            if ($normalizedCategoryTerm === 'quan' && $this->textContainsSearchTerm($item['name'] ?? '', 'ao') && !$this->textContainsSearchTerm($item['name'] ?? '', 'quan')) {
                $score -= 40;
            }
            if ($normalizedCategoryTerm === 'quan' && $this->textContainsSearchTerm($item['category'] ?? '', 'ao') && !$this->textContainsSearchTerm($item['category'] ?? '', 'quan')) {
                $score -= 40;
            }
        }

        if ($categoryFamily !== null) {
            $score += $this->matchesCategoryFamily($item, $categoryFamily) ? 90 : -120;
        }

        if ($this->isOutfitIntent($query, $filters)) {
            $normalizedQuery = $this->normalizeSearchText($query);
            if (!$this->hasAccessoryIntent($normalizedQuery)) {
                $itemFamily = $this->detectItemCategoryFamily($item);
                if ($itemFamily === 'ao' || $itemFamily === 'quan') {
                    $score += 80;
                } elseif ($itemFamily === 'non') {
                    $score -= 140;
                } elseif ($this->isAccessoryItem($item)) {
                    $score -= 90;
                }
            }
        }

        return $score;
    }
}
