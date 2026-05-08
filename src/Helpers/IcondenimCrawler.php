<?php

namespace LARAVEL\Helpers;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMXPath;
use RuntimeException;

class IcondenimCrawler
{
    protected string $collectionUrl;
    protected ?array $categoryDefinitions = null;

    public function __construct(string $collectionUrl = 'https://icondenim.com/collections/tat-ca-san-pham')
    {
        $this->collectionUrl = rtrim($collectionUrl, '/');
    }

    public function getCollectionUrl(): string
    {
        return $this->collectionUrl;
    }

    public function collectPendingProducts(?int $limit, callable $existsBySlug, int $maxPages = 50): array
    {
        $pending = [];
        $seen = [];
        $scanAll = $limit === null || $limit < 1;

        for ($page = 1; $page <= $maxPages && ($scanAll || count($pending) < $limit); $page++) {
            $url = $page === 1 ? $this->collectionUrl : $this->collectionUrl . '?page=' . $page;
            $html = $this->fetch($url);
            $productCards = $this->extractProductCards($html);

            if (empty($productCards)) {
                break;
            }

            foreach ($productCards as $productCard) {
                $slug = trim((string) ($productCard['slug'] ?? ''));
                $productUrl = trim((string) ($productCard['url'] ?? ''));

                if ($slug === '' || $productUrl === '' || isset($seen[$slug])) {
                    continue;
                }

                $seen[$slug] = true;

                if ($existsBySlug($slug)) {
                    continue;
                }

                $pending[] = [
                    'page' => $page,
                    'slug' => $slug,
                    'url' => $productUrl,
                    'preview_image_urls' => array_values(array_filter((array) ($productCard['preview_image_urls'] ?? []))),
                ];

                if (!$scanAll && count($pending) >= $limit) {
                    break;
                }
            }
        }

        return $pending;
    }

    public function findProductPreviewImages(string $slug, int $maxPages = 50): array
    {
        $slug = trim($slug);

        if ($slug === '') {
            return [];
        }

        for ($page = 1; $page <= $maxPages; $page++) {
            $url = $page === 1 ? $this->collectionUrl : $this->collectionUrl . '?page=' . $page;
            $html = $this->fetch($url);
            $productCards = $this->extractProductCards($html);

            if (empty($productCards)) {
                break;
            }

            foreach ($productCards as $productCard) {
                if (($productCard['slug'] ?? '') !== $slug) {
                    continue;
                }

                return array_values(array_filter((array) ($productCard['preview_image_urls'] ?? [])));
            }
        }

        return [];
    }

    public function parseProduct(string $url): array
    {
        $html = $this->fetch($url);
        $dom = $this->loadHtml($html);
        $xpath = new DOMXPath($dom);
        $text = $this->htmlToText($html);
        $slug = $this->extractSlugFromUrl($url);
        $title = $this->extractTitle($xpath, $slug);
        $code = $this->extractProductCode($text, $slug);

        [$regularPrice, $salePrice, $discount] = $this->extractPrices($text);
        $productJson = $this->extractProductJson($html);
        $variantMeta = $this->extractVariantMeta($productJson, $slug, $code);

        $colorBlock = $this->extractTextBlock($text, 'Màu sắc:', [
            'Kích thước:',
            'Tìm sản phẩm tại cửa hàng',
            'Thêm vào giỏ',
            'Mua ngay',
            'Mô tả',
        ]);
        $sizeBlock = $this->extractTextBlock($text, 'Kích thước:', [
            'Tìm sản phẩm tại cửa hàng',
            'Thêm vào giỏ',
            'Mua ngay',
            'Mô tả',
        ]);
        $descriptionBlock = $this->extractTextBlock($text, 'Mô tả', [
            'Chính sách giao hàng',
            'Chính sách đổi hàng',
            'TÌM SẢN PHẨM CỬA HÀNG',
        ]);

        $colors = $this->extractColors($colorBlock);
        $sizes = $this->extractSizes($sizeBlock);
        if (!empty($variantMeta['colors'])) {
            $colors = $variantMeta['colors'];
        }
        if (!empty($variantMeta['sizes'])) {
            $sizes = $variantMeta['sizes'];
        }
        $descriptionText = trim($descriptionBlock);
        $descriptionHtml = $this->convertTextToHtml($descriptionText);
        [$descriptionText, $descriptionHtml] = $this->extractDescriptionContent($xpath, $text);
        $images = $this->extractProductImages($xpath, $slug, $code);
        if (!empty($variantMeta['image_urls'])) {
            $images = $this->mergeUniqueImageUrls($images, $variantMeta['image_urls']);
        }
        $category = $this->extractCategory($xpath, $title, $code, $slug);

        return [
            'source_url' => $url,
            'source_slug' => $slug,
            'name' => $title,
            'code' => $code,
            'regular_price' => $regularPrice,
            'sale_price' => $salePrice,
            'discount' => $discount,
            'colors' => $colors,
            'sizes' => $sizes,
            'description_text' => $descriptionText,
            'description_html' => $descriptionHtml,
            'image_urls' => $images,
            'color_image_map' => $variantMeta['color_image_map'] ?? [],
            'category' => $category,
        ];
    }

