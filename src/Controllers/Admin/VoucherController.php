<?php

namespace LARAVEL\Controllers\Admin;

use Carbon\Carbon;
use Illuminate\Http\Request;
use LARAVEL\Core\Support\Facades\File;
use LARAVEL\Core\Support\Facades\Flash;
use LARAVEL\Core\Support\Facades\Func;
use LARAVEL\Models\OrdersModel;
use LARAVEL\Models\ProductCatModel;
use LARAVEL\Models\ProductListModel;
use LARAVEL\Models\ProductModel;
use LARAVEL\Models\VoucherModel;
use LARAVEL\Models\VoucherScopeModel;
use LARAVEL\Models\VoucherUsageModel;
use LARAVEL\Traits\TraitSave;

class VoucherController
{
    use TraitSave;

    private const CATEGORY_L2_OFFSET = 1000000000;

    private $configType;

    public function __construct()
    {
        $this->configType = json_decode(json_encode(config('type')))->voucher;
    }

    public function man($com, $act, $type, Request $request)
    {
        $keyword = isset($request->keyword) ? trim($request->keyword) : '';
        $status = isset($request->status) ? trim($request->status) : '';
        $expireRange = isset($request->expire_range) ? trim($request->expire_range) : '';
        $now = Carbon::now();

        $activeKey = 'hienthi';
        if (!empty($this->configType->$type->status)) {
            $statusKeys = array_keys((array) $this->configType->$type->status);
            if (!empty($statusKeys[0])) {
                $activeKey = $statusKeys[0];
            }
        }

        $query = VoucherModel::select('*')->where('id', '<>', 0);

        if (!empty($keyword)) {
            $query->where(function ($q) use ($keyword) {
                $q->where('code', 'like', '%' . $keyword . '%')
                    ->orWhere('name', 'like', '%' . $keyword . '%');
            });
        }

        if (!empty($expireRange) && strpos($expireRange, ' to ') !== false) {
            $parts = explode(' to ', $expireRange);
            try {
                $dateFrom = Carbon::createFromFormat('d/m/Y', trim($parts[0]))->startOfDay();
                $dateTo = Carbon::createFromFormat('d/m/Y', trim($parts[1]))->endOfDay();
                $query->whereBetween('end_at', [$dateFrom, $dateTo]);
            } catch (\Exception $e) {
            }
        }

        if (!empty($status)) {
            if ($status === 'disabled') {
                $query->whereRaw('FIND_IN_SET(?,status) = 0', [$activeKey]);
            } elseif ($status === 'scheduled') {
                $query->whereNotNull('start_at')->where('start_at', '>', $now);
            } elseif ($status === 'expired') {
                $query->whereNotNull('end_at')->where('end_at', '<', $now);
            } elseif ($status === 'out_of_uses') {
                $query->whereNotNull('usage_limit_total')
                    ->whereRaw('used_count >= usage_limit_total');
            } elseif ($status === 'active') {
                $query->whereRaw('FIND_IN_SET(?,status)', [$activeKey])
                    ->where(function ($q) use ($now) {
                        $q->whereNull('start_at')->orWhere('start_at', '<=', $now);
                    })
                    ->where(function ($q) use ($now) {
                        $q->whereNull('end_at')->orWhere('end_at', '>=', $now);
                    })
                    ->where(function ($q) {
                        $q->whereNull('usage_limit_total')->orWhereRaw('used_count < usage_limit_total');
                    });
            }
        }

        $items = $query->orderBy('numb', 'asc')->orderBy('id', 'desc')->paginate(10);

        $items->getCollection()->transform(function ($item) use ($now, $activeKey) {
            $statusArray = !empty($item->status) ? explode(',', $item->status) : [];
            $isDisabled = !in_array($activeKey, $statusArray);
            $startAt = !empty($item->start_at) ? Carbon::parse($item->start_at) : null;
            $endAt = !empty($item->end_at) ? Carbon::parse($item->end_at) : null;

            if ($isDisabled) {
                $item->status_label = 'Disabled';
                $item->status_class = 'secondary';
            } elseif (!empty($endAt) && $now->gt($endAt)) {
                $item->status_label = 'Expired';
                $item->status_class = 'secondary';
            } elseif (!empty($startAt) && $now->lt($startAt)) {
                $item->status_label = 'Scheduled';
                $item->status_class = 'warning';
            } elseif (!empty($item->usage_limit_total) && (int) $item->used_count >= (int) $item->usage_limit_total) {
                $item->status_label = 'Out of uses';
                $item->status_class = 'danger';
            } else {
                $item->status_label = 'Active';
                $item->status_class = 'success';
            }

            $item->start_at = !empty($startAt) ? $startAt->format('d/m/Y H:i') : '';
            $item->end_at = !empty($endAt) ? $endAt->format('d/m/Y H:i') : '';
            return $item;
        });

        return view('voucher.man.man', ['items' => $items]);
    }

