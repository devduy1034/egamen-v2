<?php

namespace LARAVEL\Controllers\Web;

use Illuminate\Http\Request;
use LARAVEL\Controllers\Controller;
use LARAVEL\Core\Support\Facades\Func;
use LARAVEL\Models\GalleryModel;
use LARAVEL\Models\ProductModel;
use LARAVEL\Models\ProductPropertiesModel;
use LARAVEL\Models\PropertiesListModel;
use LARAVEL\Models\PropertiesModel;
use LARAVEL\Models\WishlistModel;
use LARAVEL\Services\EventSchemaValidator;
use LARAVEL\Services\EventTrackingService;

class WishlistController extends Controller
{
    public function handle(string $action, Request $request): void
    {
        try {
            match ($action) {
                'state' => $this->state(),
                'toggle' => $this->toggle($request),
                'merge' => $this->merge($request),
                'list' => $this->list(),
                'remove' => $this->remove($request),
                'guest-list' => $this->guestList($request),
                'variant-options' => $this->variantOptions($request),
                default => response()->json(['success' => false, 'message' => 'Unsupported action'], 404),
            };
        } catch (\Throwable $e) {
            response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function page()
    {
        if ($this->currentMemberId() > 0) {
            response()->redirect(url('user.account', null, ['section' => 'wishlist']));
            return;
        }

        return view('wishlist.index', [
            'titleMain' => 'Sản phẩm yêu thích',
        ]);
    }

    protected function state(): void
    {
        $memberId = $this->currentMemberId();
        if ($memberId <= 0) {
            response()->json([
                'success' => true,
                'count' => 0,
                'ids' => [],
            ]);
            return;
        }

        response()->json([
            'success' => true,
            'count' => $this->wishlistQuery()->where('user_id', $memberId)->count(),
            'ids' => $this->keysForUser($memberId),
        ]);
    }

    protected function toggle(Request $request): void
    {
        $memberId = $this->currentMemberId();
        if ($memberId <= 0) {
            response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
            return;
        }

        $productId = (int) $request->input('product_id', 0);
        $variantId = $this->normalizeVariantId($request->input('variant_id', ''));
        if ($productId <= 0) {
            response()->json(['success' => false, 'message' => 'Invalid product'], 422);
            return;
        }

        $row = $this->wishlistQuery()->where('user_id', $memberId)
            ->where('product_id', $productId)
            ->where('variant_id', $variantId)
            ->first();

        $status = 'added';
        if (!empty($row)) {
            $row->delete();
            $status = 'removed';
        } else {
            $this->wishlistCreate([
                'user_id' => $memberId,
                'product_id' => $productId,
                'variant_id' => $variantId,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
            $this->safeTrackEvent(EventSchemaValidator::EVENT_WISHLIST_ADD, [
                'product_id' => (string) $productId,
            ], $request);
        }

        response()->json([
            'success' => true,
            'status' => $status,
            'count' => $this->wishlistQuery()->where('user_id', $memberId)->count(),
            'ids' => $this->keysForUser($memberId),
        ]);
    }

    protected function merge(Request $request): void
    {
        $memberId = $this->currentMemberId();
        if ($memberId <= 0) {
            response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
            return;
        }

        $items = $this->decodeItemsPayload($request->input('items', '[]'));
        foreach ($items as $item) {
            $productId = (int) ($item['productId'] ?? $item['product_id'] ?? 0);
            $variantId = $this->normalizeVariantId($item['variantId'] ?? $item['variant_id'] ?? '');
            if ($productId <= 0) {
                continue;
            }

            $exists = $this->wishlistQuery()->where('user_id', $memberId)
                ->where('product_id', $productId)
                ->where('variant_id', $variantId)
                ->exists();
            if ($exists) {
                continue;
            }

            $createdAt = trim((string) ($item['addedAt'] ?? $item['created_at'] ?? ''));
            $this->wishlistCreate([
                'user_id' => $memberId,
                'product_id' => $productId,
                'variant_id' => $variantId,
                'created_at' => $createdAt !== '' ? date('Y-m-d H:i:s', strtotime($createdAt) ?: time()) : date('Y-m-d H:i:s'),
            ]);
        }

        response()->json([
            'success' => true,
            'count' => $this->wishlistQuery()->where('user_id', $memberId)->count(),
            'ids' => $this->keysForUser($memberId),
        ]);
    }

    protected function list(): void
    {
        $memberId = $this->currentMemberId();
        if ($memberId <= 0) {
            response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
            return;
        }

        response()->json([
            'success' => true,
            'items' => $this->accountItems($memberId),
            'count' => $this->wishlistQuery()->where('user_id', $memberId)->count(),
        ]);
    }

    protected function guestList(Request $request): void
    {
        $items = $this->decodeItemsPayload($request->input('items', '[]'));
        $pairs = [];
        foreach ($items as $item) {
            $productId = (int) ($item['productId'] ?? $item['product_id'] ?? 0);
            if ($productId <= 0) {
                continue;
            }

            $pairs[] = [
                'product_id' => $productId,
                'variant_id' => $this->normalizeVariantId($item['variantId'] ?? $item['variant_id'] ?? ''),
                'created_at' => (string) ($item['addedAt'] ?? $item['created_at'] ?? ''),
            ];
        }

        response()->json([
            'success' => true,
            'items' => $this->buildGuestItems($pairs),
            'count' => count($pairs),
        ]);
    }

    protected function remove(Request $request): void
    {
        $memberId = $this->currentMemberId();
        if ($memberId <= 0) {
            response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
            return;
        }

        $wishlistId = (int) $request->input('wishlist_id', 0);
        if ($wishlistId > 0) {
            $this->wishlistQuery()->where('user_id', $memberId)->where('id', $wishlistId)->delete();
        } else {
            $productId = (int) $request->input('product_id', 0);
            $variantId = $this->normalizeVariantId($request->input('variant_id', ''));
            if ($productId > 0) {
                $this->wishlistQuery()->where('user_id', $memberId)
                    ->where('product_id', $productId)
                    ->where('variant_id', $variantId)
                    ->delete();
            }
        }

        response()->json([
            'success' => true,
            'count' => $this->wishlistQuery()->where('user_id', $memberId)->count(),
            'ids' => $this->keysForUser($memberId),
        ]);
    }

    protected function variantOptions(Request $request): void
    {
        $productId = (int) $request->input('product_id', 0);
        if ($productId <= 0) {
            response()->json(['success' => false, 'message' => 'Invalid product'], 422);
            return;
        }

        $product = ProductModel::select('id', 'name' . $this->lang, 'status')->where('id', $productId)->first();
        if (empty($product)) {
            response()->json(['success' => false, 'message' => 'Product not found'], 404);
            return;
        }

        $variantRows = ProductPropertiesModel::select('id_properties', 'quantity', 'status')
            ->where('id_parent', $productId)
            ->get();

        if ($variantRows->isEmpty()) {
            response()->json([
                'success' => true,
                'product_id' => $productId,
                'product_name' => (string) ($product['name' . $this->lang] ?? ''),
                'has_variants' => false,
                'can_direct_add' => true,
                'suggested_variant_id' => '',
                'groups' => [],
                'combinations' => [],
            ]);
            return;
        }

        $combinationMap = [];
        $propertyIds = [];
        foreach ($variantRows as $row) {
            $variantQty = (int) ($row->quantity ?? 0);
            $variantStatus = strtolower(trim((string) ($row->status ?? 'active')));
            if ($variantQty <= 0 || $variantStatus === 'inactive') {
                continue;
            }

            $ids = array_values(array_unique(array_filter(array_map('intval', explode(',', (string) ($row->id_properties ?? ''))))));
            if (empty($ids)) {
                continue;
            }
            sort($ids);
            $key = implode(',', $ids);
            $combinationMap[$key] = $ids;
            $propertyIds = array_merge($propertyIds, $ids);
        }
        $propertyIds = array_values(array_unique($propertyIds));

        if (empty($combinationMap) || empty($propertyIds)) {
            response()->json([
                'success' => false,
                'message' => 'Sản phẩm đang hết hàng.',
                'product_id' => $productId,
                'product_name' => (string) ($product['name' . $this->lang] ?? ''),
                'has_variants' => true,
                'can_direct_add' => false,
                'suggested_variant_id' => '',
                'groups' => [],
                'combinations' => [],
            ]);
            return;
        }

        $propertyRows = PropertiesModel::select('id', 'id_list', 'name' . $this->lang, 'numb')
            ->whereIn('id', $propertyIds)
            ->orderBy('numb', 'asc')
            ->orderBy('id', 'asc')
            ->get();
        $listIds = $propertyRows->pluck('id_list')->filter()->unique()->values()->all();
        $listNames = PropertiesListModel::select('id', 'name' . $this->lang)->whereIn('id', $listIds)->get()->keyBy('id');

        $groupsMap = [];
        foreach ($propertyRows as $property) {
            $listId = (int) ($property->id_list ?? 0);
            if ($listId <= 0) {
                continue;
            }
            if (!isset($groupsMap[$listId])) {
                $groupsMap[$listId] = [
                    'list_id' => $listId,
                    'list_name' => (string) ($listNames[$listId]['name' . $this->lang] ?? ('Nhóm ' . $listId)),
                    'options' => [],
                ];
            }
            $groupsMap[$listId]['options'][] = [
                'id' => (int) $property->id,
                'name' => (string) ($property['name' . $this->lang] ?? ''),
            ];
        }

        $groups = array_values($groupsMap);
        $combinationKeys = array_values(array_keys($combinationMap));
        $canDirect = count($combinationKeys) === 1;

        response()->json([
            'success' => true,
            'product_id' => $productId,
            'product_name' => (string) ($product['name' . $this->lang] ?? ''),
            'has_variants' => count($combinationKeys) > 0,
            'can_direct_add' => $canDirect,
            'suggested_variant_id' => $canDirect ? $combinationKeys[0] : '',
            'groups' => $groups,
            'combinations' => $combinationKeys,
        ]);
    }

    public function accountItems(int $memberId): array
    {
        if ($memberId <= 0) {
            return [];
        }

        $rows = $this->wishlistQuery()->where('user_id', $memberId)->orderBy('id', 'desc')->get();
        if ($rows->isEmpty()) {
            return [];
        }

        $productIds = $rows->pluck('product_id')->map(fn($v) => (int) $v)->unique()->values()->all();
        $productCols = ['id', 'name' . $this->lang, 'slug' . $this->lang, 'photo', 'regular_price', 'sale_price', 'type', 'status'];
        $products = ProductModel::select($productCols)->whereIn('id', $productIds)->get()->keyBy('id');

        $items = [];
        $variantAvailabilityCache = [];
        foreach ($rows as $row) {
            $productId = (int) ($row->product_id ?? 0);
            $variantId = $this->normalizeVariantId($row->variant_id ?? '');
            $product = $products->get($productId);
            $status = (string) ($product->status ?? '');
            $exists = !empty($product) && (in_array('hienthi', array_filter(explode(',', $status)), true));
            $variant = $exists ? $this->resolveVariantData($productId, $variantId) : [];
            $photo = (string) ($variant['photo'] ?? $product->photo ?? '');
            $type = (string) ($product->type ?? 'san-pham');
            $thumbPath = (string) (config('type.product.' . $type . '.images.photo.thumb') ?? '320x320x1');
            $slug = (string) ($product['slug' . $this->lang] ?? '');
            $name = (string) ($product['name' . $this->lang] ?? 'Sản phẩm không còn tồn tại');
            $salePrice = (float) ($variant['sale_price'] ?? $product->sale_price ?? 0);
            $regularPrice = (float) ($variant['regular_price'] ?? $product->regular_price ?? 0);
            $priceCurrent = $salePrice > 0 ? $salePrice : $regularPrice;
            $propertyIds = $this->variantIdToPropertyIds($variantId);

            $canAddToCart = $exists;
            if ($variantId !== '') {
                $canAddToCart = $canAddToCart && !empty($variant['in_stock']);
            } else {
                if (!array_key_exists($productId, $variantAvailabilityCache)) {
                    $variantAvailabilityCache[$productId] = $this->resolveVariantAvailabilityForProduct($productId);
                }
                $variantAvailability = $variantAvailabilityCache[$productId];
                if ($variantAvailability === null) {
                    $canAddToCart = $canAddToCart && $this->isSimpleProductInStock($product);
                } else {
                    $canAddToCart = $canAddToCart && $variantAvailability;
                }
            }

            $items[] = [
                'wishlist_id' => (int) ($row->id ?? 0),
                'product_id' => $productId,
                'variant_id' => $variantId,
                'key' => $this->makeKey($productId, $variantId),
                'name' => $name,
                'slug' => $slug,
                'url' => ($exists && $slug !== '') ? url('slugweb', ['slug' => $slug]) : '',
                'photo' => $photo,
                'photo_url' => $photo !== '' ? assets_photo('product', $thumbPath, $photo, 'thumbs') : '',
                'exists' => $exists,
                'price_current' => $priceCurrent,
                'price_regular' => $regularPrice,
                'price_sale' => $salePrice,
                'price_current_text' => $priceCurrent > 0 ? Func::formatMoney($priceCurrent) : 'Liên hệ',
                'price_regular_text' => $regularPrice > 0 ? Func::formatMoney($regularPrice) : '',
                'variant_name' => $this->variantName($variantId),
                'properties' => $propertyIds,
                'can_add_to_cart' => $canAddToCart,
                'created_at' => (string) ($row->created_at ?? ''),
            ];
        }

        return $items;
    }

    private function currentMemberId(): int
    {
        $memberSession = session()->get('member');
        if (is_array($memberSession)) {
            $memberSession = $memberSession['member'] ?? 0;
        }
        return (int) $memberSession;
    }

    private function keysForUser(int $memberId): array
    {
        return $this->wishlistQuery()->where('user_id', $memberId)
            ->orderBy('id', 'desc')
            ->get(['product_id', 'variant_id'])
            ->map(function ($row) {
                return $this->makeKey((int) $row->product_id, (string) $row->variant_id);
            })
            ->values()
            ->all();
    }

    private function wishlistModel(): WishlistModel
    {
        $model = new WishlistModel();
        $model->setTable('wishlists');
        return $model;
    }

    /**
     * @param array<string,mixed> $metadata
     */
    private function safeTrackEvent(string $eventType, array $metadata, ?Request $request = null): void
    {
        try {
            $tracker = new EventTrackingService();
            $tracker->trackFromRequest([
                'event_type' => $eventType,
                'metadata' => $metadata,
                'source' => 'web',
            ], $request);
        } catch (\Throwable $e) {
            error_log('[WishlistController][track] ' . $e->getMessage());
        }
    }

    private function wishlistQuery()
    {
        return $this->wishlistModel()->newQuery();
    }

    private function wishlistCreate(array $attributes): void
    {
        $model = $this->wishlistModel();
        $model->fill($attributes);
        $model->save();
    }

    private function makeKey(int $productId, string $variantId = ''): string
    {
        return $productId . ':' . $this->normalizeVariantId($variantId);
    }

    private function normalizeVariantId(mixed $raw): string
    {
        if (is_array($raw)) {
            $ids = array_values(array_unique(array_filter(array_map('intval', $raw))));
            sort($ids);
            return implode(',', $ids);
        }

        $value = trim((string) $raw);
        if ($value === '') {
            return '';
        }

        if (preg_match('/^\d+(,\d+)*$/', $value) === 1) {
            $ids = array_values(array_unique(array_filter(array_map('intval', explode(',', $value)))));
            sort($ids);
            return implode(',', $ids);
        }

        $value = preg_replace('/[^a-zA-Z0-9,_\-]/', '', $value) ?? '';
        return substr($value, 0, 191);
    }

    private function decodeItemsPayload(mixed $raw): array
    {
        if (is_array($raw)) {
            return array_values($raw);
        }

        $decoded = json_decode((string) $raw, true);
        return is_array($decoded) ? array_values($decoded) : [];
    }

    private function variantIdToPropertyIds(string $variantId): array
    {
        if ($variantId === '' || preg_match('/^\d+(,\d+)*$/', $variantId) !== 1) {
            return [];
        }

        return array_values(array_unique(array_filter(array_map('intval', explode(',', $variantId)))));
    }

    private function resolveVariantData(int $productId, string $variantId): array
    {
        $propertyIds = $this->variantIdToPropertyIds($variantId);
        if (empty($propertyIds)) {
            return [];
        }

        $query = ProductPropertiesModel::select('regular_price', 'sale_price', 'id_photo', 'quantity', 'status')
            ->where('id_parent', $productId);
        foreach ($propertyIds as $idProperty) {
            $query->whereRaw("FIND_IN_SET(?, id_properties)", [$idProperty]);
        }
        $query->whereRaw("(LENGTH(id_properties) - LENGTH(REPLACE(id_properties, ',', '')) + 1) = ?", [count($propertyIds)]);
        $variant = $query->first();
        if (empty($variant)) {
            return [];
        }

        $photo = '';
        if (!empty($variant->id_photo)) {
            $gallery = GalleryModel::select('photo')->where('id', (int) $variant->id_photo)->first();
            $photo = (string) ($gallery->photo ?? '');
        }

        return [
            'regular_price' => (float) ($variant->regular_price ?? 0),
            'sale_price' => (float) ($variant->sale_price ?? 0),
            'photo' => $photo,
            'quantity' => (int) ($variant->quantity ?? 0),
            'in_stock' => ((int) ($variant->quantity ?? 0) > 0) &&
                strtolower(trim((string) ($variant->status ?? 'active'))) !== 'inactive',
        ];
    }
    private function isSimpleProductInStock($product): bool
    {
        if (empty($product)) {
            return false;
        }

        $statusValues = array_filter(array_map(static function ($value) {
            return strtolower(trim((string) $value));
        }, explode(',', (string) ($product->status ?? ''))));

        if (in_array('inactive', $statusValues, true)) {
            return false;
        }

        $rawQty = $product->quantity ?? null;
        if ($rawQty === null || $rawQty === '') {
            return true;
        }

        return ((int) $rawQty) > 0;
    }
    private function resolveVariantAvailabilityForProduct(int $productId): ?bool
    {
        if ($productId <= 0) {
            return null;
        }

        $variantRows = ProductPropertiesModel::select('quantity', 'status')
            ->where('id_parent', $productId)
            ->get();
        if ($variantRows->isEmpty()) {
            return null;
        }

        foreach ($variantRows as $variantRow) {
            $qty = (int) ($variantRow->quantity ?? 0);
            $statusValues = array_filter(array_map(static function ($value) {
                return strtolower(trim((string) $value));
            }, explode(',', (string) ($variantRow->status ?? 'active'))));
            $isInactive = in_array('inactive', $statusValues, true);
            if ($qty > 0 && !$isInactive) {
                return true;
            }
        }

        return false;
    }

    private function variantName(string $variantId): string
    {
        $propertyIds = $this->variantIdToPropertyIds($variantId);
        if (empty($propertyIds)) {
            return '';
        }

        $names = PropertiesModel::whereIn('id', $propertyIds)
            ->orderBy('id', 'asc')
            ->pluck('name' . $this->lang)
            ->filter(fn($name) => trim((string) $name) !== '')
            ->values()
            ->all();

        return !empty($names) ? implode(' / ', $names) : '';
    }

    private function buildGuestItems(array $pairs): array
    {
        if (empty($pairs)) {
            return [];
        }

        $productIds = array_values(array_unique(array_filter(array_map(function ($item) {
            return (int) ($item['product_id'] ?? 0);
        }, $pairs))));
        if (empty($productIds)) {
            return [];
        }

        $productCols = ['id', 'name' . $this->lang, 'slug' . $this->lang, 'photo', 'regular_price', 'sale_price', 'type', 'status'];
        $products = ProductModel::select($productCols)->whereIn('id', $productIds)->get()->keyBy('id');
        $items = [];
        $variantAvailabilityCache = [];

        foreach ($pairs as $row) {
            $productId = (int) ($row['product_id'] ?? 0);
            if ($productId <= 0) {
                continue;
            }

            $variantId = $this->normalizeVariantId($row['variant_id'] ?? '');
            $product = $products->get($productId);
            $status = (string) ($product->status ?? '');
            $exists = !empty($product) && in_array('hienthi', array_filter(explode(',', $status)), true);
            $variant = $exists ? $this->resolveVariantData($productId, $variantId) : [];
            $photo = (string) ($variant['photo'] ?? $product->photo ?? '');
            $type = (string) ($product->type ?? 'san-pham');
            $thumbPath = (string) (config('type.product.' . $type . '.images.photo.thumb') ?? '320x320x1');
            $slug = (string) ($product['slug' . $this->lang] ?? '');
            $name = (string) ($product['name' . $this->lang] ?? 'Sản phẩm không còn tồn tại');
            $salePrice = (float) ($variant['sale_price'] ?? $product->sale_price ?? 0);
            $regularPrice = (float) ($variant['regular_price'] ?? $product->regular_price ?? 0);
            $priceCurrent = $salePrice > 0 ? $salePrice : $regularPrice;
            $propertyIds = $this->variantIdToPropertyIds($variantId);

            $canAddToCart = $exists;
            if ($variantId !== '') {
                $canAddToCart = $canAddToCart && !empty($variant['in_stock']);
            } else {
                if (!array_key_exists($productId, $variantAvailabilityCache)) {
                    $variantAvailabilityCache[$productId] = $this->resolveVariantAvailabilityForProduct($productId);
                }
                $variantAvailability = $variantAvailabilityCache[$productId];
                if ($variantAvailability === null) {
                    $canAddToCart = $canAddToCart && $this->isSimpleProductInStock($product);
                } else {
                    $canAddToCart = $canAddToCart && $variantAvailability;
                }
            }

            $items[] = [
                'wishlist_id' => 0,
                'product_id' => $productId,
                'variant_id' => $variantId,
                'key' => $this->makeKey($productId, $variantId),
                'name' => $name,
                'slug' => $slug,
                'url' => ($exists && $slug !== '') ? url('slugweb', ['slug' => $slug]) : '',
                'photo' => $photo,
                'photo_url' => $photo !== '' ? assets_photo('product', $thumbPath, $photo, 'thumbs') : '',
                'exists' => $exists,
                'price_current' => $priceCurrent,
                'price_regular' => $regularPrice,
                'price_sale' => $salePrice,
                'price_current_text' => $priceCurrent > 0 ? Func::formatMoney($priceCurrent) : 'Liên hệ',
                'price_regular_text' => $regularPrice > 0 ? Func::formatMoney($regularPrice) : '',
                'variant_name' => $this->variantName($variantId),
                'properties' => $propertyIds,
                'can_add_to_cart' => $canAddToCart,
                'created_at' => (string) ($row['created_at'] ?? ''),
            ];
        }

        return $items;
    }
}
