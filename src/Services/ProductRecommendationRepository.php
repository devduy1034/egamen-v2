<?php

namespace LARAVEL\Services;

use LARAVEL\Models\ProductModel;
use LARAVEL\Models\ProductPropertiesModel;

class ProductRecommendationRepository
{
    public function __construct(
        protected ?string $lang = null
    ) {
        if ($this->lang === null || $this->lang === '') {
            $this->lang = session()->get('locale') ?? config('app.lang_default');
        }
    }

    /**
     * @param array<int,int> $ids
     * @return array<int,array<string,mixed>>
     */
    public function getProductsByIds(array $ids): array
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));
        if (empty($ids)) {
            return [];
        }

        $products = ProductModel::query()
            ->select(
                'id',
                'name' . $this->lang,
                'slug' . $this->lang,
                'photo',
                'id_brand',
                'id_list',
                'id_cat',
                'id_item',
                'id_sub',
                'properties',
                'regular_price',
                'sale_price',
                'discount',
                'status',
                'type'
            )
            ->with(['getBrand'])
            ->whereIn('id', $ids)
            ->where('type', 'san-pham')
            ->whereRaw("FIND_IN_SET(?,status)", ['hienthi'])
            ->get()
            ->keyBy('id');

        $stockMap = $this->resolveStockMap($ids);
        $result = [];
        foreach ($ids as $id) {
            $row = $products->get($id);
            if (empty($row)) {
                continue;
            }
            $result[] = $this->normalizeProductRecord($row, $stockMap[$id] ?? true);
        }
        return $result;
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public function getCandidateProducts(int $limit = 240): array
    {
        $limit = max(1, $limit);
        $rows = ProductModel::query()
            ->select(
                'id',
                'name' . $this->lang,
                'slug' . $this->lang,
                'photo',
                'id_brand',
                'id_list',
                'id_cat',
                'id_item',
                'id_sub',
                'properties',
                'regular_price',
                'sale_price',
                'discount',
                'status',
                'type'
            )
            ->with(['getBrand'])
            ->where('type', 'san-pham')
            ->whereRaw("FIND_IN_SET(?,status)", ['hienthi'])
            ->orderBy('id', 'desc')
            ->limit($limit)
            ->get();

        $ids = $rows->pluck('id')->map(fn ($v) => (int) $v)->values()->all();
        $stockMap = $this->resolveStockMap($ids);

        return $rows->map(function ($row) use ($stockMap) {
            $id = (int) ($row->id ?? 0);
            return $this->normalizeProductRecord($row, $stockMap[$id] ?? true);
        })->values()->all();
    }

    /**
     * @param array<int,int> $productIds
     * @return array<int,bool>
     */
    private function resolveStockMap(array $productIds): array
    {
        $productIds = array_values(array_unique(array_filter(array_map('intval', $productIds))));
        if (empty($productIds)) {
            return [];
        }

        $rows = ProductPropertiesModel::query()
            ->select('id_parent', 'quantity', 'status')
            ->whereIn('id_parent', $productIds)
            ->get();

        $hasVariant = [];
        $stockMap = [];
        foreach ($productIds as $pid) {
            $stockMap[$pid] = true;
        }

        foreach ($rows as $row) {
            $pid = (int) ($row->id_parent ?? 0);
            if ($pid <= 0) {
                continue;
            }
            $hasVariant[$pid] = true;
            if (!isset($stockMap[$pid])) {
                $stockMap[$pid] = false;
            }
            $qty = (int) ($row->quantity ?? 0);
            $status = strtolower(trim((string) ($row->status ?? 'active')));
            if ($qty > 0 && $status !== 'inactive') {
                $stockMap[$pid] = true;
            } elseif (!isset($stockMap[$pid]) || $stockMap[$pid] !== true) {
                $stockMap[$pid] = false;
            }
        }

        foreach ($hasVariant as $pid => $_) {
            if (!isset($stockMap[$pid])) {
                $stockMap[$pid] = false;
            }
        }

        return $stockMap;
    }

    /**
     * @return array<string,mixed>
     */
    private function normalizeProductRecord($row, bool $inStock): array
    {
        $sale = (float) ($row->sale_price ?? 0);
        $regular = (float) ($row->regular_price ?? 0);
        $price = $sale > 0 ? $sale : $regular;

        return [
            'product_id' => (int) ($row->id ?? 0),
            'name' => (string) ($row['name' . $this->lang] ?? ''),
            'slug' => (string) ($row['slug' . $this->lang] ?? ''),
            'photo' => (string) ($row->photo ?? ''),
            'brand_id' => (int) ($row->id_brand ?? 0),
            'brand_name' => trim((string) data_get($row, 'getBrand.name' . $this->lang, '')),
            'category_ids' => array_values(array_filter([
                (int) ($row->id_list ?? 0),
                (int) ($row->id_cat ?? 0),
                (int) ($row->id_item ?? 0),
                (int) ($row->id_sub ?? 0),
            ])),
            'property_ids' => array_values(array_unique(array_filter(array_map('intval', explode(',', (string) ($row->properties ?? '')))))),
            'price' => $price,
            'regular_price' => $regular,
            'sale_price' => $sale,
            'discount' => (float) ($row->discount ?? 0),
            'type' => (string) ($row->type ?? 'san-pham'),
            'in_stock' => $inStock,
        ];
    }
}