    public function edit($com, $act, $type, Request $request)
    {
        $id = isset($request->id) ? (int) $request->id : 0;
        $item = null;

        if (!empty($id)) {
            $item = VoucherModel::where('id', $id)->first();
            if (!empty($item)) {
                $item->start_at = !empty($item->start_at) ? Carbon::parse($item->start_at)->format('d/m/Y H:i') : '';
                $item->end_at = !empty($item->end_at) ? Carbon::parse($item->end_at)->format('d/m/Y H:i') : '';
                $item->exclude_products = !empty($item->exclude_products) ? json_decode($item->exclude_products, true) : [];
                $scopeCategoryIds = VoucherScopeModel::where('voucher_id', $id)
                    ->where('scope_type', 'CATEGORY')
                    ->pluck('scope_id')
                    ->toArray();
                [$scopeCategoriesL1, $scopeCategoriesL2] = $this->splitCategoryScopeIds($scopeCategoryIds);
                $item->scope_categories = $scopeCategoriesL1;
                $item->scope_categories_l2 = $scopeCategoriesL2;
                $item->scope_products = VoucherScopeModel::where('voucher_id', $id)
                    ->where('scope_type', 'PRODUCT')
                    ->pluck('scope_id')
                    ->toArray();
            }
        }

        $categories = ProductListModel::select('id', 'namevi')->orderBy('numb', 'asc')->get();
        $categoriesLevel2 = ProductCatModel::select('id', 'id_list', 'namevi')->orderBy('numb', 'asc')->get();
        $products = ProductModel::select('id', 'namevi')->orderBy('numb', 'asc')->get();

        $usages = [];
        if (!empty($id)) {
            $usageRows = VoucherUsageModel::where('voucher_id', $id)->orderBy('used_at', 'desc')->limit(10)->get();
            $orderCodes = OrdersModel::select('id', 'code')->whereIn('id', $usageRows->pluck('order_id'))->get()->keyBy('id');
            $usages = $usageRows->map(function ($row) use ($orderCodes) {
                return [
                    'order_id' => $row->order_id,
                    'order_code' => $orderCodes[$row->order_id]->code ?? null,
                    'discount_amount' => $row->discount_amount,
                    'used_at' => $row->used_at
                ];
            })->toArray();
        }

        return view('voucher.man.add', [
            'item' => $item,
            'categories' => $categories,
            'categories_level2' => $categoriesLevel2,
            'products' => $products,
            'usages' => $usages
        ]);
    }

    public function save($com, $act, $type, Request $request)
    {
        if (!empty($request->csrf_token)) {
            $message = '';
            $response = [];
            $id = !empty($request->id) ? (int) $request->id : 0;
            $data = !empty($request->data) ? $request->data : [];

            // Scope arrays are stored in voucher_scopes, not in vouchers table.
            unset($data['scope_categories'], $data['scope_categories_l2'], $data['scope_products']);
            if (!$this->hasVoucherColumn('condition_text')) {
                unset($data['condition_text']);
            }

            foreach ($data as $column => $value) {
                if (is_array($value)) continue;
                $data[$column] = htmlspecialchars(Func::sanitize($value));
            }

            if (!empty($request->status)) {
                $status = '';
                foreach ($request->status as $attr_column => $attr_value) if ($attr_value != "") $status .= $attr_column . ',';
                $data['status'] = !empty($status) ? rtrim($status, ',') : '';
            } else {
                $data['status'] = '';
            }

            $data['code'] = strtoupper($data['code'] ?? '');

            $data['discount_value'] = !empty($data['discount_value']) ? $data['discount_value'] : 0;

            $data['max_discount'] = !empty($data['max_discount']) ? $data['max_discount'] : null;
            $data['min_order_value'] = !empty($data['min_order_value']) ? $data['min_order_value'] : null;
            $data['usage_limit_total'] = !empty($data['usage_limit_total']) ? $data['usage_limit_total'] : null;
            $data['usage_limit_per_user'] = !empty($data['usage_limit_per_user']) ? $data['usage_limit_per_user'] : null;
            $data['one_voucher_per_order'] = !empty($data['one_voucher_per_order']) ? 1 : 0;

            $data['start_at'] = $this->parseDateTime($data['start_at'] ?? '');
            $data['end_at'] = $this->parseDateTime($data['end_at'] ?? '');

            $excludeProducts = $request->data['exclude_products'] ?? [];
            $data['exclude_products'] = !empty($excludeProducts) ? json_encode($excludeProducts) : null;
            if (!isset($data['used_count'])) $data['used_count'] = 0;

            if (!empty($response)) {
                foreach ($data as $k => $v) if (!empty($v)) Flash::set($k, $v);
                $response['status'] = 'danger';
                $message = base64_encode(json_encode($response));
                Flash::set('message', $message);
                response()->redirect(linkReferer());
            }

            if ($id) {
                if (VoucherModel::where('id', $id)->update($data)) {
                    $this->syncScopes($id, $request->data ?? []);
                    $this->syncPhoto($request, $id);
                    return transfer('Cập nhật dữ liệu thành công.', true, url('admin', ['com' => $com, 'act' => 'man', 'type' => $type], ['page' => $request->page]));
                }
                return transfer('Cập nhật dữ liệu thất bại.', false, linkReferer());
            }

            $itemSave = VoucherModel::create($data);
            if (!empty($itemSave)) {
                $this->syncScopes($itemSave->id, $request->data ?? []);
                $this->syncPhoto($request, $itemSave->id);
                response()->redirect(url('admin', ['com' => $com, 'act' => 'man', 'type' => $type], ['page' => $request->page]));
            }
            return transfer('Thêm dữ liệu thất bại.', false, linkReferer());
        }
    }

