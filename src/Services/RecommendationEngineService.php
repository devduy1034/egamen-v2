<?php

namespace LARAVEL\Services;

class RecommendationEngineService
{
    public function __construct(
        protected ?UserEventRepository $eventRepository = null,
        protected ?ProductRecommendationRepository $productRepository = null
    ) {
        $this->eventRepository = $this->eventRepository ?: new UserEventRepository();
        $this->productRepository = $this->productRepository ?: new ProductRecommendationRepository();
    }

    /**
     * @return array{user_id:?string,items:array<int,array<string,mixed>>}
     */
    public function recommend(?string $userId, ?string $anonymousId = null, int $limit = 20, int $contextProductId = 0, ?string $sessionId = null): array
    {
        $defaultLimit = max(1, (int) config('recommendation.default_limit', 20));
        $maxLimit = max($defaultLimit, (int) config('recommendation.max_limit', 60));
        $limit = min($maxLimit, max(1, $limit > 0 ? $limit : $defaultLimit));

        $events = $this->eventRepository->getEventsForIdentity(
            $this->normalizeNullableString($userId),
            $this->normalizeNullableString($anonymousId),
            max(10, (int) config('recommendation.max_event_lookback', 300)),
            $this->normalizeNullableString($sessionId)
        );

        if (empty($events)) {
            return [
                'user_id' => $this->normalizeNullableString($userId),
                'items' => $this->fallbackRecommendations($limit),
            ];
        }

        $eventWeights = (array) config('recommendation.event_weights', []);
        $productIdsFromEvents = $this->extractProductIdsFromEvents($events);
        $seedProducts = $this->indexProductsById($this->productRepository->getProductsByIds($productIdsFromEvents));
        $profile = $this->buildUserProfile($events, $seedProducts, $eventWeights);

        $candidates = $this->productRepository->getCandidateProducts((int) config('recommendation.candidate_limit', 240));
        if (empty($candidates)) {
            return [
                'user_id' => $this->normalizeNullableString($userId),
                'items' => $this->fallbackRecommendations($limit),
            ];
        }

        $contextProduct = $contextProductId > 0 ? ($seedProducts[$contextProductId] ?? null) : null;
        if ($contextProduct === null && $contextProductId > 0) {
            $contextRows = $this->productRepository->getProductsByIds([$contextProductId]);
            $contextProduct = $contextRows[0] ?? null;
        }

        $ranked = [];
        foreach ($candidates as $candidate) {
            $productId = (int) ($candidate['product_id'] ?? 0);
            if ($productId <= 0) {
                continue;
            }
            if (isset($profile['purchased_product_ids'][$productId])) {
                continue;
            }

            $scored = $this->scoreCandidate($candidate, $profile, $contextProduct);
            if ($scored['score'] <= 0) {
                continue;
            }
            $ranked[] = $scored;
        }

        if (empty($ranked)) {
            return [
                'user_id' => $this->normalizeNullableString($userId),
                'items' => $this->fallbackRecommendations($limit),
            ];
        }

        usort($ranked, function ($left, $right) {
            return ($right['score'] <=> $left['score']);
        });

        $items = [];
        foreach (array_slice($ranked, 0, $limit) as $row) {
            $items[] = [
                'product_id' => (string) $row['product_id'],
                'score' => round((float) $row['score'], 4),
                'reason' => (string) $row['reason'],
                'source' => 'personalized',
            ] + $this->buildItemPayload((array) ($row['product'] ?? []));
        }

        if (count($items) < $limit) {
            $existing = array_map(static fn ($item) => (int) ($item['product_id'] ?? 0), $items);
            $fallback = array_values(array_filter($this->fallbackRecommendations($limit), function ($item) use ($existing) {
                return !in_array((int) ($item['product_id'] ?? 0), $existing, true);
            }));
            foreach ($fallback as $item) {
                if (count($items) >= $limit) {
                    break;
                }
                $items[] = $item;
            }
        }

        return [
            'user_id' => $this->normalizeNullableString($userId),
            'items' => $items,
        ];
    }