    protected function fetch(string $url): string
    {
        $ch = curl_init($url);

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => 45,
            CURLOPT_CONNECTTIMEOUT => 20,
            CURLOPT_MAXREDIRS => 5,
            CURLOPT_ENCODING => '',
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36',
            CURLOPT_HTTPHEADER => [
                'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
                'Accept-Language: vi-VN,vi;q=0.9,en-US;q=0.8,en;q=0.7',
                'Cache-Control: no-cache',
                'Pragma: no-cache',
            ],
        ]);

        $html = curl_exec($ch);
        $statusCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($html === false || $statusCode >= 400) {
            throw new RuntimeException('Không thể tải URL: ' . $url . ($error ? ' - ' . $error : ''));
        }

        return (string) $html;
    }

    protected function loadHtml(string $html): DOMDocument
    {
        libxml_use_internal_errors(true);
        $dom = new DOMDocument();
        $dom->loadHTML('<?xml encoding="utf-8" ?>' . $html, LIBXML_NOERROR | LIBXML_NOWARNING);
        libxml_clear_errors();

        return $dom;
    }

    protected function extractProductCards(string $html): array
    {
        $dom = $this->loadHtml($html);
        $xpath = new DOMXPath($dom);
        $cards = [];

        foreach ($xpath->query('//a[@href]') as $node) {
            if (!$node instanceof DOMElement) {
                continue;
            }

            $href = trim((string) $node->getAttribute('href'));
            $normalized = $this->normalizeUrl($href);

            if ($normalized === '' || !str_contains($normalized, '/products/')) {
                continue;
            }

            $slug = $this->extractSlugFromUrl($normalized);
            if ($slug === '') {
                continue;
            }

            $previewImageUrls = $this->extractPreviewImagesFromProductLink($node);

            if (empty($cards[$slug])) {
                $cards[$slug] = [
                    'slug' => $slug,
                    'url' => $normalized,
                    'preview_image_urls' => $previewImageUrls,
                ];
                continue;
            }

            if (count($previewImageUrls) > count((array) ($cards[$slug]['preview_image_urls'] ?? []))) {
                $cards[$slug]['preview_image_urls'] = $previewImageUrls;
            }
        }

        return array_values($cards);
    }

    protected function extractPreviewImagesFromProductLink(DOMElement $node): array
    {
        $groupedUrls = [
            0 => [],
            1 => [],
            2 => [],
        ];

        foreach ($node->getElementsByTagName('img') as $imageNode) {
            if (!$imageNode instanceof DOMElement) {
                continue;
            }

            $className = strtolower(trim((string) $imageNode->getAttribute('class')));
            $group = 2;

            if (str_contains($className, 'img-first')) {
                $group = 0;
            } elseif (str_contains($className, 'img-hover')) {
                $group = 1;
            }

            $candidates = [
                $imageNode->getAttribute('src'),
                $imageNode->getAttribute('data-src'),
                $imageNode->getAttribute('data-image'),
            ];

            foreach ($candidates as $candidate) {
                $imageUrl = $this->normalizeUrl((string) $candidate);

                if (!$this->passesBaseProductImageChecks($imageUrl)) {
                    continue;
                }

                $groupedUrls[$group][$imageUrl] = $imageUrl;
            }
        }

        return array_values(array_merge($groupedUrls[0], $groupedUrls[1], $groupedUrls[2]));
    }

    protected function extractTitle(DOMXPath $xpath, string $slug): string
    {
        $titleNode = $xpath->query('//h1')->item(0);
        $title = trim((string) ($titleNode?->textContent ?? ''));

        if ($title !== '') {
            return $title;
        }

        $metaTitle = trim((string) $xpath->evaluate('string(//meta[@property="og:title"]/@content)'));

        if ($metaTitle !== '') {
            return $metaTitle;
        }

        return ucwords(str_replace('-', ' ', $slug));
    }

    protected function extractProductCode(string $text, string $slug): string
    {
        if (preg_match('/(?:Mã sản phẩm|SKU)\s*:\s*([A-Z0-9\-]+)/iu', $text, $matches)) {
            return $this->normalizeProductCode((string) $matches[1]);
        }

        return $this->normalizeProductCode(strtoupper(str_replace('-', '', $slug)));
    }

    protected function normalizeProductCode(string $code): string
    {
        $code = strtoupper(trim($code));
        $code = preg_replace('/\s+/u', '', $code);

        if (!is_string($code) || $code === '') {
            return '';
        }

        if (preg_match('/^([A-Z][A-Z0-9]*\d[A-Z0-9]*)-\d{1,3}$/', $code, $matches)) {
            return (string) $matches[1];
        }

        return $code;
    }

    protected function extractPrices(string $text): array
    {
        preg_match_all('/(\d{1,3}(?:[\.,]\d{3})+)\s*₫/u', $text, $matches);
        $prices = [];

        foreach ($matches[1] ?? [] as $price) {
            $prices[] = (int) str_replace(['.', ','], '', $price);
        }

        $prices = array_values(array_filter($prices));

        if (empty($prices)) {
            return [0, 0, 0];
        }

        $current = (int) ($prices[0] ?? 0);
        $compare = (int) ($prices[1] ?? 0);

        if ($compare > $current) {
            $regularPrice = $compare;
            $salePrice = $current;
        } else {
            $regularPrice = $current;
            $salePrice = 0;
        }

        $discount = 0;

        if ($salePrice > 0 && $regularPrice > $salePrice) {
            $discount = (int) round((($regularPrice - $salePrice) / $regularPrice) * 100);
        }

        return [$regularPrice, $salePrice, $discount];
    }

    protected function extractColors(string $block): array
    {
        $lines = $this->normalizeLines($block);
        $colors = [];

        foreach ($lines as $line) {
            $cleaned = preg_replace('/\s*\/\s*\d+.*$/u', '', $line);
            $cleaned = preg_replace('/-\d{3,}.*$/u', '', (string) $cleaned);
            $cleaned = trim((string) $cleaned, " \t\n\r\0\x0B-");

            if ($cleaned === '' || preg_match('/^(S|M|L|XL|XXL|XXXL|\d{2,3})$/iu', $cleaned)) {
                continue;
            }

            if (!preg_match('/\p{L}/u', $cleaned)) {
                continue;
            }

            if (preg_match('/[:;,.!?]/u', $cleaned)) {
                continue;
            }

            if (mb_strlen($cleaned) > 40) {
                continue;
            }

            $wordCount = count(array_filter(explode(' ', preg_replace('/\s+/u', ' ', $cleaned))));

            if ($wordCount > 5) {
                continue;
            }

            $colors[$this->normalizeKey($cleaned)] = $cleaned;
        }

        return array_values($colors);
    }

    protected function extractSizes(string $block): array
    {
        preg_match_all('/\b(?:\d{2,3}|XS|S|M|L|XL|XXL|XXXL|FREE|FREESIZE)\b/iu', $block, $matches);
        $sizes = [];

        foreach ($matches[0] ?? [] as $size) {
            $cleaned = strtoupper(trim((string) $size));
            $sizes[$cleaned] = $cleaned;
        }

        return array_values($sizes);
    }

    protected function extractTextBlock(string $text, string $start, array $ends = []): string
    {
        $startPosition = mb_stripos($text, $start);

        if ($startPosition === false) {
            return '';
        }

        $block = mb_substr($text, $startPosition + mb_strlen($start));
        $endPosition = null;

        foreach ($ends as $end) {
            $candidate = mb_stripos($block, $end);

            if ($candidate !== false && ($endPosition === null || $candidate < $endPosition)) {
                $endPosition = $candidate;
            }
        }

        if ($endPosition !== null) {
            $block = mb_substr($block, 0, $endPosition);
        }

        return trim($block);
    }

    protected function extractDescriptionContent(DOMXPath $xpath, string $text): array
    {
        $descriptionHtml = $this->normalizeDescriptionHtml($this->extractDescriptionHtmlFromDom($xpath));

        if ($descriptionHtml !== '') {
            $descriptionText = $this->cleanupDescriptionText($this->htmlToText($descriptionHtml));
            $descriptionText = $this->stripLeadingDescriptionLabel($descriptionText);

            if ($descriptionText !== '' || $this->descriptionHtmlHasRenderableContent($descriptionHtml)) {
                return [$descriptionText, $descriptionHtml];
            }
        }

        $descriptionBlock = $this->extractTextBlock($text, 'MÃ´ táº£', [
            'ChÃ­nh sÃ¡ch giao hÃ ng',
            'ChÃ­nh sÃ¡ch Ä‘á»•i hÃ ng',
            'Sáº£n pháº©m cÃ¹ng loáº¡i',
            'TÃŒM Sáº¢N PHáº¨M Cá»¬A HÃ€NG',
            'HÆ°á»›ng dáº«n chá»n size',
            'Báº£ng size',
        ]);
        $descriptionBlock = $this->extractNormalizedTextBlock($text, ['mo ta'], [
            'chinh sach giao hang',
            'chinh sach doi hang',
            'san pham cung loai',
            'tim san pham cua hang',
            'huong dan chon size',
            'bang size',
        ]);
        $descriptionText = $this->cleanupDescriptionText($descriptionBlock);
        $descriptionText = $this->stripLeadingDescriptionLabel($descriptionText);

        return [$descriptionText, $this->convertTextToHtml($descriptionText)];
    }

    protected function extractDescriptionHtmlFromDom(DOMXPath $xpath): string
    {
        $queries = [
            "//*[contains(concat(' ', normalize-space(@class), ' '), ' more-description ')]",
            "//*[contains(translate(@id,'ABCDEFGHIJKLMNOPQRSTUVWXYZ','abcdefghijklmnopqrstuvwxyz'),'description')]",
            "//*[contains(translate(@class,'ABCDEFGHIJKLMNOPQRSTUVWXYZ','abcdefghijklmnopqrstuvwxyz'),'description')]",
            "//*[contains(translate(@class,'ABCDEFGHIJKLMNOPQRSTUVWXYZ','abcdefghijklmnopqrstuvwxyz'),'product-content')]",
            "//*[contains(translate(@class,'ABCDEFGHIJKLMNOPQRSTUVWXYZ','abcdefghijklmnopqrstuvwxyz'),'content-product')]",
            "//*[@data-tab='description' or @data-content='description']",
        ];
        $bestHtml = '';
        $bestScore = 0;

        foreach ($queries as $query) {
            foreach ($xpath->query($query) as $node) {
                if (!$node instanceof DOMElement) {
                    continue;
                }

                $html = $this->extractInnerHtml($node);
                $candidateText = $this->cleanupDescriptionText($this->htmlToText($html));
                $score = $this->scoreDescriptionCandidateNormalized($candidateText);

                if (str_contains(' ' . strtolower((string) $node->getAttribute('class')) . ' ', ' more-description ')) {
                    $score += 2000;
                }

                if ($score <= $bestScore) {
                    continue;
                }

                $bestScore = $score;
                $bestHtml = $html;
            }
        }

        return $bestHtml;
    }

    protected function normalizeDescriptionHtml(string $html): string
    {
        $html = trim($html);

        if ($html === '') {
            return '';
        }

        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="utf-8" ?><div id="crawler-description-root">' . $html . '</div>', LIBXML_NOERROR | LIBXML_NOWARNING);
        libxml_clear_errors();
        $xpath = new DOMXPath($dom);
        $root = $xpath->query('//*[@id="crawler-description-root"]')->item(0);

        if (!$root instanceof DOMElement) {
            return trim($html);
        }

        foreach (['script', 'style', 'noscript', 'template'] as $tagName) {
            while (($nodes = $root->getElementsByTagName($tagName)) && $nodes->length > 0) {
                $node = $nodes->item(0);

                if ($node instanceof DOMNode && $node->parentNode instanceof DOMNode) {
                    $node->parentNode->removeChild($node);
                }
            }
        }

        foreach ($xpath->query('.//*[@src or @href or @data-src or @data-original or @data-image or @poster]', $root) as $node) {
            if (!$node instanceof DOMElement) {
                continue;
            }

            foreach (['src', 'href', 'poster'] as $attribute) {
                $value = $this->normalizeUrl((string) $node->getAttribute($attribute));

                if ($value !== '') {
                    $node->setAttribute($attribute, $value);
                }
            }

            if ($node->hasAttribute('src')) {
                $srcValue = $this->normalizeUrl((string) $node->getAttribute('src'));

                if ($srcValue !== '') {
                    $node->setAttribute('src', $srcValue);
                }
            }

            if (!$node->hasAttribute('src') || trim((string) $node->getAttribute('src')) === '') {
                foreach (['data-src', 'data-original', 'data-image'] as $fallbackAttribute) {
                    $fallbackValue = $this->normalizeUrl((string) $node->getAttribute($fallbackAttribute));

                    if ($fallbackValue === '') {
                        continue;
                    }

                    $node->setAttribute('src', $fallbackValue);
                    break;
                }
            }
        }

        return $this->extractInnerHtml($root);
    }

    protected function descriptionHtmlHasRenderableContent(string $html): bool
    {
        if (trim($html) === '') {
            return false;
        }

        if (preg_match('/<(img|picture|video|iframe|table|ul|ol|p|div|section|article)\b/i', $html)) {
            return true;
        }

        return trim($this->htmlToText($html)) !== '';
    }

    protected function extractNormalizedTextBlock(string $text, array $starts, array $ends = []): string
    {
        $lines = $this->normalizeLines($text);
        $result = [];
        $collecting = false;

        foreach ($lines as $line) {
            $normalized = $this->normalizeSearchText($line);

            if (!$collecting) {
                if (in_array($normalized, $starts, true)) {
                    $collecting = true;
                }

                continue;
            }

            if (in_array($normalized, $ends, true)) {
                break;
            }

            $result[] = $line;
        }

        return implode("\n", $result);
    }

    protected function extractInnerHtml(DOMNode $node): string
    {
        $html = '';

        foreach ($node->childNodes as $childNode) {
            $html .= $node->ownerDocument?->saveHTML($childNode) ?? '';
        }

        return trim($html);
    }

    protected function scoreDescriptionCandidateNormalized(string $text): int
    {
        $text = trim($text);

        if ($text === '') {
            return 0;
        }

        $normalized = $this->normalizeSearchText($text);

        foreach ([
            'dang ky nhan tin',
            'ho tro khach hang',
            'phuong thuc thanh toan',
            'tim san pham cua hang',
        ] as $noise) {
            if (str_contains(' ' . $normalized . ' ', ' ' . $noise . ' ')) {
                return 0;
            }
        }

        $score = min(mb_strlen($text), 4000);

        foreach (['icondenim', 'chat lieu', 'mau sac', 'dac diem'] as $keyword) {
            if (str_contains(' ' . $normalized . ' ', ' ' . $keyword . ' ')) {
                $score += 250;
            }
        }

        return $score;
    }

    protected function scoreDescriptionCandidate(string $text): int
    {
        $text = trim($text);

        if ($text === '') {
            return 0;
        }

        foreach ([
            'Ä‘Äƒng kÃ½ nháº­n tin',
            'há»— trá»£ khÃ¡ch hÃ ng',
            'phÆ°Æ¡ng thá»©c thanh toÃ¡n',
            'tÃ¬m sáº£n pháº©m cá»­a hÃ ng',
        ] as $noise) {
            if (mb_stripos($text, $noise) !== false) {
                return 0;
            }
        }

        $score = min(mb_strlen($text), 4000);

        foreach (['ICONDENIM', 'Cháº¥t liá»‡u', 'MÃ u sáº¯c', 'Äáº·c Ä‘iá»ƒm'] as $keyword) {
            if (mb_stripos($text, $keyword) !== false) {
                $score += 250;
            }
        }

        return $score;
    }

    protected function cleanupDescriptionText(string $text): string
    {
        $lines = $this->normalizeLines($text);
        $cleaned = [];

        foreach ($lines as $line) {
            $line = trim(html_entity_decode($line, ENT_QUOTES | ENT_HTML5, 'UTF-8'));

            if ($line === '') {
                continue;
            }

            if (preg_match('/^(?:[\*\-~]\s*){3,}$/u', $line)) {
                continue;
            }

            if (preg_match('/^(producthandle|featured_image)\s*:/iu', $line)) {
                continue;
            }

            $normalized = $this->normalizeSearchText($line);

            if (in_array($normalized, ['mo ta'], true)) {
                continue;
            }

            if (in_array($normalized, [
                'chinh sach giao hang',
                'chinh sach doi hang',
                'san pham cung loai',
                'tim san pham cua hang',
                'huong dan chon size',
                'bang size',
            ], true)) {
                break;
            }

            $line = preg_replace('/^#{1,6}\s*/u', '', $line);
            $line = trim((string) $line);

            if ($line === '') {
                continue;
            }

            $cleaned[] = $line;
        }

        return implode("\n", $cleaned);
    }

    protected function stripLeadingDescriptionLabel(string $text): string
    {
        $lines = $this->normalizeLines($text);

        if (empty($lines)) {
            return '';
        }

        $firstLine = array_shift($lines);
        $normalized = $this->normalizeSearchText($firstLine);

        if ($normalized === 'mo ta') {
            return implode("\n", $lines);
        }

        if (str_starts_with($normalized, 'mo ta ')) {
            $firstLine = trim((string) preg_replace('/^m[oô]\s*t[aả]\s*/iu', '', $firstLine));
        }

        if ($firstLine !== '') {
            array_unshift($lines, $firstLine);
        }

        return implode("\n", $lines);
    }

    protected function convertTextToHtml(string $text): string
    {
        $lines = $this->normalizeLines($text);

        if (empty($lines)) {
            return '';
        }

        $html = [];
        $listItems = [];

        foreach ($lines as $line) {
            $cleaned = trim($line);

            if ($cleaned === '') {
                continue;
            }

            if (preg_match('/^(?:[\*\-~]\s*){3,}$/u', $cleaned)) {
                continue;
            }

            if (preg_match('/^(#{1,6})\s*(.+)$/u', $cleaned, $matches)) {
                if (!empty($listItems)) {
                    $html[] = '<ul>' . implode('', $listItems) . '</ul>';
                    $listItems = [];
                }

                $heading = trim((string) $matches[2]);
                $heading = preg_replace('/^â–º\s*/u', '', $heading);
                $level = strlen((string) $matches[1]) <= 3 ? 'h3' : 'h4';

                if ($heading !== '') {
                    $html[] = '<' . $level . '>' . htmlspecialchars($heading) . '</' . $level . '>';
                }

                continue;
            }

            $isListItem = preg_match('/^[\-\*\•\+]/u', $cleaned) === 1;
            $isSection = preg_match('/^►/u', $cleaned) === 1;

            if ($isListItem) {
                $listItems[] = '<li>' . htmlspecialchars(trim(preg_replace('/^[\-\*\•\+]+\s*/u', '', $cleaned))) . '</li>';
                continue;
            }

            if (!empty($listItems)) {
                $html[] = '<ul>' . implode('', $listItems) . '</ul>';
                $listItems = [];
            }

            if ($isSection) {
                $html[] = '<p><strong>' . htmlspecialchars(trim(mb_substr($cleaned, 1))) . '</strong></p>';
                continue;
            }

            $html[] = '<p>' . htmlspecialchars($cleaned) . '</p>';
        }

        if (!empty($listItems)) {
            $html[] = '<ul>' . implode('', $listItems) . '</ul>';
        }

        return implode("\n", $html);
    }

    protected function extractProductImages(DOMXPath $xpath, string $slug, string $code): array
    {
        $images = [];

        foreach ($xpath->query('//meta[@property="og:image"]/@content') as $node) {
            $url = $this->normalizeUrl((string) $node->nodeValue);

            if ($this->isValidProductImage($url, $slug, $code)) {
                $images[$url] = $url;
            }
        }

        foreach ($xpath->query('//*[@src or @href]') as $node) {
            if (!$node instanceof DOMElement) {
                continue;
            }

            $candidates = [
                $node->getAttribute('src'),
                $node->getAttribute('data-src'),
                $node->getAttribute('data-image'),
                $node->getAttribute('href'),
            ];

            foreach ($candidates as $candidate) {
                $url = $this->normalizeUrl((string) $candidate);

                if ($this->isValidProductImage($url, $slug, $code)) {
                    $images[$url] = $url;
                }
            }
        }

        return array_values($images);
    }

    protected function extractProductJson(string $html): array
    {
        $marker = 'productjson:';
        $start = strpos($html, $marker);

        if ($start === false) {
            return [];
        }

        $start += strlen($marker);
        $end = strpos($html, 'producthandle:', $start);

        if ($end === false) {
            return [];
        }

        $json = trim(substr($html, $start, $end - $start));
        $json = rtrim($json, ", \t\n\r\0\x0B;");
        $decoded = json_decode($json, true);

        return is_array($decoded) ? $decoded : [];
    }

    protected function extractVariantMeta(array $productJson, string $slug = '', string $code = ''): array
    {
        $meta = [
            'colors' => [],
            'sizes' => [],
            'image_urls' => [],
            'color_image_map' => [],
        ];

        foreach ((array) ($productJson['images'] ?? []) as $imageUrl) {
            $normalizedUrl = $this->normalizeUrl((string) $imageUrl);

            if ($this->passesBaseProductImageChecks($normalizedUrl)) {
                $meta['image_urls'][$normalizedUrl] = $normalizedUrl;
            }
        }

        $colorIndex = $this->detectVariantOptionIndex((array) ($productJson['options'] ?? []), ['mau', 'color']);
        $sizeIndex = $this->detectVariantOptionIndex((array) ($productJson['options'] ?? []), ['kich thuoc', 'size']);

        foreach ((array) ($productJson['variants'] ?? []) as $variant) {
            if (!is_array($variant)) {
                continue;
            }

            $colorValue = $this->cleanVariantOptionValue($this->extractVariantOptionValue($variant, $colorIndex), 'color');
            $sizeValue = $this->cleanVariantOptionValue($this->extractVariantOptionValue($variant, $sizeIndex), 'size');
            $featuredImage = $this->extractVariantFeaturedImage($variant);

            if ($colorValue !== '') {
                $meta['colors'][$this->normalizeKey($colorValue)] = $colorValue;
            }

            if ($sizeValue !== '') {
                $meta['sizes'][$this->normalizeKey($sizeValue)] = $sizeValue;
            }

            if ($this->passesBaseProductImageChecks($featuredImage)) {
                $meta['image_urls'][$featuredImage] = $featuredImage;
            }

            if ($colorValue !== '' && $this->passesBaseProductImageChecks($featuredImage)) {
                $key = $this->normalizeKey($colorValue);

                if (empty($meta['color_image_map'][$key])) {
                    $meta['color_image_map'][$key] = [
                        'color_name' => $colorValue,
                        'image_url' => $featuredImage,
                    ];
                }
            }
        }

        $meta['colors'] = array_values($meta['colors']);
        $meta['sizes'] = array_values($meta['sizes']);
        $meta['image_urls'] = array_values($meta['image_urls']);
        $meta['color_image_map'] = array_values($meta['color_image_map']);

        return $meta;
    }

    protected function detectVariantOptionIndex(array $options, array $keywords): int
    {
        foreach ($options as $index => $optionName) {
            $normalized = $this->normalizeSearchText((string) $optionName);

            foreach ($keywords as $keyword) {
                if ($normalized !== '' && str_contains(' ' . $normalized . ' ', ' ' . $keyword . ' ')) {
                    return (int) $index;
                }
            }
        }

        return -1;
    }

    protected function extractVariantOptionValue(array $variant, int $index): string
    {
        if ($index < 0) {
            return '';
        }

        $options = (array) ($variant['options'] ?? []);

        if (array_key_exists($index, $options)) {
            return trim((string) $options[$index]);
        }

        $key = 'option' . ($index + 1);

        return trim((string) ($variant[$key] ?? ''));
    }

    protected function cleanVariantOptionValue(string $value, string $type = 'text'): string
    {
        $value = html_entity_decode(trim($value), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        if ($value === '') {
            return '';
        }

        if ($type === 'color') {
            $value = preg_replace('/\s*[-\/]\s*[A-Z0-9]{3,}$/u', '', $value);
            $value = preg_replace('/\s*-\s*\d{3,}$/u', '', (string) $value);
        }

        if ($type === 'size') {
            $value = preg_replace('/^size\s*/iu', '', $value);
            $value = strtoupper(trim((string) $value));
        }

        $value = preg_replace('/\s+/u', ' ', (string) $value);

        return trim((string) $value);
    }

    protected function extractVariantFeaturedImage(array $variant): string
    {
        $featuredImage = $variant['featured_image'] ?? null;

        if (is_array($featuredImage)) {
            $featuredImage = $featuredImage['src'] ?? '';
        }

        return $this->normalizeUrl((string) $featuredImage);
    }

    protected function mergeUniqueImageUrls(array ...$groups): array
    {
        $images = [];

        foreach ($groups as $group) {
            foreach ($group as $imageUrl) {
                $normalizedUrl = $this->normalizeUrl((string) $imageUrl);

                if ($normalizedUrl !== '') {
                    $images[$normalizedUrl] = $normalizedUrl;
                }
            }
        }

        return array_values($images);
    }

    protected function isValidProductImage(string $url, string $slug, string $code): bool
    {
        if (!$this->passesBaseProductImageChecks($url)) {
            return false;
        }

        $slugNeedle = str_replace('-', '', $slug);
        $codeNeedle = str_replace('-', '', strtolower($code));
        $urlNeedle = str_replace(['-', '_'], '', strtolower($url));

        if ($slugNeedle !== '' && str_contains($urlNeedle, $slugNeedle)) {
            return true;
        }

        if ($codeNeedle !== '' && str_contains($urlNeedle, $codeNeedle)) {
            return true;
        }

        return str_contains($url, '1024x1024') || str_contains($url, 'master');
    }

    protected function passesBaseProductImageChecks(string $url): bool
    {
        if ($url === '' || !preg_match('/\.(jpg|jpeg|png|webp)(\?|$)/i', $url)) {
            return false;
        }

        if (!preg_match('/(hstatic\.net|icondenim\.com)/i', $url)) {
            return false;
        }

        if (preg_match('/(logo|icon_|policies_|search|zalo|tiktok|vnpay|cod|dmca|online\.gov|banner|voucher|store|membership|payment)/i', $url)) {
            return false;
        }

        if (preg_match('/(?:^|[\/_-])video(?:[\/_-]|$)/i', (string) parse_url($url, PHP_URL_PATH))) {
            return false;
        }

        return true;
    }

    protected function extractCategory(DOMXPath $xpath, string $title, string $code, string $slug): array
    {
        $category = $this->extractCategoryFromJsonLd($xpath);

        if (empty($category['list_slug']) && empty($category['cat_slug'])) {
            $category = $this->matchCategoryByText(implode(' ', array_filter([
                $title,
                $code,
                str_replace('-', ' ', $slug),
            ])));
            if (!empty($category)) {
                $category['source'] = 'keyword';
            }
        }

        if (!empty($category['cat_slug'])) {
            $matched = $this->matchCategoryByCollectionSlug($category['cat_slug']);
            $category = $this->mergeCategoryMatch($matched, $category);
        }

        if (!empty($category['list_slug']) && empty($category['list_name'])) {
            $matched = $this->matchCategoryByCollectionSlug($category['list_slug']);
            $category = $this->mergeCategoryMatch($matched, $category);
        }

        return array_filter($category, static fn ($value) => $value !== '' && $value !== null);
    }

    protected function extractCategoryFromJsonLd(DOMXPath $xpath): array
    {
        foreach ($xpath->query('//script[@type="application/ld+json"]') as $node) {
            $json = trim((string) $node->textContent);

            if ($json === '') {
                continue;
            }

            $payload = json_decode($json, true);

            if (!is_array($payload)) {
                continue;
            }

            foreach ($this->extractBreadcrumbListsFromJsonLd($payload) as $items) {
                $category = [];

                foreach ($items as $item) {
                    if (!is_array($item)) {
                        continue;
                    }

                    $normalized = $this->normalizeBreadcrumbItem($item);
                    $matched = [];
                    $collectionSlug = $this->extractCollectionSlugFromUrl($normalized['item']);

                    if ($collectionSlug !== '') {
                        $matched = $this->matchCategoryByCollectionSlug($collectionSlug);
                    }

                    if (empty($matched) && $normalized['name'] !== '') {
                        $matched = $this->matchCategoryByText($normalized['name']);
                    }

                    if (empty($matched)) {
                        continue;
                    }

                    $category = $this->mergeCategoryMatch($category, $matched);
                }

                if (!empty($category['list_slug']) || !empty($category['cat_slug'])) {
                    $category['source'] = 'jsonld';

                    return $category;
                }
            }
        }

        return [];
    }

    protected function extractBreadcrumbListsFromJsonLd(array $payload): array
    {
        $lists = [];
        $queue = [$payload];

        while (!empty($queue)) {
            $current = array_shift($queue);

            if (!is_array($current)) {
                continue;
            }

            $type = $current['@type'] ?? null;
            $isBreadcrumb = $type === 'BreadcrumbList'
                || (is_array($type) && in_array('BreadcrumbList', $type, true));

            if ($isBreadcrumb && !empty($current['itemListElement']) && is_array($current['itemListElement'])) {
                $lists[] = $current['itemListElement'];
            }

            foreach ($current as $value) {
                if (is_array($value)) {
                    $queue[] = $value;
                }
            }
        }

        return $lists;
    }

    protected function normalizeBreadcrumbItem(array $item): array
    {
        $name = trim((string) ($item['name'] ?? ''));
        $link = '';

        if (!empty($item['item'])) {
            if (is_string($item['item'])) {
                $link = $item['item'];
            } elseif (is_array($item['item'])) {
                $name = $name !== '' ? $name : trim((string) ($item['item']['name'] ?? ''));
                $link = (string) ($item['item']['@id'] ?? $item['item']['url'] ?? '');
            }
        }

        if ($link === '') {
            $link = (string) ($item['@id'] ?? $item['url'] ?? '');
        }

        return [
            'name' => $name,
            'item' => $this->normalizeUrl($link),
        ];
    }

    protected function matchCategoryByCollectionSlug(string $collectionSlug): array
    {
        $collectionSlug = trim(mb_strtolower($collectionSlug));

        if ($collectionSlug === '') {
            return [];
        }

        foreach ($this->getCategoryDefinitions() as $list) {
            $listAliases = array_map(
                static fn ($value) => trim(mb_strtolower((string) $value)),
                array_merge([$list['list_slug']], $list['aliases'] ?? [])
            );

            if (in_array($collectionSlug, $listAliases, true)) {
                return [
                    'list_name' => $list['list_name'],
                    'list_slug' => $list['list_slug'],
                ];
            }

            foreach ($list['cats'] as $cat) {
                $catAliases = array_map(
                    static fn ($value) => trim(mb_strtolower((string) $value)),
                    array_merge([$cat['slug']], $cat['aliases'] ?? [])
                );

                if (in_array($collectionSlug, $catAliases, true)) {
                    return [
                        'list_name' => $list['list_name'],
                        'list_slug' => $list['list_slug'],
                        'cat_name' => $cat['name'],
                        'cat_slug' => $cat['slug'],
                    ];
                }
            }
        }

        return [];
    }

    protected function matchCategoryByText(string $text): array
    {
        $normalized = $this->normalizeSearchText($text);

        if ($normalized === '') {
            return [];
        }

        $bestMatch = [];
        $bestScore = 0;

        foreach ($this->getCategoryDefinitions() as $list) {
            foreach ($list['cats'] as $cat) {
                $score = $this->scoreKeywordMatch($normalized, $cat['keywords'] ?? []);

                if ($score <= $bestScore) {
                    continue;
                }

                $bestScore = $score;
                $bestMatch = [
                    'list_name' => $list['list_name'],
                    'list_slug' => $list['list_slug'],
                    'cat_name' => $cat['name'],
                    'cat_slug' => $cat['slug'],
                ];
            }
        }

        if (!empty($bestMatch)) {
            return $bestMatch;
        }

        foreach ($this->getCategoryDefinitions() as $list) {
            $score = $this->scoreKeywordMatch($normalized, $list['keywords'] ?? []);

            if ($score <= $bestScore) {
                continue;
            }

            $bestScore = $score;
            $bestMatch = [
                'list_name' => $list['list_name'],
                'list_slug' => $list['list_slug'],
            ];
        }

        return $bestMatch;
    }

    protected function scoreKeywordMatch(string $normalizedText, array $keywords): int
    {
        $score = 0;

        foreach ($keywords as $keyword) {
            $keyword = $this->normalizeSearchText((string) $keyword);

            if ($keyword === '') {
                continue;
            }

            if (str_contains(' ' . $normalizedText . ' ', ' ' . $keyword . ' ')) {
                $score = max($score, substr_count($keyword, ' ') + 1);
            }
        }

        return $score;
    }

    protected function normalizeSearchText(string $value): string
    {
        $value = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $value = mb_strtolower(trim($value));
        $value = strtr($value, [
            'à' => 'a', 'á' => 'a', 'ạ' => 'a', 'ả' => 'a', 'ã' => 'a',
            'ă' => 'a', 'ằ' => 'a', 'ắ' => 'a', 'ặ' => 'a', 'ẳ' => 'a', 'ẵ' => 'a',
            'â' => 'a', 'ầ' => 'a', 'ấ' => 'a', 'ậ' => 'a', 'ẩ' => 'a', 'ẫ' => 'a',
            'è' => 'e', 'é' => 'e', 'ẹ' => 'e', 'ẻ' => 'e', 'ẽ' => 'e',
            'ê' => 'e', 'ề' => 'e', 'ế' => 'e', 'ệ' => 'e', 'ể' => 'e', 'ễ' => 'e',
            'ì' => 'i', 'í' => 'i', 'ị' => 'i', 'ỉ' => 'i', 'ĩ' => 'i',
            'ò' => 'o', 'ó' => 'o', 'ọ' => 'o', 'ỏ' => 'o', 'õ' => 'o',
            'ô' => 'o', 'ồ' => 'o', 'ố' => 'o', 'ộ' => 'o', 'ổ' => 'o', 'ỗ' => 'o',
            'ơ' => 'o', 'ờ' => 'o', 'ớ' => 'o', 'ợ' => 'o', 'ở' => 'o', 'ỡ' => 'o',
            'ù' => 'u', 'ú' => 'u', 'ụ' => 'u', 'ủ' => 'u', 'ũ' => 'u',
            'ư' => 'u', 'ừ' => 'u', 'ứ' => 'u', 'ự' => 'u', 'ử' => 'u', 'ữ' => 'u',
            'ỳ' => 'y', 'ý' => 'y', 'ỵ' => 'y', 'ỷ' => 'y', 'ỹ' => 'y',
            'đ' => 'd',
        ]);
        $ascii = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);

        if ($ascii !== false) {
            $value = strtolower((string) $ascii);
        }

        $value = preg_replace('/[^a-z0-9]+/i', ' ', $value);
        $value = preg_replace('/\s+/u', ' ', (string) $value);

        return trim((string) $value);
    }

    protected function mergeCategoryMatch(array $base, array $matched): array
    {
        foreach (['list_name', 'list_slug', 'cat_name', 'cat_slug', 'source'] as $key) {
            if (empty($base[$key]) && !empty($matched[$key])) {
                $base[$key] = $matched[$key];
            }
        }

        return $base;
    }

    protected function extractCollectionSlugFromUrl(string $url): string
    {
        $path = trim((string) parse_url($url, PHP_URL_PATH), '/');

        if ($path === '' || !str_contains($path, 'collections/')) {
            return '';
        }

        $segments = explode('/', $path);
        $handle = trim((string) end($segments));

        return $handle === 'tat-ca-san-pham' ? '' : $handle;
    }

    protected function getCategoryDefinitions(): array
    {
        if ($this->categoryDefinitions !== null) {
            return $this->categoryDefinitions;
        }

        $this->categoryDefinitions = [
            [
                'list_name' => 'Áo Nam',
                'list_slug' => 'ao-nam',
                'aliases' => ['ao'],
                'keywords' => ['ao thun', 'ao polo', 'ao so mi', 'ao somi', 'ao khoac', 'ao ni', 'ao len', 'hoodie', 'tank top', 'ao ba lo'],
                'cats' => [
                    ['name' => 'Áo Thun', 'slug' => 'ao-thun', 'aliases' => ['t-shirt', 'tshirt'], 'keywords' => ['ao thun', 't shirt', 'tshirt', 'tee']],
                    ['name' => 'Áo Polo', 'slug' => 'ao-polo', 'aliases' => ['polo'], 'keywords' => ['ao polo', 'polo']],
                    ['name' => 'Áo Sơmi', 'slug' => 'ao-somi', 'aliases' => ['ao-so-mi', 'ao-somi'], 'keywords' => ['ao so mi', 'ao somi', 'shirt']],
                    ['name' => 'Áo Khoác', 'slug' => 'ao-khoac', 'aliases' => ['khoac'], 'keywords' => ['ao khoac', 'jacket', 'blazer', 'windbreaker']],
                    ['name' => 'Áo Nỉ Và Len', 'slug' => 'ao-ni-va-len', 'aliases' => ['ao-ni-len', 'ao-len'], 'keywords' => ['ao ni', 'ao len', 'sweater', 'cardigan']],
                    ['name' => 'Hoodie', 'slug' => 'hoodie', 'aliases' => ['sweatshirt'], 'keywords' => ['hoodie', 'sweatshirt']],
                    ['name' => 'Tank Top - Áo Ba Lỗ', 'slug' => 'tank-top-ao-ba-lo', 'aliases' => ['tank-top', 'ao-ba-lo'], 'keywords' => ['tank top', 'ao ba lo']],
                ],
            ],
            [
                'list_name' => 'Quần Nam',
                'list_slug' => 'quan-nam',
                'aliases' => ['quan'],
                'keywords' => ['quan jean', 'jeans', 'quan short', 'quan kaki', 'chino', 'jogger', 'quan dai', 'quan tay', 'boxer'],
                'cats' => [
                    ['name' => 'Quần Jean', 'slug' => 'quan-jean', 'aliases' => ['quan-jeans', 'jean', 'jeans', 'denim'], 'keywords' => ['quan jean', 'quan jeans', 'jean', 'jeans', 'denim']],
                    ['name' => 'Quần Short', 'slug' => 'quan-short', 'aliases' => ['short'], 'keywords' => ['quan short', 'short']],
                    ['name' => 'Quần Kaki & Chino', 'slug' => 'quan-kaki-chino', 'aliases' => ['quan-kaki', 'chino'], 'keywords' => ['quan kaki', 'kaki', 'chino']],
                    ['name' => 'Quần Jogger - Quần Dài', 'slug' => 'quan-jogger-quan-dai', 'aliases' => ['quan-jogger', 'quan-dai', 'jogger'], 'keywords' => ['quan jogger', 'jogger', 'quan dai']],
                    ['name' => 'Quần Tây', 'slug' => 'quan-tay', 'aliases' => [], 'keywords' => ['quan tay', 'trouser', 'trousers']],
                    ['name' => 'Quần Boxer', 'slug' => 'quan-boxer', 'aliases' => ['boxer'], 'keywords' => ['quan boxer', 'boxer']],
                ],
            ],
            [
                'list_name' => 'Giày & Phụ Kiện',
                'list_slug' => 'giay-phu-kien',
                'aliases' => ['phu-kien', 'giay'],
                'keywords' => ['balo', 'tui', 'vi da', 'non', 'cap', 'that lung', 'mat kinh', 'kinh mat', 'giay', 'dep', 'vo', 'tat chan'],
                'cats' => [
                    ['name' => 'Balo, Túi & Ví', 'slug' => 'balo-tui-vi', 'aliases' => ['tui-vi', 'balo'], 'keywords' => ['balo', 'tui', 'tui xach', 'vi da', 'bag', 'wallet']],
                    ['name' => 'Nón', 'slug' => 'non', 'aliases' => ['cap'], 'keywords' => ['non', 'cap', 'bucket hat', 'baseball cap']],
                    ['name' => 'Thắt Lưng', 'slug' => 'that-lung', 'aliases' => ['belt'], 'keywords' => ['that lung', 'belt']],
                    ['name' => 'Mắt Kính', 'slug' => 'mat-kinh', 'aliases' => ['kinh-mat', 'sunglasses'], 'keywords' => ['mat kinh', 'kinh mat', 'sunglasses', 'glasses']],
                    ['name' => 'Giày & Dép', 'slug' => 'giay-dep', 'aliases' => ['giay', 'dep', 'sandal'], 'keywords' => ['giay', 'dep', 'sandal', 'slide', 'slipper']],
                    ['name' => 'Vớ', 'slug' => 'vo', 'aliases' => ['sock', 'socks'], 'keywords' => ['vo', 'tat chan', 'tat co', 'sock', 'socks']],
                ],
            ],
        ];

        return $this->categoryDefinitions;
    }

    protected function htmlToText(string $html): string
    {
        $cleanedHtml = preg_replace('/<script\b[^>]*>.*?<\/script>/is', '', $html);
        $cleanedHtml = preg_replace('/<style\b[^>]*>.*?<\/style>/is', '', (string) $cleanedHtml);
        $cleanedHtml = preg_replace('/<noscript\b[^>]*>.*?<\/noscript>/is', '', (string) $cleanedHtml);
        $cleanedHtml = preg_replace('/<template\b[^>]*>.*?<\/template>/is', '', (string) $cleanedHtml);
        $prepared = preg_replace('/<(br|\/p|\/div|\/li|\/h[1-6]|\/section|\/article)[^>]*>/iu', "\n", (string) $cleanedHtml);
        $text = html_entity_decode(strip_tags((string) $prepared), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = str_replace(["\r\n", "\r"], "\n", $text);
        $text = preg_replace("/\n{2,}/", "\n", $text);
        $text = preg_replace("/[ \t]+/", ' ', $text);

        return trim((string) $text);
    }

    protected function normalizeLines(string $text): array
    {
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = str_replace(["\r\n", "\r"], "\n", $text);
        $text = preg_replace("/\n{2,}/", "\n", $text);
        $lines = array_map(static fn ($line) => trim((string) $line), explode("\n", $text));
        $lines = array_values(array_filter($lines, static fn ($line) => $line !== ''));

        return $lines;
    }

    protected function normalizeUrl(string $url): string
    {
        $url = trim($url);

        if ($url === '' || str_starts_with($url, '#') || str_starts_with($url, 'javascript:')) {
            return '';
        }

        if (str_starts_with($url, '//')) {
            return 'https:' . $url;
        }

        if (str_starts_with($url, '/')) {
            return 'https://icondenim.com' . $url;
        }

        return $url;
    }

    protected function extractSlugFromUrl(string $url): string
    {
        $path = (string) parse_url($url, PHP_URL_PATH);
        $path = trim($path, '/');

        if (!str_contains($path, 'products/')) {
            return '';
        }

        $segments = explode('/', $path);
        $slug = trim((string) end($segments));

        return $slug !== '' ? $slug : '';
    }

    protected function normalizeKey(string $value): string
    {
        $value = mb_strtolower(trim($value));
        $value = preg_replace('/\s+/u', ' ', $value);

        return (string) $value;
    }
}