    public function delete($com, $act, $type, Request $request)
    {
        if (!empty($request->id)) {
            $id = (int) $request->id;
            $this->deletePhoto($id);
            VoucherModel::where('id', $id)->delete();
            VoucherScopeModel::where('voucher_id', $id)->delete();
            VoucherUsageModel::where('voucher_id', $id)->delete();
        } elseif (!empty($request->listid)) {
            $listid = explode(',', $request->listid);
            foreach ($listid as $id) {
                $id = (int) $id;
                $this->deletePhoto($id);
                VoucherModel::where('id', $id)->delete();
                VoucherScopeModel::where('voucher_id', $id)->delete();
                VoucherUsageModel::where('voucher_id', $id)->delete();
            }
        }
        response()->redirect(url('admin', ['com' => $com, 'act' => 'man', 'type' => $type], ['page' => $request->page]));
    }

    private function parseDateTime($value)
    {
        if (empty($value)) return null;
        try {
            return Carbon::createFromFormat('d/m/Y H:i', $value)->format('Y-m-d H:i:s');
        } catch (\Exception $e) {
            return null;
        }
    }

    private function syncScopes(int $voucherId, array $data)
    {
        VoucherScopeModel::where('voucher_id', $voucherId)->delete();

        $scopeType = $data['scope_type'] ?? 'ALL';
        if ($scopeType === 'CATEGORY') {
            $scopeCategories = $data['scope_categories'] ?? [];
            $scopeCategories = array_unique(array_filter(array_map('intval', $scopeCategories)));
            foreach ($scopeCategories as $categoryId) {
                VoucherScopeModel::create([
                    'voucher_id' => $voucherId,
                    'scope_type' => 'CATEGORY',
                    'scope_id' => (int) $categoryId
                ]);
            }

            $scopeCategoriesL2 = $data['scope_categories_l2'] ?? [];
            $scopeCategoriesL2 = array_unique(array_filter(array_map('intval', $scopeCategoriesL2)));
            foreach ($scopeCategoriesL2 as $categoryId) {
                VoucherScopeModel::create([
                    'voucher_id' => $voucherId,
                    'scope_type' => 'CATEGORY',
                    'scope_id' => $this->encodeCategoryL2ScopeId((int) $categoryId)
                ]);
            }
        }

        if ($scopeType === 'PRODUCT') {
            $scopeProducts = $data['scope_products'] ?? [];
            $scopeProducts = array_unique(array_filter(array_map('intval', $scopeProducts)));
            foreach ($scopeProducts as $productId) {
                VoucherScopeModel::create([
                    'voucher_id' => $voucherId,
                    'scope_type' => 'PRODUCT',
                    'scope_id' => (int) $productId
                ]);
            }
        }
    }

    private function splitCategoryScopeIds(array $scopeIds): array
    {
        $level1 = [];
        $level2 = [];

        foreach ($scopeIds as $scopeId) {
            $scopeId = (int) $scopeId;
            if ($scopeId >= self::CATEGORY_L2_OFFSET) {
                $decoded = $scopeId - self::CATEGORY_L2_OFFSET;
                if ($decoded > 0) {
                    $level2[] = $decoded;
                }
            } elseif ($scopeId > 0) {
                $level1[] = $scopeId;
            }
        }

        return [array_values(array_unique($level1)), array_values(array_unique($level2))];
    }

    private function encodeCategoryL2ScopeId(int $categoryId): int
    {
        return self::CATEGORY_L2_OFFSET + $categoryId;
    }

    private function syncPhoto(Request $request, int $id): void
    {
        if (!$this->hasPhotoColumn()) return;

        $file = $request->file('file-photo');
        $cropFile = $request->input('cropFile-photo');

        if (!empty($cropFile)) {
            $this->insertImgeCrop(VoucherModel::class, $request, $file, $cropFile, $id, 'voucher', 'photo');
            return;
        }

        if (!empty($file)) {
            $this->insertImge(VoucherModel::class, $request, $file, $id, 'voucher', 'photo');
        }
    }

    private function deletePhoto(int $id): void
    {
        if (!$this->hasPhotoColumn()) return;

        $row = VoucherModel::select('id', 'photo')->where('id', $id)->first();
        if (empty($row) || empty($row['photo'])) return;

        $photoPath = upload('voucher', $row['photo'], true);
        if (File::exists($photoPath)) {
            File::delete($photoPath);
        }
    }

    private function hasPhotoColumn(): bool
    {
        return $this->hasVoucherColumn('photo');
    }

    private function hasVoucherColumn(string $column): bool
    {
        try {
            return VoucherModel::query()
                ->getConnection()
                ->getSchemaBuilder()
                ->hasColumn((new VoucherModel())->getTable(), $column);
        } catch (\Throwable $e) {
            return false;
        }
    }
}