    /**
     * @param array<int,array<string,mixed>> $events
     * @param array<int,array<string,mixed>> $seedProducts
     * @param array<string,int|float> $eventWeights
     * @return array<string,mixed>
     */
    private function buildUserProfile(array $events, array $seedProducts, array $eventWeights): array
    {
        $brandWeights = [];
        $categoryWeights = [];
        $propertyWeights = [];
        $priceSamples = [];
        $purchasedProductIds = [];
        $wishlistProductIds = [];
        $viewedProductIds = [];
        $coPurchaseBoost = [];
        $recentViewName = '';
        $recentWishlistName = '';

        foreach ($events as $event) {
            $type = (string) ($event['event_type'] ?? '');
            $metadata = is_array($event['metadata'] ?? null) ? $event['metadata'] : [];
            $eventWeight = (float) ($eventWeights[$type] ?? 1);

            $productIds = $this->extractProductIdsFromEvent($event);
            foreach ($productIds as $pid) {
                if ($pid <= 0 || empty($seedProducts[$pid])) {
                    continue;
                }
                $product = $seedProducts[$pid];

                if ($type === EventSchemaValidator::EVENT_PURCHASE) {
                    $purchasedProductIds[$pid] = true;
                }
                if ($type === EventSchemaValidator::EVENT_WISHLIST_ADD) {
                    $wishlistProductIds[$pid] = true;
                    if ($recentWishlistName === '') {
                        $recentWishlistName = (string) ($product['name'] ?? '');
                    }
                }
                if (in_array($type, [EventSchemaValidator::EVENT_PRODUCT_VIEW, EventSchemaValidator::EVENT_CLICK_RESULT], true)) {
                    $viewedProductIds[$pid] = true;
                    if ($recentViewName === '') {
                        $recentViewName = (string) ($product['name'] ?? '');
                    }
                }

                $brandId = (int) ($product['brand_id'] ?? 0);
                if ($brandId > 0) {
                    $brandWeights[$brandId] = ($brandWeights[$brandId] ?? 0.0) + $eventWeight;
                }
                foreach ((array) ($product['category_ids'] ?? []) as $categoryId) {
                    $categoryId = (int) $categoryId;
                    if ($categoryId <= 0) {
                        continue;
                    }
                    $categoryWeights[$categoryId] = ($categoryWeights[$categoryId] ?? 0.0) + $eventWeight;
                }
                foreach ((array) ($product['property_ids'] ?? []) as $propertyId) {
                    $propertyId = (int) $propertyId;
                    if ($propertyId <= 0) {
                        continue;
                    }
                    $propertyWeights[$propertyId] = ($propertyWeights[$propertyId] ?? 0.0) + $eventWeight;
                }
                $price = (float) ($product['price'] ?? 0);
                if ($price > 0) {
                    $priceSamples[] = ['price' => $price, 'weight' => $eventWeight];
                }
            }

            if ($type === EventSchemaValidator::EVENT_PURCHASE) {
                $ids = array_values(array_filter(array_map('intval', $productIds)));
                $count = count($ids);
                if ($count > 1) {
                    for ($i = 0; $i < $count; $i++) {
                        for ($j = 0; $j < $count; $j++) {
                            if ($i === $j) {
                                continue;
                            }
                            $left = $ids[$i];
                            $right = $ids[$j];
                            if ($left <= 0 || $right <= 0) {
                                continue;
                            }
                            $coPurchaseBoost[$left][$right] = ($coPurchaseBoost[$left][$right] ?? 0) + 1;
                        }
                    }
                }
            }

            if ($type === EventSchemaValidator::EVENT_SEARCH_QUERY) {
                // Reserved for future query-intent features.
                $query = trim((string) ($metadata['query'] ?? ''));
                if ($query !== '') {
                    // Keep noop branch to preserve extensibility with minimal complexity.
                }
            }
        }

        return [
            'brand_weights' => $this->normalizeMap($brandWeights),
            'category_weights' => $this->normalizeMap($categoryWeights),
            'property_weights' => $this->normalizeMap($propertyWeights),
            'target_price' => $this->weightedAveragePrice($priceSamples),
            'purchased_product_ids' => $purchasedProductIds,
            'wishlist_product_ids' => $wishlistProductIds,
            'viewed_product_ids' => $viewedProductIds,
            'co_purchase_boost' => $coPurchaseBoost,
            'recent_view_name' => $recentViewName,
            'recent_wishlist_name' => $recentWishlistName,
        ];
    }

    /**
     * @param array<string,mixed> $candidate
     * @param array<string,mixed> $profile
     * @param array<string,mixed>|null $contextProduct
     * @return array<string,mixed>
     */
    private function scoreCandidate(array $candidate, array $profile, ?array $contextProduct = null): array
    {
        $brandWeights = (array) ($profile['brand_weights'] ?? []);
        $categoryWeights = (array) ($profile['category_weights'] ?? []);
        $propertyWeights = (array) ($profile['property_weights'] ?? []);

        $brandScore = 0.0;
        $brandId = (int) ($candidate['brand_id'] ?? 0);
        if ($brandId > 0 && isset($brandWeights[$brandId])) {
            $brandScore = (float) $brandWeights[$brandId];
        }

        $categoryScore = $this->averageMatchedWeight((array) ($candidate['category_ids'] ?? []), $categoryWeights);
        $propertyScore = $this->averageMatchedWeight((array) ($candidate['property_ids'] ?? []), $propertyWeights);
        $priceScore = $this->priceAffinityScore((float) ($candidate['price'] ?? 0), (float) ($profile['target_price'] ?? 0));
        $coPurchaseScore = $this->coPurchaseScore((int) ($candidate['product_id'] ?? 0), (array) ($profile['co_purchase_boost'] ?? []), (array) ($profile['viewed_product_ids'] ?? []), (array) ($profile['wishlist_product_ids'] ?? []));

        $rawScore = ($categoryScore * 0.34)
            + ($brandScore * 0.18)
            + ($propertyScore * 0.24)
            + ($priceScore * 0.12)
            + ($coPurchaseScore * 0.12);

        if (!empty($candidate['in_stock'])) {
            $rawScore += 0.08;
        } else {
            $rawScore -= 0.12;
        }

        $contextSimilarity = 0.0;
        if (!empty($contextProduct)) {
            $contextSimilarity = $this->contextSimilarity($candidate, $contextProduct);
            $rawScore += $contextSimilarity * 0.2;
        }

        $score = $this->normalizeScore($rawScore);
        $reason = $this->buildReason($candidate, $profile, $coPurchaseScore, $contextSimilarity);

        return [
            'product_id' => (int) ($candidate['product_id'] ?? 0),
            'score' => $score,
            'reason' => $reason,
            'product' => $candidate,
        ];
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    private function fallbackRecommendations(int $limit): array
    {
        $limit = max(1, $limit);
        $trendingIds = $this->eventRepository->getTrendingProductIds($limit * 3);
        $products = $this->productRepository->getProductsByIds($trendingIds);

        if (empty($products)) {
            $products = $this->productRepository->getCandidateProducts($limit * 2);
        }

        $items = [];
        foreach ($products as $product) {
            if (count($items) >= $limit) {
                break;
            }
            $items[] = [
                'product_id' => (string) ((int) ($product['product_id'] ?? 0)),
                'score' => !empty($product['in_stock']) ? 0.55 : 0.4,
                'reason' => 'Popular products for new users',
                'source' => 'fallback',
            ] + $this->buildItemPayload($product);
        }
        return $items;
    }

    /**
     * @param array<string,mixed> $product
     * @return array<string,mixed>
     */
    private function buildItemPayload(array $product): array
    {
        return [
            'name' => (string) ($product['name'] ?? ''),
            'slug' => (string) ($product['slug'] ?? ''),
            'photo' => (string) ($product['photo'] ?? ''),
            'price' => (float) ($product['price'] ?? 0),
            'regular_price' => (float) ($product['regular_price'] ?? 0),
            'sale_price' => (float) ($product['sale_price'] ?? 0),
            'discount' => (float) ($product['discount'] ?? 0),
            'in_stock' => !empty($product['in_stock']),
        ];
    }

    /**
     * @param array<int,array<string,mixed>> $products
     * @return array<int,array<string,mixed>>
     */
    private function indexProductsById(array $products): array
    {
        $indexed = [];
        foreach ($products as $product) {
            $pid = (int) ($product['product_id'] ?? 0);
            if ($pid <= 0) {
                continue;
            }
            $indexed[$pid] = $product;
        }
        return $indexed;
    }

    /**
     * @param array<int,array<string,mixed>> $events
     * @return array<int,int>
     */
    private function extractProductIdsFromEvents(array $events): array
    {
        $ids = [];
        foreach ($events as $event) {
            foreach ($this->extractProductIdsFromEvent($event) as $id) {
                if ($id > 0) {
                    $ids[] = $id;
                }
            }
        }
        return array_values(array_unique(array_map('intval', $ids)));
    }

    /**
     * @param array<string,mixed> $event
     * @return array<int,int>
     */
    private function extractProductIdsFromEvent(array $event): array
    {
        $metadata = is_array($event['metadata'] ?? null) ? $event['metadata'] : [];
        $eventType = (string) ($event['event_type'] ?? '');

        if ($eventType === EventSchemaValidator::EVENT_PURCHASE) {
            $ids = [];
            foreach ((array) ($metadata['items'] ?? []) as $item) {
                $pid = (int) ($item['product_id'] ?? 0);
                if ($pid > 0) {
                    $ids[] = $pid;
                }
            }
            return array_values(array_unique($ids));
        }

        $pid = (int) ($metadata['product_id'] ?? 0);
        return $pid > 0 ? [$pid] : [];
    }

    /**
     * @param array<int|float,array-key> $map
     * @return array<int,float>
     */
    private function normalizeMap(array $map): array
    {
        if (empty($map)) {
            return [];
        }
        $max = max(array_map('floatval', $map));
        if ($max <= 0) {
            return [];
        }
        $normalized = [];
        foreach ($map as $key => $value) {
            $normalized[(int) $key] = (float) $value / $max;
        }
        return $normalized;
    }

    /**
     * @param array<int,array<string,float|int>> $samples
     */
    private function weightedAveragePrice(array $samples): float
    {
        if (empty($samples)) {
            return 0.0;
        }
        $sumWeighted = 0.0;
        $sumWeight = 0.0;
        foreach ($samples as $sample) {
            $price = (float) ($sample['price'] ?? 0);
            $weight = max(0.1, (float) ($sample['weight'] ?? 1));
            if ($price <= 0) {
                continue;
            }
            $sumWeighted += $price * $weight;
            $sumWeight += $weight;
        }
        if ($sumWeight <= 0) {
            return 0.0;
        }
        return $sumWeighted / $sumWeight;
    }

    /**
     * @param array<int,int> $ids
     * @param array<int,float> $weights
     */
    private function averageMatchedWeight(array $ids, array $weights): float
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));
        if (empty($ids) || empty($weights)) {
            return 0.0;
        }
        $sum = 0.0;
        $count = 0;
        foreach ($ids as $id) {
            if (isset($weights[$id])) {
                $sum += (float) $weights[$id];
                $count++;
            }
        }
        if ($count === 0) {
            return 0.0;
        }
        return $sum / $count;
    }

    private function priceAffinityScore(float $candidatePrice, float $targetPrice): float
    {
        if ($candidatePrice <= 0 || $targetPrice <= 0) {
            return 0.0;
        }
        $ratio = abs($candidatePrice - $targetPrice) / max(1.0, $targetPrice);
        return max(0.0, 1.0 - min(1.0, $ratio));
    }

    /**
     * @param array<int,array<int,int>> $coPurchaseBoost
     * @param array<int,bool> $viewedIds
     * @param array<int,bool> $wishlistIds
     */
    private function coPurchaseScore(int $candidateProductId, array $coPurchaseBoost, array $viewedIds, array $wishlistIds): float
    {
        if ($candidateProductId <= 0 || empty($coPurchaseBoost)) {
            return 0.0;
        }

        $seedIds = array_keys($viewedIds + $wishlistIds);
        if (empty($seedIds)) {
            return 0.0;
        }

        $maxScore = 0.0;
        foreach ($seedIds as $seedId) {
            $seedId = (int) $seedId;
            if ($seedId <= 0) {
                continue;
            }
            $value = (float) ($coPurchaseBoost[$seedId][$candidateProductId] ?? 0);
            if ($value > $maxScore) {
                $maxScore = $value;
            }
        }

        if ($maxScore <= 0) {
            return 0.0;
        }
        return min(1.0, $maxScore / 3.0);
    }

    /**
     * @param array<string,mixed> $candidate
     * @param array<string,mixed> $context
     */
    private function contextSimilarity(array $candidate, array $context): float
    {
        $categorySimilarity = 0.0;
        $candidateCategories = array_values(array_unique(array_map('intval', (array) ($candidate['category_ids'] ?? []))));
        $contextCategories = array_values(array_unique(array_map('intval', (array) ($context['category_ids'] ?? []))));
        if (!empty($candidateCategories) && !empty($contextCategories)) {
            $intersection = count(array_intersect($candidateCategories, $contextCategories));
            $union = count(array_unique(array_merge($candidateCategories, $contextCategories)));
            $categorySimilarity = $union > 0 ? ($intersection / $union) : 0.0;
        }

        $propertySimilarity = 0.0;
        $candidateProperties = array_values(array_unique(array_map('intval', (array) ($candidate['property_ids'] ?? []))));
        $contextProperties = array_values(array_unique(array_map('intval', (array) ($context['property_ids'] ?? []))));
        if (!empty($candidateProperties) && !empty($contextProperties)) {
            $intersection = count(array_intersect($candidateProperties, $contextProperties));
            $union = count(array_unique(array_merge($candidateProperties, $contextProperties)));
            $propertySimilarity = $union > 0 ? ($intersection / $union) : 0.0;
        }

        $brandSimilarity = ((int) ($candidate['brand_id'] ?? 0) > 0 && (int) ($candidate['brand_id'] ?? 0) === (int) ($context['brand_id'] ?? 0)) ? 1.0 : 0.0;

        return ($categorySimilarity * 0.45) + ($propertySimilarity * 0.35) + ($brandSimilarity * 0.2);
    }

    /**
     * @param array<string,mixed> $candidate
     * @param array<string,mixed> $profile
     */
    private function buildReason(array $candidate, array $profile, float $coPurchaseScore, float $contextSimilarity): string
    {
        if ($contextSimilarity > 0.3 && $this->normalizeNullableString($profile['recent_view_name'] ?? null) !== null) {
            return 'Because you viewed ' . (string) $profile['recent_view_name'];
        }
        if ($coPurchaseScore > 0.2) {
            return 'Frequently bought together';
        }
        $pid = (int) ($candidate['product_id'] ?? 0);
        if ($pid > 0 && isset($profile['wishlist_product_ids'][$pid])) {
            return 'Based on your wishlist';
        }
        if (!empty($profile['recent_wishlist_name'])) {
            return 'Similar to items in your wishlist';
        }
        if (!empty($profile['recent_view_name'])) {
            return 'Similar to recently viewed items';
        }
        return 'Recommended for you';
    }

    private function normalizeScore(float $raw): float
    {
        if ($raw <= 0) {
            return 0.0;
        }
        $score = 1 - exp(-$raw);
        return max(0.0, min(0.99, $score));
    }

    private function normalizeNullableString(mixed $value): ?string
    {
        $str = trim((string) $value);
        return $str !== '' ? $str : null;
    }
}
