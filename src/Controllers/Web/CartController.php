<?php


namespace LARAVEL\Controllers\Web;

use Carbon\Carbon;
use Illuminate\Http\Request;
use LARAVEL\Cart\Model\CartModel;
use LARAVEL\Controllers\Controller;
use LARAVEL\Core\Support\Facades\Auth;
use LARAVEL\Core\Support\Facades\Func;
use LARAVEL\Facade\Cart;
use LARAVEL\Core\Support\Facades\Email;
use LARAVEL\Models\MemberModel;
use LARAVEL\Models\NewsModel;
use LARAVEL\Models\Place\CityModel;
use LARAVEL\Models\Place\DistrictModel;
use LARAVEL\Models\Place\WardModel;
use LARAVEL\Models\GalleryModel;
use LARAVEL\Models\OrderStatusModel;
use LARAVEL\Models\OrdersModel;
use LARAVEL\Models\ProductModel;
use LARAVEL\Models\ProductPropertiesModel;
use LARAVEL\Models\PropertiesListModel;
use LARAVEL\Models\PropertiesModel;
use LARAVEL\Models\VoucherModel;
use LARAVEL\Models\VoucherUsageModel;
use LARAVEL\LARAVELGateway\VNPay\Support\Signature as VNPaySignature;
use LARAVEL\Services\EventSchemaValidator;
use LARAVEL\Services\EventTrackingService;
use LARAVEL\Traits\TraitOrderInventory;

class CartController extends Controller
{
    use TraitOrderInventory;

    public function __construct()
    {
        parent::__construct();
        \Seo::set('title', "Giỏ hàng");
        \Seo::set('meta', 'noindex,nofollow');
    }
    public function handle($action, Request $request): void
    {
        match ($action) {
            'add-to-cart' => $this->addCart($request),
            'update-to-number' => $this->updateCart($request),
            'sync-cart-stock' => $this->syncCartStock(),
            'delete-to-cart' => $this->deleteCart($request),
            'delete-all-cart' => $this->deleteAllCart(),
            'get-district' => $this->getDistrict($request),
            'get-ward' => $this->getWard($request),
            'get-ship' => $this->getShip($request),
            'send-to-cart' => $this->saveCart($request),
            'show-price' => $this->showPrice($request),
            'show-photo' => $this->showPhoto($request),
            default => 'unknown',
        };
    }
    protected function deleteAllCart()
    {
        Cart::destroy();
        transfer("Giỏ hàng của bạn đã được xóa thành công!", 1, url('home'));
    }
    protected function showPrice($request): void
    {
        $idProduct = (int)$request->id_product;
        $rawProperties = $request->properties;
        $properties = is_string($rawProperties) ? (json_decode($rawProperties, true) ?? []) : (array)$rawProperties;
        $properties = array_values(array_filter(array_map('intval', $properties)));

        $itemProduct = ProductModel::select('regular_price', 'sale_price', 'code', 'photo')->find($idProduct);
        if (empty($itemProduct)) {
            response()->json([
                'priceNew' => '',
                'priceOld' => '',
                'code' => '',
                'photo' => ''
            ]);
            return;
        }

        $query = ProductPropertiesModel::select('regular_price', 'sale_price', 'code', 'id_photo')->where('id_parent', $idProduct);
        foreach ($properties as $idProperty) {
            $query->whereRaw("FIND_IN_SET(?, id_properties)", [$idProperty]);
        }
        if (!empty($properties)) {
            $query->whereRaw("(LENGTH(id_properties) - LENGTH(REPLACE(id_properties, ',', '')) + 1) = ?", [count($properties)]);
        }
        $row = $query->first();

        $salePrice = $row->sale_price ?? $itemProduct->sale_price;
        $regularPrice = $row->regular_price ?? $itemProduct->regular_price;
        $code = $row->code ?? $itemProduct->code;
        $photo = $this->resolveVariantPhoto($idProduct, $properties, (int)($request->color_id ?? 0));
        $photoFull = '';
        $photoThumb = '';
        if (!empty($photo)) {
            $thumbDetail = !empty($itemProduct->type) ? config('type.product.' . $itemProduct->type . '.images.photo.thumb_detail') : '';
            $thumbPath = !empty($thumbDetail) ? $thumbDetail : '710x440x1';
            $photoFull = assets_photo('product', '', $photo);
            $photoThumb = assets_photo('product', $thumbPath, $photo, 'thumbs');
        }

        $hasSale = !empty($salePrice) && (float)$salePrice > 0;
        $priceNewText = Func::formatMoney($hasSale ? $salePrice : $regularPrice);
        $priceOldText = $hasSale ? Func::formatMoney($regularPrice) : '';

        response()->json([
            'priceNew' => $priceNewText,
            'priceOld' => $priceOldText,
            'code' => $code ?? '',
            'photo' => $photo ?? '',
            'photoFull' => $photoFull ?? '',
            'photoThumb' => $photoThumb ?? ''
        ]);
    }

    protected function showPhoto($request): void
    {
        $idProduct = (int)$request->id_product;
        $rawProperties = $request->properties;
        $properties = is_string($rawProperties) ? (json_decode($rawProperties, true) ?? []) : (array)$rawProperties;
        $properties = array_values(array_filter(array_map('intval', $properties)));
        $photo = $this->resolveVariantPhoto($idProduct, $properties, (int)($request->color_id ?? 0));
        $itemProduct = ProductModel::select('type')->find($idProduct);
        $thumbDetail = !empty($itemProduct?->type) ? config('type.product.' . $itemProduct->type . '.images.photo.thumb_detail') : '';
        $thumbPath = !empty($thumbDetail) ? $thumbDetail : '710x440x1';
        $photoFull = !empty($photo) ? assets_photo('product', '', $photo) : '';
        $photoThumb = !empty($photo) ? assets_photo('product', $thumbPath, $photo, 'thumbs') : '';
        response()->json([
            'photo' => $photo ?? '',
            'photoFull' => $photoFull,
            'photoThumb' => $photoThumb
        ]);
    }

    protected function resolveVariantPhoto(int $idProduct = 0, array $properties = [], int $colorId = 0): string
    {
        if (empty($idProduct)) return '';

        $query = ProductPropertiesModel::select('id_photo')->where('id_parent', $idProduct);
        foreach ($properties as $idProperty) {
            $query->whereRaw("FIND_IN_SET(?, id_properties)", [$idProperty]);
        }
        if (!empty($properties)) {
            $query->whereRaw("(LENGTH(id_properties) - LENGTH(REPLACE(id_properties, ',', '')) + 1) = ?", [count($properties)]);
        }
        $row = $query->first();
        if (!empty($row?->id_photo)) {
            $gallery = GalleryModel::select('photo')->find($row->id_photo);
            if (!empty($gallery?->photo)) return (string)$gallery->photo;
        }

        if (empty($colorId) && !empty($properties)) {
            $colorList = PropertiesListModel::select('id')->where('slugvi', 'mau')->first();
            if (!empty($colorList)) {
                $colorId = (int)PropertiesModel::where('id_list', $colorList->id)->whereIn('id', $properties)->value('id');
            }
        }
        if (!empty($colorId)) {
            $colorVariant = ProductPropertiesModel::select('id_photo')
                ->where('id_parent', $idProduct)
                ->whereRaw("FIND_IN_SET(?, id_properties)", [$colorId])
                ->where('id_photo', '>', 0)
                ->orderBy('id', 'asc')
                ->first();
            if (!empty($colorVariant?->id_photo)) {
                $gallery = GalleryModel::select('photo')->find($colorVariant->id_photo);
                if (!empty($gallery?->photo)) return (string)$gallery->photo;
            }
        }

        return '';
    }

    protected function getDistrict($request): void
    {
        $districts = DistrictModel::select(['id', 'namevi'])->where('id_city', $request->id)->get()->toArray();
        response()->json(['districts' => $districts]);
    }
    protected function getWard($request): void
    {
        $wards = WardModel::select(['id', 'namevi', 'status'])->where('id_city', $request->id)->get()->map(function ($ward) {
            return [
                'id' => (int) ($ward->id ?? 0),
                'namevi' => (string) ($ward->namevi ?? ''),
                'ship_area' => $this->parseShippingAreaFromStatus((string) ($ward->status ?? ''))
            ];
        })->toArray();
        response()->json(['wards' => $wards]);
    }
    protected function getShip($request): void
    {
        $cityId = (int) ($request->city_id ?? 0);
        $wardId = (int) ($request->ward_id ?? 0);
        $shipping = $this->getShippingFeeByArea($cityId, $wardId);
        response()->json([
            'shipPrice' => (int) ($shipping['fee'] ?? 0),
            'shipText' => !empty($shipping['fee']) ? Func::formatMoney((float) ($shipping['fee'])) : "Miễn phí",
            'shipArea' => (string) ($shipping['area'] ?? ''),
            'shipAreaLabel' => (string) ($shipping['area_label'] ?? '')
        ]);
    }
    protected function getShippingConfig(): array
    {
        $optionsRaw = Func::setting('options');
        $options = is_string($optionsRaw) ? (json_decode($optionsRaw, true) ?? []) : (array) $optionsRaw;
        $normalizeMoney = static function ($value): int {
            $normalized = preg_replace('/[^\d]/', '', (string) $value);
            return !empty($normalized) ? (int) $normalized : 0;
        };

        $defaultArea = strtolower(trim((string) ($options['ship_default_area'] ?? 'tinh-xa')));
        $defaultArea = str_replace('_', '-', $defaultArea);
        if (!in_array($defaultArea, ['noi-thanh', 'ngoai-thanh', 'tinh-xa'], true)) {
            $defaultArea = 'tinh-xa';
        }

        return [
            'default_area' => $defaultArea,
            'ship_fee_noi_thanh' => $normalizeMoney($options['ship_fee_noi_thanh'] ?? 0),
            'ship_fee_ngoai_thanh' => $normalizeMoney($options['ship_fee_ngoai_thanh'] ?? 0),
            'ship_fee_tinh_xa' => $normalizeMoney($options['ship_fee_tinh_xa'] ?? 0),
        ];
    }
    protected function parseShippingAreaFromStatus(string $status = ''): string
    {
        if (empty($status)) return '';
        $statusArray = array_filter(array_map(static function ($item) {
            return str_replace('_', '-', strtolower(trim((string) $item)));
        }, explode(',', $status)));

        if (in_array('noi-thanh', $statusArray, true)) return 'noi-thanh';
        if (in_array('ngoai-thanh', $statusArray, true)) return 'ngoai-thanh';
        if (in_array('tinh-xa', $statusArray, true)) return 'tinh-xa';
        return '';
    }
    protected function shippingAreaLabel(string $area = ''): string
    {
        return match ($area) {
            'noi-thanh' => "Nội thành",
            'ngoai-thanh' => "Ngoại thành",
            'tinh-xa' => "Tỉnh xa",
            default => ''
        };
    }
    protected function resolveShippingArea(int $cityId = 0, int $wardId = 0, string $defaultArea = 'tinh-xa'): string
    {
        if ($wardId > 0) {
            $ward = WardModel::select('id', 'id_city', 'status')->find($wardId);
            if (!empty($ward)) {
                $wardArea = $this->parseShippingAreaFromStatus((string) ($ward->status ?? ''));
                if (!empty($wardArea)) return $wardArea;

                if (empty($cityId)) $cityId = (int) ($ward->id_city ?? 0);
            }
        }

        if ($cityId > 0) {
            $city = CityModel::select('id', 'status')->find($cityId);
            if (!empty($city)) {
                $cityArea = $this->parseShippingAreaFromStatus((string) ($city->status ?? ''));
                if (!empty($cityArea)) return $cityArea;
            }
        }

        return $defaultArea;
    }
    protected function getShippingFeeByArea(int $cityId = 0, int $wardId = 0): array
    {
        $shippingConfig = $this->getShippingConfig();
        $area = $this->resolveShippingArea($cityId, $wardId, (string) ($shippingConfig['default_area'] ?? 'tinh-xa'));
        $feeKey = 'ship_fee_' . str_replace('-', '_', $area);
        $fee = (int) ($shippingConfig[$feeKey] ?? 0);

        return [
            'area' => $area,
            'area_label' => $this->shippingAreaLabel($area),
            'fee' => max(0, $fee),
        ];
    }
    protected function findVoucherForCheckout(string $voucherCode = '', int $memberId = 0, string $email = ''): ?VoucherModel
    {
        $voucherCode = strtoupper(trim($voucherCode));
        if (empty($voucherCode)) return null;

        $now = Carbon::now();
        $voucher = VoucherModel::where('code', $voucherCode)
            ->whereRaw("FIND_IN_SET(?,status)", ['hienthi'])
            ->where(function ($query) use ($now) {
                $query->whereNull('start_at')->orWhere('start_at', '<=', $now);
            })
            ->where(function ($query) use ($now) {
                $query->whereNull('end_at')->orWhere('end_at', '>=', $now);
            })
            ->where(function ($query) {
                $query->whereNull('usage_limit_total')->orWhereRaw('used_count < usage_limit_total');
            })
            ->first();

        if (empty($voucher)) return null;
        if ($this->isVoucherPerUserLimitReached($voucher, $memberId, $email)) return null;
        return $voucher;
    }
    protected function calculateVoucherAdjustment(?VoucherModel $voucher, float $subtotal = 0, float $shipPrice = 0): array
    {
        $subtotal = max(0, (float) $subtotal);
        $baseShipPrice = max(0, (float) $shipPrice);
        $result = [
            'applied' => false,
            'voucher_code' => '',
            'voucher_type' => '',
            'voucher_discount' => 0.0,
            'ship_price' => $baseShipPrice
        ];
        if (empty($voucher)) return $result;

        $minOrderValue = max(0, (float) ($voucher->min_order_value ?? 0));
        if ($minOrderValue > 0 && $subtotal < $minOrderValue) return $result;

        $discountType = strtoupper(trim((string) ($voucher->discount_type ?? 'FIXED_AMOUNT')));
        $discountValue = max(0, (float) ($voucher->discount_value ?? 0));
        $maxDiscount = max(0, (float) ($voucher->max_discount ?? 0));
        $discountAmount = 0.0;
        $finalShipPrice = $baseShipPrice;

        if ($discountType === 'PERCENT') {
            $discountAmount = round(($subtotal * $discountValue) / 100);
            if ($maxDiscount > 0) {
                $discountAmount = min($discountAmount, round($maxDiscount));
            }
            $discountAmount = min($discountAmount, $subtotal);
        } elseif ($discountType === 'FREE_SHIP') {
            $maxShipDiscount = round($discountValue);
            $shipDiscount = $maxShipDiscount > 0 ? min($finalShipPrice, $maxShipDiscount) : $finalShipPrice;
            $finalShipPrice = max(0, $finalShipPrice - $shipDiscount);
            $discountAmount = 0;
        } else {
            $discountAmount = round($discountValue);
            $discountAmount = min($discountAmount, $subtotal);
        }

        $result['applied'] = true;
        $result['voucher_code'] = strtoupper(trim((string) ($voucher->code ?? '')));
        $result['voucher_type'] = $discountType;
        $result['voucher_discount'] = max(0, (float) $discountAmount);
        $result['ship_price'] = max(0, (float) $finalShipPrice);
        return $result;
    }
    protected function hasVoucherUsageColumn(string $column): bool
    {
        try {
            return VoucherUsageModel::query()
                ->getConnection()
                ->getSchemaBuilder()
                ->hasColumn((new VoucherUsageModel())->getTable(), $column);
        } catch (\Throwable $e) {
            return false;
        }
    }
    protected function isVoucherPerUserLimitReached(?VoucherModel $voucher, int $memberId = 0, string $email = ''): bool
    {
        if (empty($voucher)) return false;
        $usageLimitPerUser = (int) ($voucher->usage_limit_per_user ?? 0);
        if ($usageLimitPerUser <= 0) return false;

        try {
            $query = VoucherUsageModel::query()->where('voucher_id', (int) ($voucher->id ?? 0));
            $hasUserIdentity = false;

            if ($memberId > 0) {
                if ($this->hasVoucherUsageColumn('member_id')) {
                    $query->where('member_id', $memberId);
                    $hasUserIdentity = true;
                } else if ($this->hasVoucherUsageColumn('id_user')) {
                    $query->where('id_user', $memberId);
                    $hasUserIdentity = true;
                }
            }

            $email = trim((string) $email);
            if (!$hasUserIdentity && !empty($email)) {
                if ($this->hasVoucherUsageColumn('email')) {
                    $query->where('email', $email);
                    $hasUserIdentity = true;
                } else if ($this->hasVoucherUsageColumn('user_email')) {
                    $query->where('user_email', $email);
                    $hasUserIdentity = true;
                }
            }

            if (!$hasUserIdentity) return false;
            return $query->count() >= $usageLimitPerUser;
        } catch (\Throwable $e) {
            return false;
        }
    }
    protected function recordVoucherUsage(?VoucherModel $voucher, ?CartModel $order, float $discountAmount = 0, int $memberId = 0, string $email = ''): void
    {
        if (empty($voucher) || empty($order?->id)) return;

        try {
            VoucherModel::where('id', (int) ($voucher->id ?? 0))->increment('used_count');

            $payload = [];
            if ($this->hasVoucherUsageColumn('voucher_id')) $payload['voucher_id'] = (int) ($voucher->id ?? 0);
            if ($this->hasVoucherUsageColumn('order_id')) $payload['order_id'] = (int) ($order->id ?? 0);
            if ($this->hasVoucherUsageColumn('discount_amount')) $payload['discount_amount'] = max(0, (float) $discountAmount);

            if ($memberId > 0) {
                if ($this->hasVoucherUsageColumn('member_id')) $payload['member_id'] = $memberId;
                else if ($this->hasVoucherUsageColumn('id_user')) $payload['id_user'] = $memberId;
            }

            $email = trim((string) $email);
            if (!empty($email)) {
                if ($this->hasVoucherUsageColumn('email')) $payload['email'] = $email;
                else if ($this->hasVoucherUsageColumn('user_email')) $payload['user_email'] = $email;
            }

            if ($this->hasVoucherUsageColumn('used_at')) $payload['used_at'] = Carbon::now()->format('Y-m-d H:i:s');
            if ($this->hasVoucherUsageColumn('date_created')) $payload['date_created'] = time();
            if ($this->hasVoucherUsageColumn('date_updated')) $payload['date_updated'] = time();

            if (!empty($payload)) {
                VoucherUsageModel::create($payload);
            }
        } catch (\Throwable $e) {
        }
    }
    protected function currentCheckoutMember(): ?MemberModel
    {
        $memberSession = session()->get('member');
        if (is_array($memberSession)) {
            $memberSession = $memberSession['member'] ?? 0;
        }
        $memberId = (int) $memberSession;
        if ($memberId <= 0) {
            return null;
        }

        $member = MemberModel::where('id', $memberId)->first();
        if (empty($member)) {
            return null;
        }

        if (strtolower(trim((string) ($member->status ?? ''))) === 'locked') {
            return null;
        }

        return $member;
    }
    protected function loadMemberAddresses(int $memberId = 0): array
    {
        if ($memberId <= 0) {
            return [];
        }

        $path = $this->memberAddressStoragePath($memberId);
        if (!is_file($path)) {
            return [];
        }

        $payload = json_decode((string) file_get_contents($path), true);
        return is_array($payload) ? array_values($payload) : [];
    }
    protected function memberAddressStoragePath(int $memberId): string
    {
        $dir = dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'caches' . DIRECTORY_SEPARATOR . 'member_addresses';
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }

        return $dir . DIRECTORY_SEPARATOR . 'member_' . $memberId . '.json';
    }
    protected function hydrateAddressLocationIds(array $addresses = []): array
    {
        foreach ($addresses as $index => $address) {
            $cityName = trim((string) ($address['city'] ?? ''));
            $wardName = trim((string) ($address['ward'] ?? ''));
            $cityId = 0;
            $wardId = 0;

            if ($cityName !== '') {
                $cityId = (int) CityModel::whereRaw('LOWER(namevi) = ?', [$this->normalizePlaceName($cityName)])->value('id');
                if ($cityId <= 0) {
                    $cityId = (int) CityModel::where('namevi', $cityName)->value('id');
                }
            }

            if ($cityId > 0 && $wardName !== '') {
                $wardId = (int) WardModel::where('id_city', $cityId)
                    ->whereRaw('LOWER(namevi) = ?', [$this->normalizePlaceName($wardName)])
                    ->value('id');
                if ($wardId <= 0) {
                    $wardId = (int) WardModel::where('id_city', $cityId)
                        ->where('namevi', $wardName)
                        ->value('id');
                }
            }

            $addresses[$index]['city_id'] = $cityId;
            $addresses[$index]['ward_id'] = $wardId;
        }

        return $addresses;
    }
    protected function normalizePlaceName(string $value = ''): string
    {
        return mb_strtolower(trim($value), 'UTF-8');
    }
    public function showcart(Request $request)
    {
        $httt = NewsModel::where('type', 'hinh-thuc-thanh-toan')->whereRaw("FIND_IN_SET(?,status)", ['hienthi'])->orderBy('id', 'desc')->get();
        $city = CityModel::select('id', 'namevi')->orderBy('id', 'asc')->get();
        $now = Carbon::now();
        $vouchers = VoucherModel::select('*')
            ->whereRaw("FIND_IN_SET(?,status)", ['hienthi'])
            ->where(function ($query) use ($now) {
                $query->whereNull('start_at')->orWhere('start_at', '<=', $now);
            })
            ->where(function ($query) use ($now) {
                $query->whereNull('end_at')->orWhere('end_at', '>=', $now);
            })
            ->where(function ($query) {
                $query->whereNull('usage_limit_total')->orWhereRaw('used_count < usage_limit_total');
            })
            ->orderBy('id', 'desc')
            ->get();

        $member = $this->currentCheckoutMember();
        $memberAddresses = [];
        $checkoutPrefill = [
            'fullname' => '',
            'phone' => '',
            'email' => '',
            'address' => '',
            'city_id' => 0,
            'city_name' => '',
            'ward_id' => 0,
            'ward_name' => '',
        ];

        if (!empty($member)) {
            $checkoutPrefill['fullname'] = trim((string) ($member->fullname ?? ''));
            $checkoutPrefill['phone'] = trim((string) ($member->phone ?? ''));
            $checkoutPrefill['email'] = trim((string) ($member->email ?? ''));
            $checkoutPrefill['address'] = trim((string) ($member->address ?? ''));

            $memberAddresses = $this->hydrateAddressLocationIds($this->loadMemberAddresses((int) $member->id));
            $defaultAddress = [];
            foreach ($memberAddresses as $address) {
                if (!empty($address['is_default'])) {
                    $defaultAddress = $address;
                    break;
                }
            }
            if (empty($defaultAddress) && !empty($memberAddresses)) {
                $defaultAddress = $memberAddresses[0];
            }

            if (!empty($defaultAddress)) {
                $checkoutPrefill['fullname'] = trim((string) ($defaultAddress['recipient_name'] ?? $checkoutPrefill['fullname']));
                $checkoutPrefill['phone'] = trim((string) ($defaultAddress['recipient_phone'] ?? $checkoutPrefill['phone']));
                $checkoutPrefill['address'] = trim((string) ($defaultAddress['address_line'] ?? $checkoutPrefill['address']));
                $checkoutPrefill['city_id'] = (int) ($defaultAddress['city_id'] ?? 0);
                $checkoutPrefill['city_name'] = trim((string) ($defaultAddress['city'] ?? ''));
                $checkoutPrefill['ward_id'] = (int) ($defaultAddress['ward_id'] ?? 0);
                $checkoutPrefill['ward_name'] = trim((string) ($defaultAddress['ward'] ?? ''));
            }
        }

        $shippingConfig = $this->getShippingConfig();
        $defaultShipPrice = (int) ($shippingConfig['ship_fee_' . str_replace('-', '_', $shippingConfig['default_area'])] ?? 0);
        $cartCodesByRow = [];
        $cartStockByRow = [];
        $cartHasOutOfStock = false;
        foreach (Cart::content() as $cartItem) {
            $resolvedCode = trim((string) ($cartItem->options->productCode ?? ''));
            $itemProduct = $cartItem->options->itemProduct ?? null;

            if ($resolvedCode === '') {
                $propertyIds = [];
                $propertiesOption = $cartItem->options->properties ?? [];

                if ($propertiesOption instanceof \Illuminate\Support\Collection) {
                    $propertyIds = $propertiesOption
                        ->pluck('id')
                        ->map(function ($id) {
                            return (int) $id;
                        })
                        ->filter(function ($id) {
                            return $id > 0;
                        })
                        ->values()
                        ->all();
                } else {
                    foreach ((array) $propertiesOption as $propertyRow) {
                        $propertyId = 0;
                        if (is_array($propertyRow)) {
                            $propertyId = (int) ($propertyRow['id'] ?? 0);
                        } elseif (is_object($propertyRow)) {
                            $propertyId = (int) ($propertyRow->id ?? 0);
                        }
                        if ($propertyId > 0) {
                            $propertyIds[] = $propertyId;
                        }
                    }
                    $propertyIds = array_values(array_unique($propertyIds));
                }

                $productId = (int) ($itemProduct->id ?? 0);
                if ($productId > 0 && !empty($propertyIds)) {
                    $variantCodeQuery = ProductPropertiesModel::select('code')->where('id_parent', $productId);
                    foreach ($propertyIds as $propertyId) {
                        $variantCodeQuery->whereRaw("FIND_IN_SET(?, id_properties)", [$propertyId]);
                    }
                    $variantCodeQuery->whereRaw("(LENGTH(id_properties) - LENGTH(REPLACE(id_properties, ',', '')) + 1) = ?", [count($propertyIds)]);
                    $variantCodeRow = $variantCodeQuery->first();
                    if (!empty($variantCodeRow?->code)) {
                        $resolvedCode = trim((string) $variantCodeRow->code);
                    }
                }

                if ($resolvedCode === '') {
                    $resolvedCode = trim((string) ($itemProduct->code ?? ''));
                }
            }

            $cartCodesByRow[(string) ($cartItem->rowId ?? '')] = $resolvedCode;
            $stockState = $this->resolveCartItemStockState($cartItem, (int) ($cartItem->qty ?? 1));
            $cartStockByRow[(string) ($cartItem->rowId ?? '')] = $stockState;
            if (empty($stockState['in_stock'])) {
                $cartHasOutOfStock = true;
            }
        }
        return view('giohang.index', compact(
            'httt',
            'city',
            'vouchers',
            'defaultShipPrice',
            'member',
            'memberAddresses',
            'checkoutPrefill',
            'cartCodesByRow',
            'cartStockByRow',
            'cartHasOutOfStock'
        ));
    }
    public function orderLookup(Request $request)
    {
        $code = strtoupper(trim((string) $request->query('code', '')));
        $memberIdFromSession = $this->resolveMemberIdFromSession();

        if ($memberIdFromSession > 0) {
            if ($code !== '') {
                $ownedOrderId = (int) (OrdersModel::where('code', $code)
                    ->where('id_user', $memberIdFromSession)
                    ->value('id') ?? 0);
                if ($ownedOrderId > 0) {
                    return response()->redirect(url('user.account', null, [
                        'section' => 'orders',
                        'order_id' => $ownedOrderId,
                    ]));
                }
            }
            return response()->redirect(url('user.account', null, ['section' => 'orders']));
        }

        $order = null;
        $lookupError = '';
        if ($code !== '') {
            $order = OrdersModel::with(['getStatus', 'getPayment'])->where('code', $code)->first();
            if (empty($order)) {
                $lookupError = 'Không tìm thấy đơn hàng với mã bạn nhập.';
            }
        }

        $lookupCreatedAt = '';
        $lookupAddress = '';
        $lookupStatusName = '';
        $lookupPaymentName = '';
        if (!empty($order)) {
            $infoUser = is_array($order->info_user ?? null) ? $order->info_user : [];
            $lookupCreatedAt = $this->formatOrderCreatedAt($order->created_at ?? null);
            $lookupAddress = $this->buildOrderAddressText($infoUser);
            $lookupStatusName = trim((string) ($order->getStatus->namevi ?? ''));
            if ($lookupStatusName === '') {
                $lookupStatusName = trim((string) Func::showName('order_status', (int) ($order->order_status ?? 0), 'namevi'));
            }
            $lookupPaymentName = trim((string) ($order->getPayment->namevi ?? ''));
            if ($lookupPaymentName === '') {
                $lookupPaymentName = trim((string) Func::showName('news', (int) ($order->order_payment ?? 0), 'namevi'));
            }
        }

        \Seo::set('title', 'Tra cứu đơn hàng');

        return view('giohang.lookup', [
            'titleMain' => 'Tra cứu đơn hàng',
            'lookupCode' => $code,
            'lookupOrder' => $order,
            'lookupError' => $lookupError,
            'lookupCreatedAt' => $lookupCreatedAt,
            'lookupAddress' => $lookupAddress,
            'lookupStatusName' => $lookupStatusName,
            'lookupPaymentName' => $lookupPaymentName,
        ]);
    }
    public function orderSuccess(Request $request)
    {
        $code = strtoupper(trim((string) $request->query('code', '')));
        if ($code === '') {
            return response()->redirect(url('home'));
        }

        $order = OrdersModel::where('code', $code)->first();
        if (empty($order)) {
            return response()->redirect(url('home'));
        }

        $isOnlinePayment = $this->isVNPayPaymentMethod((int) ($order->order_payment ?? 0));
        $isPaid = $isOnlinePayment && ((int) $request->query('paid', 0) === 1);

        $title = $isPaid ? 'Thanh toán thành công!' : 'Đặt hàng thành công!';
        $paymentStatusText = $isPaid
            ? 'Đã thanh toán'
            : 'Đặt hàng thành công - Thanh toán khi nhận hàng';

        $createdAtText = $this->formatOrderCreatedAt($order->created_at ?? null);
        $memberIdFromSession = $this->resolveMemberIdFromSession();
        if ($memberIdFromSession > 0 && (int) ($order->id_user ?? 0) === $memberIdFromSession) {
            $orderLookupUrl = url('user.account', null, [
                'section' => 'orders',
                'order_id' => (int) ($order->id ?? 0),
            ]);
        } else {
            $orderLookupUrl = $this->buildPublicOrderLookupUrl((string) ($order->code ?? ''));
        }

        \Seo::set('title', $title);

        return view('giohang.success', [
            'titleMain' => $title,
            'subtitle' => 'Cảm ơn bạn. Đơn hàng của bạn đã được ghi nhận.',
            'orderCode' => (string) ($order->code ?? ''),
            'paymentStatusText' => $paymentStatusText,
            'createdAtText' => $createdAtText,
            'homeUrl' => (string) url('home'),
            'orderLookupUrl' => (string) $orderLookupUrl,
        ]);
    }
    protected function buildCheckoutSuccessUrl($order, bool $paid = false): string
    {
        $orderCode = strtoupper(trim((string) ($order->code ?? '')));
        if ($orderCode === '') {
            return (string) url('home');
        }

        return (string) url('order.success', null, [
            'code' => $orderCode,
            'paid' => $paid ? 1 : 0,
        ]);
    }
    protected function resolveMemberIdFromSession(): int
    {
        $memberSession = session()->get('member');
        if (is_array($memberSession)) {
            $memberSession = $memberSession['member'] ?? 0;
        }
        return max(0, (int) $memberSession);
    }
    protected function buildPublicOrderLookupUrl(string $orderCode = ''): string
    {
        $params = [];
        $orderCode = strtoupper(trim($orderCode));
        if ($orderCode !== '') {
            $params['code'] = $orderCode;
        }
        return (string) url('order.lookup', null, $params);
    }
    protected function formatOrderCreatedAt($createdAtRaw = null): string
    {
        $createdAtTimestamp = time();
        if (is_numeric($createdAtRaw)) {
            $createdAtTimestamp = (int) $createdAtRaw;
        } else {
            $parsedCreatedAt = strtotime((string) $createdAtRaw);
            if ($parsedCreatedAt !== false && $parsedCreatedAt > 0) {
                $createdAtTimestamp = $parsedCreatedAt;
            }
        }
        return date('d/m/Y H:i', $createdAtTimestamp);
    }
    protected function buildOrderAddressText(array $infoUser = []): string
    {
        $address = trim((string) ($infoUser['address'] ?? ''));
        $wardName = trim((string) ($infoUser['ward_name'] ?? ($infoUser['ward'] ?? '')));
        $cityName = trim((string) ($infoUser['city_name'] ?? ($infoUser['city'] ?? '')));
        return implode(', ', array_filter([$address, $wardName, $cityName], static fn($v) => $v !== ''));
    }
    public function saveCart(Request $request)
    {
        $idMember = 0;
        $memberSession = session()->get('member');
        if (is_array($memberSession)) {
            $memberSession = $memberSession['member'] ?? 0;
        }
        $memberIdFromSession = (int) $memberSession;
        if ($memberIdFromSession > 0) {
            $member = MemberModel::where('id', $memberIdFromSession)->first();
            if (!empty($member)) {
                if (strtolower(trim((string) ($member->status ?? ''))) === 'locked') {
                    session()->unset('member');
                    session()->unset('member_name');
                    return transfer("Tài khoản đã bị khóa.", false, url('user.login'));
                }
                $idMember = (int) $member->id;
            }
        }
        if (empty($request->get('dataOrder'))) return url('home');
        $cartStockValidation = $this->validateCartStockLines();
        if (empty($cartStockValidation['is_valid'])) {
            $firstCartError = $cartStockValidation['first_error'] ?? [];
            $messageStock = trim((string) ($firstCartError['message'] ?? ''));
            if ($messageStock === '') {
                $messageStock = "Ton kho da thay doi. Vui long kiem tra lai gio hang.";
            }
            return transfer($messageStock, false, (string) url('giohang'));
        }
        $dataOrder = $request->get('dataOrder');
        $cityId = (int) ($dataOrder['city'] ?? 0);
        $wardId = (int) ($dataOrder['ward'] ?? 0);
        $cityName = '';
        $wardName = '';

        if ($wardId > 0) {
            $wardInfo = WardModel::select('id_city', 'namevi')->find($wardId);
            if (!empty($wardInfo)) {
                $wardName = trim((string) ($wardInfo->namevi ?? ''));
                if (empty($cityId)) $cityId = (int) ($wardInfo->id_city ?? 0);
            }
        }

        if ($cityId > 0) {
            $cityName = trim((string) (CityModel::where('id', $cityId)->value('namevi') ?? ''));
        }

        $info_user['fullname'] = $dataOrder['fullname'];
        $info_user['phone'] = $dataOrder['phone'];
        $info_user['email'] = $dataOrder['email'];
        $info_user['city'] = $cityId;
        $info_user['city_name'] = $cityName;
        // $info_user['district'] = $dataOrder['district'];
        $info_user['ward'] = $wardId;
        $info_user['ward_name'] = $wardName;
        $info_user['address'] = $dataOrder['address'];
        $dataOrderSave['id_user'] = $idMember ?? 0;
        $dataOrderSave['order_payment'] = $dataOrder['payments'];
        $selectedPaymentId = (int) ($dataOrder['payments'] ?? 0);
        $isVNPayPayment = $this->isVNPayPaymentMethod($selectedPaymentId);
        $requirements = trim((string) ($dataOrder['requirements'] ?? ''));
        $tempPrice = (float) Cart::subtotalFloat();
        $shipping = $this->getShippingFeeByArea($cityId, $wardId);
        $baseShipPrice = max(0, (float) ($shipping['fee'] ?? 0));
        $shipPrice = $baseShipPrice;
        $voucherCode = strtoupper(trim((string) ($dataOrder['voucher_code'] ?? '')));
        $voucher = $this->findVoucherForCheckout($voucherCode, (int) $idMember, (string) ($info_user['email'] ?? ''));
        $voucherAdjustment = $this->calculateVoucherAdjustment($voucher, $tempPrice, $shipPrice);
        if (!empty($voucherAdjustment['applied']) && !empty($voucherAdjustment['voucher_code'])) {
            $requirements = trim($requirements . PHP_EOL . 'Voucher: ' . $voucherAdjustment['voucher_code']);
        }
        $shipAreaLabel = (string) ($shipping['area_label'] ?? '');
        if (!empty($shipAreaLabel)) {
            $requirements = trim($requirements . PHP_EOL . "Khu vực giao hàng: " . $shipAreaLabel);
        }
        $dataOrderSave['requirements'] = $requirements;
        $dataOrderSave['numb'] = 1;
        $dataOrderSave['order_status'] = $isVNPayPayment
            ? $this->resolvePendingPaymentStatusId()
            : 1;
        if ($isVNPayPayment) {
            $dataOrderSave['notes'] = "Chờ thanh toán VNPay.";
        }
        $shipPrice = max(0, (float) ($voucherAdjustment['ship_price'] ?? $shipPrice));
        $voucherDiscount = max(0, (float) ($voucherAdjustment['voucher_discount'] ?? 0));
        $totalPrice = max(0, $tempPrice + $shipPrice - $voucherDiscount);
        $dataOrderSave['ship_price'] = $shipPrice;
        $dataOrderSave['temp_price'] = $tempPrice;
        $dataOrderSave['total_price'] = $totalPrice;
        $dataOrderSave['code'] =  strtoupper(Func::stringRandom(10));
        $orderDetail = Cart::content();
        $reserveInventoryResult = $this->reserveOrderInventory($orderDetail);
        if (empty($reserveInventoryResult['status'])) {
            $messageStock = (string) ($reserveInventoryResult['message'] ?? "Tồn kho đã thay đổi. Vui lòng thử lại.");
            return transfer($messageStock, false, linkReferer());
        }
        $info_user = $this->markReservedInventoryFlag($info_user);
        $dataOrderSave['info_user'] = $info_user;
        $dataOrderSave['order_detail'] = $orderDetail;
        $cartSave = CartModel::create($dataOrderSave);
        if (!empty($cartSave)) {
            if (!empty($voucherAdjustment['applied']) && !empty($voucher)) {
                $voucherUsageDiscount = $voucherDiscount;
                if (($voucherAdjustment['voucher_type'] ?? '') === 'FREE_SHIP') {
                    $voucherUsageDiscount = max(0, $baseShipPrice - $shipPrice);
                }
                $this->recordVoucherUsage(
                    $voucher,
                    $cartSave,
                    (float) $voucherUsageDiscount,
                    (int) $idMember,
                    (string) ($info_user['email'] ?? '')
                );
            }

            if ($isVNPayPayment) {
                try {
                    $order = OrdersModel::where('id', (int) ($cartSave->id ?? 0))->first();
                    if (empty($order)) {
                        $order = OrdersModel::where('code', (string) ($cartSave->code ?? ''))->first();
                    }

                    if (empty($order)) {
                        throw new \RuntimeException("Không tìm thấy đơn hàng vừa tạo để chuyển VNPay.");
                    }

                    $redirectUrl = $this->buildVNPayRedirectUrl($order, $request);
                    $this->sendOrderCheckoutEmails($cartSave, $info_user);
                    Cart::destroy();
                    return response()->redirect($redirectUrl);
                } catch (\Throwable $e) {
                    if (!empty($order ?? null)) {
                        $this->markVNPayFailedOrder($order, "Khởi tạo VNPay thất bại: " . $e->getMessage(), false);
                    } else {
                        $this->releaseOrderInventory($orderDetail);
                    }

                    return transfer("Khởi tạo thanh toán VNPay thất bại. Vui lòng thử lại.", false, url('giohang'));
                }
            }

            $this->trackPurchaseFromOrder($cartSave, false, $request);
            Cart::destroy();
            if ($this->sendOrderCheckoutEmails($cartSave, $info_user)) {
                return response()->redirect($this->buildCheckoutSuccessUrl($cartSave, false));
            } else {
                return transfer("Thông tin đơn hàng gửi thất bại.", false, url('home'));
            }
        } else {
            $this->releaseOrderInventory($orderDetail);
            return transfer("Thông tin đơn hàng gửi thất bại.", false, url('home'));
        }
    }
    protected function sendOrderCheckoutEmails($order, array $infoUser = []): bool
    {
        $optCompany = json_decode(Func::setting('options'), true);
        if (!is_array($optCompany)) {
            $optCompany = [];
        }
        $company = Func::setting();
        $subject = "Thông tin đơn hàng từ " . (string) ($company['namevi'] ?? '');
        $message = Email::markdown('giohang.send', $order);

        $isAdminSent = Email::send("admin", null, $subject, $message, '', $optCompany, $company);
        if (!$isAdminSent) {
            return false;
        }

        $customerEmail = trim((string) ($infoUser['email'] ?? ''));
        if ($customerEmail === '') {
            return true;
        }

        $arrayEmail = array(
            "dataEmail" => array(
                "name" => (string) ($infoUser['fullname'] ?? ''),
                "email" => $customerEmail
            )
        );
        Email::send("customer", $arrayEmail, $subject, $message, '', $optCompany, $company);

        return true;
    }
    public function retryVNPayPayment(Request $request)
    {
        $ordersUrl = (string) url('user.account', null, ['section' => 'orders']);
        if (empty($request->csrf_token)) {
            return transfer("Phiên làm việc đã hết hạn. Vui lòng tải lại trang.", false, $ordersUrl);
        }

        $memberId = $this->resolveMemberIdFromSession();
        if ($memberId <= 0) {
            return transfer("Vui lòng đăng nhập lại để tiếp tục.", false, (string) url('user.login'));
        }

        $orderId = max(0, (int) $request->input('order_id', 0));
        if ($orderId <= 0) {
            return transfer("Đơn hàng không hợp lệ.", false, $ordersUrl);
        }

        $accountOrderUrl = (string) url('user.account', null, [
            'section' => 'orders',
            'order_id' => $orderId,
        ]);

        $order = OrdersModel::where('id', $orderId)
            ->where('id_user', $memberId)
            ->first();
        if (empty($order)) {
            return transfer("Không tìm thấy đơn hàng cần thanh toán lại.", false, $ordersUrl);
        }

        if (!$this->canRetryVNPayOrder($order)) {
            return transfer("Đơn hàng chưa đủ điều kiện để thanh toán lại VNPay.", false, $accountOrderUrl);
        }

        try {
            $infoUserRaw = $order->info_user ?? [];
            $infoUser = is_array($infoUserRaw) ? $infoUserRaw : (array) $infoUserRaw;
            if (!$this->hasReservedInventoryFlag($infoUser)) {
                $reserveResult = $this->reserveOrderInventory($order->order_detail ?? []);
                if (empty($reserveResult['status'])) {
                    $message = trim((string) ($reserveResult['message'] ?? ''));
                    if ($message === '') {
                        $message = "Tồn kho đã thay đổi, chưa thể thanh toán lại đơn này.";
                    }
                    return transfer($message, false, $accountOrderUrl);
                }
                $updatedInfoUser = $this->markReservedInventoryFlag($infoUser);
                OrdersModel::where('id', (int) $order->id)->update(['info_user' => $updatedInfoUser]);
                $order->info_user = $updatedInfoUser;
            }

            $pendingStatusId = $this->resolvePendingPaymentStatusId();
            if ($pendingStatusId > 0 && (int) ($order->order_status ?? 0) !== $pendingStatusId) {
                OrdersModel::where('id', (int) $order->id)->update(['order_status' => $pendingStatusId]);
                $order->order_status = $pendingStatusId;
            }

            $this->appendOrderNote($order, 'Yêu cầu thanh toán lại VNPay.');
            $redirectUrl = $this->buildVNPayRedirectUrl($order, $request);
            return response()->redirect($redirectUrl);
        } catch (\Throwable $e) {
            $this->markVNPayFailedOrder($order, "Khởi tạo lại VNPay thất bại: " . $e->getMessage(), false);
            return transfer("Không thể khởi tạo lại thanh toán VNPay. Vui lòng thử lại.", false, $accountOrderUrl);
        }
    }
    public function vnpayReturn(Request $request)

    
    {
        return $this->handleVNPayCallback($request, false);

    }
    public function vnpayIpn(Request $request)
    {
        return $this->handleVNPayCallback($request, true);
    }
    protected function handleVNPayCallback(Request $request, bool $isIpn = false)
    {
        
        $payload = $request->query->all();
        $cartUrl = $this->resolveCallbackRedirectUrl($this->extractRoutePath((string) url('giohang')));
        if ($cartUrl === '') {
            $cartUrl = (string) url('giohang');
        }

        if (empty($payload)) {
            return $isIpn
                ? $this->buildVNPayIpnResponse('99', "Dữ liệu không hợp lệ")
                : transfer("Dữ liệu thanh toán VNPay không hợp lệ.", false, $cartUrl);
        }

        if (!$this->validateVNPaySignature($payload)) {
            return $isIpn
                ? $this->buildVNPayIpnResponse('97', "Chữ ký không hợp lệ")
                : transfer("Chữ ký VNPay không hợp lệ.", false, $cartUrl);
        }

        $txnRef = trim((string) ($payload['vnp_TxnRef'] ?? ''));
        if ($txnRef === '') {
            return $isIpn
                ? $this->buildVNPayIpnResponse('01', "Không tìm thấy đơn hàng")
                : transfer("Không tìm thấy mã đơn hàng VNPay.", false, $cartUrl);
        }

        $order = OrdersModel::where('code', $txnRef)->first();
        if (empty($order)) {
            return $isIpn
                ? $this->buildVNPayIpnResponse('01', "Không tìm thấy đơn hàng")
                : transfer("Không tìm thấy đơn hàng thanh toán.", false, $cartUrl);
        }

        $responseCode = trim((string) ($payload['vnp_ResponseCode'] ?? ''));
        $transactionNo = trim((string) ($payload['vnp_TransactionNo'] ?? ''));
        $message = $this->resolveVNPayResponseMessage($payload);

        if ($responseCode === '00') {
            $successNote = "VNPay thành công"
                . '; TxnRef=' . $txnRef
                . ($transactionNo !== '' ? '; TransactionNo=' . $transactionNo : '');
            $this->markVNPaySuccessOrder($order, $successNote);
            if (!$isIpn) {
                $this->trackPurchaseFromOrder($order, true, $request);
            }

            if ($isIpn) {
                return $this->buildVNPayIpnResponse('00', 'Confirm Success');
            }

            return response()->redirect($this->resolveCallbackRedirectUrl($this->buildCheckoutSuccessUrl($order, true)));
        }

        $isCancel = $responseCode === '24';
        $failedNote = "VNPay thất bại"
            . '; TxnRef=' . $txnRef
            . '; ResponseCode=' . ($responseCode !== '' ? $responseCode : 'N/A')
            . '; Message=' . $message;
        $this->markVNPayFailedOrder($order, $failedNote, false);

        if ($isIpn) {
            return $this->buildVNPayIpnResponse('00', 'Confirm Success');
        }

        if ($isCancel) {
            return transfer("Bạn đã hủy giao dịch VNPay.", false, $cartUrl);
        }

        return transfer("Thanh toán VNPay không thành công: " . $message, false, $cartUrl);
    }
    
    protected function resolveCallbackRedirectUrl(string $pathOrUrl): string
    {
        $relativePath = $this->extractRoutePath($pathOrUrl);
        $appBaseUrl = trim((string) env('APP_URL', ''));

        if ($appBaseUrl === '') {
            return $this->ensureAbsoluteUrl($relativePath);
        }

        $parsed = parse_url($appBaseUrl);
        if (!is_array($parsed) || empty($parsed['host'])) {
            return $this->ensureAbsoluteUrl($relativePath);
        }

        $origin = ($parsed['scheme'] ?? 'http') . '://' . $parsed['host'];
        if (!empty($parsed['port'])) {
            $origin .= ':' . $parsed['port'];
        }

        $basePath = trim((string) ($parsed['path'] ?? ''), '/');
        $relativePath = ltrim($relativePath, '/');

        if ($relativePath === '') {
            return rtrim($origin, '/') . ($basePath !== '' ? '/' . $basePath . '/' : '/');
        }

        if ($basePath !== '' && ($relativePath === $basePath || str_starts_with($relativePath, $basePath . '/'))) {
            return rtrim($origin, '/') . '/' . $relativePath;
        }

        if ($basePath !== '') {
            return rtrim($origin, '/') . '/' . $basePath . '/' . $relativePath;
        }

        return rtrim($origin, '/') . '/' . $relativePath;
    }
    protected function buildVNPayIpnResponse(string $code, string $message)
    {
        return response()->json([
            'RspCode' => $code,
            'Message' => $message,
        ]);
    }
    protected function canRetryVNPayOrder(OrdersModel $order): bool
    {
        if (!$this->isVNPayPaymentMethod((int) ($order->order_payment ?? 0))) {
            return false;
        }

        $pendingStatusId = $this->resolvePendingPaymentStatusId();
        if ($pendingStatusId > 0 && (int) ($order->order_status ?? 0) !== $pendingStatusId) {
            return false;
        }

        $paidStatusId = $this->resolveVNPayPaidStatusId();
        if ($paidStatusId > 0 && (int) ($order->order_status ?? 0) === $paidStatusId) {
            return false;
        }

        return true;
    }
    protected function isVNPayPaymentMethod(int $paymentId): bool
    {
        if ($paymentId <= 0) {
            return false;
        }

        $payment = NewsModel::select('id', 'type', 'namevi', 'slugvi', 'descvi')
            ->where('id', $paymentId)
            ->where('type', 'hinh-thuc-thanh-toan')
            ->first();

        if (empty($payment)) {
            return false;
        }

        $keywords = strtolower(trim(implode(' ', [
            (string) ($payment->namevi ?? ''),
            (string) ($payment->slugvi ?? ''),
            (string) ($payment->descvi ?? ''),
        ])));

        return (bool) preg_match('/\bvn\s*pay\b|\bvnpay\b/i', $keywords);
    }
    protected function buildVNPayRedirectUrl($order, Request $request): string
    {
        $txnRef = trim((string) ($order->code ?? ''));
        if ($txnRef === '') {
            $txnRef = 'ORDER' . (int) ($order->id ?? 0);
        }

        $amount = max(0, (float) ($order->total_price ?? 0));
        if ($amount <= 0) {
            throw new \RuntimeException("Giá trị đơn hàng không hợp lệ để thanh toán VNPay.");
        }

        $returnUrl = $this->resolveVNPayReturnUrl();
        if ($returnUrl === '') {
            throw new \RuntimeException("Không tạo được URL return cho VNPay.");
        }

        $tmnCode = trim((string) config('gateways.gateways.VNPay.options.vnp_TmnCode', ''));
        $hashSecret = trim((string) config('gateways.gateways.VNPay.options.vnp_HashSecret', ''));
        $version = trim((string) config('gateways.gateways.VNPay.vnp_Version', '2.1.0'));
        if ($tmnCode === '' || $hashSecret === '') {
            throw new \RuntimeException("Thiếu cấu hình VNPay (TMN Code hoặc Hash Secret).");
        }

        $inputData = [
            'vnp_Version' => $version !== '' ? $version : '2.1.0',
            'vnp_TmnCode' => $tmnCode,
            'vnp_Amount' => (string) ((int) round($amount * 100)),
            'vnp_Command' => 'pay',
            'vnp_CreateDate' => date('YmdHis'),
            'vnp_CurrCode' => 'VND',
            'vnp_ExpireDate' => date('YmdHis', time() + (15 * 60)),
            'vnp_IpAddr' => $this->resolveVNPayClientIp($request),
            'vnp_Locale' => 'vn',
            'vnp_OrderInfo' => 'Thanh toan don hang ' . $txnRef,
            'vnp_OrderType' => 'other',
            'vnp_ReturnUrl' => $returnUrl,
            'vnp_TxnRef' => $txnRef,
        ];

        ksort($inputData);
        $query = [];
        $hashData = [];
        foreach ($inputData as $key => $value) {
            $value = (string) $value;
            if ($value === '') {
                continue;
            }

            $encodedKey = urlencode((string) $key);
            $encodedValue = urlencode($value);
            $query[] = $encodedKey . '=' . $encodedValue;
            $hashData[] = $encodedKey . '=' . $encodedValue;
        }

        $signature = hash_hmac('sha512', implode('&', $hashData), $hashSecret);
        $redirectUrl = rtrim($this->resolveVNPayEndpoint(), '?')
            . '?'
            . implode('&', $query)
            . '&vnp_SecureHash=' . $signature;

        $this->logVNPayDebug([
            'tmnCode' => $tmnCode,
            'txnRef' => $txnRef,
            'returnUrl' => $returnUrl,
            'hashType' => 'sha512',
            'hashSecretLength' => strlen($hashSecret),
            'hashData' => implode('&', $hashData),
            'secureHash' => $signature,
            'redirectUrl' => $redirectUrl
        ]);
        if ($redirectUrl === '') {
            throw new \RuntimeException("URL chuyển hướng VNPay đang trống.");
        }

        return $redirectUrl;
    }
    protected function resolveVNPayEndpoint(): string
    {
        $testMode = config('gateways.gateways.VNPay.options.testMode', true);
        if (is_string($testMode)) {
            $testMode = in_array(strtolower(trim($testMode)), ['1', 'true', 'yes', 'on'], true);
        } else {
            $testMode = (bool) $testMode;
        }

        return $testMode
            ? 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html'
            : 'https://pay.vnpay.vn/vpcpay.html';
    }
    protected function resolveVNPayClientIp(Request $request): string
    {
        $ip = trim((string) ($request->ip() ?? ''));
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return $ip;
        }

        return '127.0.0.1';
    }
    protected function logVNPayDebug(array $context = []): void
    {
        $enabled = env('VNPAY_DEBUG', false);
        if (is_string($enabled)) {
            $enabled = in_array(strtolower(trim($enabled)), ['1', 'true', 'yes', 'on'], true);
        } else {
            $enabled = (bool) $enabled;
        }

        if (!$enabled) {
            return;
        }

        try {
            $filePath = base_path('caches/vnpay_debug.log');
            $line = '[' . date('Y-m-d H:i:s') . '] ' . json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            file_put_contents($filePath, $line . PHP_EOL, FILE_APPEND);
        } catch (\Throwable $e) {
        }
    }
    protected function resolveVNPayReturnUrl(): string
    {
        $configuredReturnUrl = trim((string) env('VNPAY_RETURN_URL', ''));
        if ($configuredReturnUrl !== '') {
            if (preg_match('#^https?://#i', $configuredReturnUrl)) {
                return $configuredReturnUrl;
            }

            return $this->ensureAbsoluteUrl($configuredReturnUrl);
        }

        return $this->ensureAbsoluteUrl($this->extractRoutePath((string) url('vnpay.return')));
    }
    protected function ensureAbsoluteUrl(string $pathOrUrl): string
    {
        $pathOrUrl = trim($pathOrUrl);
        if ($pathOrUrl === '') {
            return '';
        }

        $publicBaseUrl = trim((string) env('VNPAY_PUBLIC_URL', ''));
        if ($publicBaseUrl === '') {
            $publicBaseUrl = (string) env('APP_URL', (string) config('app.asset'));
        }

        $parsed = parse_url($publicBaseUrl);
        $origin = '';
        if (is_array($parsed) && !empty($parsed['host'])) {
            $origin = ($parsed['scheme'] ?? 'https') . '://' . $parsed['host'];
            if (!empty($parsed['port'])) {
                $origin .= ':' . $parsed['port'];
            }
        }

        if ($origin === '') {
            if (preg_match('#^https?://#i', $pathOrUrl)) {
                return $pathOrUrl;
            }
            return rtrim((string) config('app.asset'), '/') . '/' . ltrim($pathOrUrl, '/');
        }

        $relativePath = $this->extractRoutePath($pathOrUrl);
        if ($relativePath === '') {
            return rtrim($origin, '/') . '/';
        }

        return rtrim($origin, '/') . '/' . ltrim($relativePath, '/');
    }
    protected function extractRoutePath(string $pathOrUrl): string
    {
        $pathOrUrl = trim($pathOrUrl);
        if ($pathOrUrl === '') {
            return '';
        }

        if (strpos($pathOrUrl, '//') === 0) {
            $pathOrUrl = 'https:' . $pathOrUrl;
        }

        if (preg_match('#^https?://#i', $pathOrUrl)) {
            $parsed = parse_url($pathOrUrl);
            if (!is_array($parsed)) {
                return '';
            }

            $path = (string) ($parsed['path'] ?? '/');
            $query = isset($parsed['query']) && $parsed['query'] !== '' ? '?' . $parsed['query'] : '';
            return $path . $query;
        }

        return $pathOrUrl;
    }
    protected function validateVNPaySignature(array $payload): bool
    {
        if (empty($payload['vnp_SecureHash'])) {
            return false;
        }

        $dataSignature = array_filter($payload, static function ($parameter) {
            return 0 === strpos((string) $parameter, 'vnp_')
                && 'vnp_SecureHash' !== $parameter
                && 'vnp_SecureHashType' !== $parameter;
        }, ARRAY_FILTER_USE_KEY);

        $hashSecret = (string) config('gateways.gateways.VNPay.options.vnp_HashSecret');
        if ($hashSecret === '') {
            return false;
        }

        try {
            $signature = new VNPaySignature(
                $hashSecret,
                (string) ($payload['vnp_SecureHashType'] ?? 'sha512')
            );
            return $signature->validate($dataSignature, (string) $payload['vnp_SecureHash']);
        } catch (\Throwable $e) {
            return false;
        }
    }
    protected function resolveVNPayResponseMessage(array $payload): string
    {
        $message = trim((string) ($payload['vnp_Message'] ?? ''));
        if ($message !== '') {
            return $message;
        }

        $responseCode = trim((string) ($payload['vnp_ResponseCode'] ?? ''));
        if ($responseCode === '24') {
            return "Giao dịch bị hủy bởi người dùng.";
        }
        if ($responseCode === '00') {
            return "Thanh toán thành công.";
        }

        return "Giao dịch không thành công.";
    }
    protected function appendOrderNote(OrdersModel $order, string $note): void
    {
        $note = trim($note);
        if ($note === '') {
            return;
        }

        $currentNotes = trim((string) ($order->notes ?? ''));
        if ($currentNotes !== '' && str_contains($currentNotes, $note)) {
            return;
        }

        $entry = '[' . date('d/m/Y H:i:s') . '] ' . $note;
        $updatedNotes = $currentNotes === '' ? $entry : ($currentNotes . PHP_EOL . $entry);
        OrdersModel::where('id', (int) $order->id)->update(['notes' => $updatedNotes]);
        $order->notes = $updatedNotes;
    }
    protected function markVNPaySuccessOrder(OrdersModel $order, string $note): void
    {
        $this->appendOrderNote($order, $note);
        $paidStatusId = $this->resolveVNPayPaidStatusId();
        if ($paidStatusId > 0 && (int) ($order->order_status ?? 0) !== $paidStatusId) {
            OrdersModel::where('id', (int) $order->id)->update(['order_status' => $paidStatusId]);
            $order->order_status = $paidStatusId;
        }
    }
    protected function markVNPayFailedOrder(OrdersModel $order, string $note, bool $cancelStatus = false): void
    {
        $this->appendOrderNote($order, $note);
        $this->releaseReservedInventoryForOrder($order);

        if ($cancelStatus) {
            $canceledStatusId = $this->resolveCanceledStatusId();
            if ($canceledStatusId > 0 && (int) ($order->order_status ?? 0) !== $canceledStatusId) {
                OrdersModel::where('id', (int) $order->id)->update(['order_status' => $canceledStatusId]);
                $order->order_status = $canceledStatusId;
            }
        }
    }
    protected function releaseReservedInventoryForOrder(OrdersModel $order): void
    {
        $infoUserRaw = $order->info_user ?? [];
        $infoUser = is_array($infoUserRaw) ? $infoUserRaw : (array) $infoUserRaw;

        if (!$this->hasReservedInventoryFlag($infoUser)) {
            return;
        }

        $this->releaseOrderInventory($order->order_detail ?? []);
        $updatedInfoUser = $this->clearReservedInventoryFlag($infoUser);
        OrdersModel::where('id', (int) $order->id)->update(['info_user' => $updatedInfoUser]);
        $order->info_user = $updatedInfoUser;
    }
    protected function resolveCanceledStatusId(): int
    {
        if (OrderStatusModel::where('id', 5)->exists()) {
            return 5;
        }

        $statuses = OrderStatusModel::select('id', 'namevi')->get();
        foreach ($statuses as $status) {
            $name = mb_strtolower(trim((string) ($status->namevi ?? '')), 'UTF-8');
            if (str_contains($name, 'huy') || str_contains($name, 'cancel')) {
                return (int) ($status->id ?? 0);
            }
        }

        return 0;
    }
    protected function resolvePendingPaymentStatusId(): int
    {
        $statusId = $this->resolveOrderStatusIdByAliases([
            'cho thanh toan',
            'cho tt',
            'pending payment',
            'awaiting payment',
            'pending',
            'unpaid'
        ]);
        if ($statusId > 0) {
            return $statusId;
        }

        if (OrderStatusModel::where('id', 1)->exists()) {
            return 1;
        }

        return $this->resolveDefaultOrderStatusId();
    }
    protected function resolveVNPayPaidStatusId(): int
    {
        $statusId = $this->resolveOrderStatusIdByAliases([
            'da thanh toan',
            'thanh toan thanh cong',
            'paid',
            'payment success',
            'payment completed'
        ]);
        if ($statusId > 0) {
            return $statusId;
        }

        $statusId = $this->resolveOrderStatusIdByAliases([
            'dang xu ly',
            'xu ly',
            'processing',
            'xac nhan',
            'confirmed',
            'moi dat',
            'cho xac nhan',
            'new'
        ]);
        if ($statusId > 0) {
            return $statusId;
        }

        if (OrderStatusModel::where('id', 2)->exists()) {
            return 2;
        }

        if (OrderStatusModel::where('id', 1)->exists()) {
            return 1;
        }

        return $this->resolveDefaultOrderStatusId();
    }
    protected function resolveOrderStatusIdByAliases(array $aliases): int
    {
        $keywords = array_values(array_filter(array_map(function ($alias) {
            return $this->normalizeOrderStatusNameForLookup((string) $alias);
        }, $aliases)));
        if (empty($keywords)) {
            return 0;
        }

        $statuses = OrderStatusModel::select('id', 'namevi')->get();
        foreach ($statuses as $status) {
            $id = (int) ($status->id ?? 0);
            if ($id <= 0) {
                continue;
            }

            $name = $this->normalizeOrderStatusNameForLookup((string) ($status->namevi ?? ''));
            if ($name === '') {
                continue;
            }

            foreach ($keywords as $keyword) {
                if ($keyword !== '' && str_contains($name, $keyword)) {
                    return $id;
                }
            }
        }

        return 0;
    }
    protected function normalizeOrderStatusNameForLookup(string $value = ''): string
    {
        $value = mb_strtolower(trim($value), 'UTF-8');
        if ($value === '') return '';

        if (function_exists('iconv')) {
            $ascii = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
            if (!empty($ascii)) {
                $value = strtolower((string) $ascii);
            }
        }

        $value = preg_replace('/[^a-z0-9]+/', ' ', (string) $value);
        $value = preg_replace('/\s+/', ' ', (string) $value);

        return trim((string) $value);
    }
    protected function resolveDefaultOrderStatusId(): int
    {
        return (int) (OrderStatusModel::query()->orderBy('id', 'asc')->value('id') ?? 0);
    }
    protected function updateCart($request): void
    {
        $rowId = $request->rowId;
        $quantity = max(1, (int) ($request->quantity ?? 1));
        $item = Cart::get($rowId);
        if (empty($item)) {
            response()->json(['error' => 1, 'message' => "Không tìm thấy sản phẩm trong giỏ hàng."]);
            return;
        }

        $currentQty = max(1, (int) ($item->qty ?? 1));
        $stockState = $this->resolveCartItemStockState($item, $quantity);
        if (empty($stockState['in_stock'])) {
            $message = trim((string) ($stockState['message'] ?? ''));
            if ($message === '') {
                $message = "Số lượng đã vượt quá tồn kho hiện tại.";
            }
            response()->json([
                'error' => 1,
                'message' => $message,
                'rowId' => (string) $rowId,
                'requestedQty' => $quantity,
                'currentQty' => $currentQty,
                'availableQty' => array_key_exists('available_qty', $stockState) && $stockState['available_qty'] !== null
                    ? (int) $stockState['available_qty']
                    : null,
                'cartHasOutOfStock' => !empty($this->collectCartStockStateByRow()['has_out_of_stock']),
                'cartStockByRow' => $this->collectCartStockStateByRow()['rows'] ?? [],
            ]);
            return;
        }

        Cart::update($rowId, $quantity);
        $updatedItem = Cart::get($rowId);
        $regular_price = Func::formatMoney(($updatedItem->options->itemProduct->regular_price ?? 0) * ($updatedItem->qty ?? 0));
        $sale_price = Func::formatMoney(($updatedItem->options->itemProduct->sale_price ?? 0) * ($updatedItem->qty ?? 0));
        $temp = Cart::subtotalFloat();
        $tempText = Func::formatMoney($temp);
        $total = $temp;
        $totalText = Func::formatMoney($total);
        $cartStockState = $this->collectCartStockStateByRow();
        response()->json([
            'max' => Cart::count(),
            'regularPrice' => $regular_price,
            'salePrice' => $sale_price,
            'tempText' => $tempText,
            'totalText' => $totalText,
            'rowId' => (string) $rowId,
            'quantity' => (int) ($updatedItem->qty ?? 1),
            'cartHasOutOfStock' => !empty($cartStockState['has_out_of_stock']),
            'cartStockByRow' => $cartStockState['rows'] ?? [],
        ]);
    }
    protected function syncCartStock(): void
    {
        $cartStockState = $this->collectCartStockStateByRow();
        response()->json([
            'success' => true,
            'cartHasOutOfStock' => !empty($cartStockState['has_out_of_stock']),
            'cartStockByRow' => $cartStockState['rows'] ?? [],
            'timestamp' => time(),
        ]);
    }
    protected function deleteCart($request): void
    {
        $rowId = $request->input('rowId');
        Cart::remove($rowId);
        $tempText = Func::formatMoney(Cart::subtotalFloat());
        $total = Cart::subtotalFloat();
        $totalText = Func::formatMoney($total);
        response()->json(['max' => Cart::count(), 'tempText' => $tempText, 'totalText' => $totalText]);
    }
    protected function addCart($request): void
    {
        $idProduct = $request->id;
        $qty = (!empty($request->quantity)) ? (int)$request->quantity : 1;
        $properties = json_decode($request->properties, true) ?? [];
        $properties = array_values(array_filter(array_map('intval', (array)$properties)));
        $getProperties = collect();
        $itemProduct = ProductModel::find($idProduct);
        if (empty($itemProduct)) {
            response()->json(['error' => 1, 'message' => "Sản phẩm không tồn tại."]);
            return;
        }
        $productCode = (string) ($itemProduct->code ?? '');
        $variantMaxQty = 0;

        if (empty($properties)) {
            $variantRows = ProductPropertiesModel::select('id_properties', 'quantity', 'status')
                ->where('id_parent', $idProduct)
                ->get();

            if ($variantRows->isNotEmpty()) {
                $inStockRows = $variantRows->filter(function ($row) {
                    $qtyRow = (int) ($row->quantity ?? 0);
                    $statusRow = strtolower(trim((string) ($row->status ?? 'active')));
                    return $qtyRow > 0 && $statusRow !== 'inactive';
                })->values();

                if ($inStockRows->isEmpty()) {
                    response()->json(['error' => 1, 'message' => "Sản phẩm đang hết hàng."]);
                    return;
                }

                if ($variantRows->count() > 1) {
                    response()->json(['error' => 1, 'message' => "Vui lòng chọn phân loại sản phẩm trước khi thêm vào giỏ."]);
                    return;
                }

                $singlePropertyIds = array_values(array_filter(array_map('intval', explode(',', (string) ($inStockRows->first()->id_properties ?? '')))));
                if (!empty($singlePropertyIds)) {
                    $properties = $singlePropertyIds;
                }
            }
        }

        if (!empty($properties)) {
            $getProperties = PropertiesModel::whereIn('id', $properties)->with('getListProperties')->get();
            $query = \LARAVEL\Models\ProductPropertiesModel::select('regular_price', 'sale_price', 'quantity', 'status', 'code');
            foreach (array_values($properties) as $v) $query->whereRaw("FIND_IN_SET(?, id_properties)", [$v]);
            $query->where('id_parent', $idProduct);
            $query->whereRaw("(LENGTH(id_properties) - LENGTH(REPLACE(id_properties, ',', '')) + 1) = ?", [count($properties)]);
            $getPrice = $query->first();
            if (!empty($getPrice)) {
                $variantMaxQty = (int)($getPrice->quantity ?? 0);
                $variantStatus = strtolower(trim((string)($getPrice->status ?? 'active')));
                if ($variantStatus === 'inactive' || $variantMaxQty <= 0) {
                    response()->json(['error' => 1, 'message' => "Phiên bản đã chọn đang hết hàng."]);
                    return;
                }
                $itemProduct->regular_price = $getPrice->regular_price;
                $itemProduct->sale_price = $getPrice->sale_price;
                if (!empty($getPrice->code)) {
                    $productCode = (string) $getPrice->code;
                }
            }
        }
        $code = md5(vsprintf('%s.%s.%s', [$idProduct, $itemProduct->namevi, json_encode(array_values($properties))]));
        $itemProduct->code = $productCode;
        $data = [
            'id' => $itemProduct->id,
            'name' => $itemProduct->namevi,
            'price' => (!empty($itemProduct->sale_price)) ? $itemProduct->sale_price : $itemProduct->regular_price,
            'qty' => $qty,
            'weight' => 0,
            'options' => [
                'properties' => $getProperties ?? collect(),
                'code' => $code,
                'productCode' => $productCode,
                'variantPhoto' => $this->resolveVariantPhoto((int)$itemProduct->id, $properties),
                'itemProduct' => $itemProduct
            ]
        ];
        if ($this->findProductInCart($code)->isNotEmpty()) {
            $rowId = $this->findProductInCart($code)->first()->rowId;
            $qtyOld = $this->findProductInCart($code)->first()->qty;
            if (!empty($variantMaxQty) && ($qtyOld + $qty) > $variantMaxQty) {
                response()->json(['error' => 1, 'message' => "Số lượng vượt quá tồn kho. Còn lại: " . $variantMaxQty]);
                return;
            }
            Cart::update($rowId, ($qtyOld + $qty));
        } else {
            if (!empty($variantMaxQty) && $qty > $variantMaxQty) {
                response()->json(['error' => 1, 'message' => "Số lượng vượt quá tồn kho. Còn lại: " . $variantMaxQty]);
                return;
            }
            Cart::add($data);
        }
        $this->safeTrackEvent(EventSchemaValidator::EVENT_ADD_TO_CART, [
            'product_id' => (string) ((int) $idProduct),
            'quantity' => (int) $qty,
            'price' => (float) ($data['price'] ?? 0),
        ], $request);
        response()->json(['max' => Cart::count()]);
    }
    protected function findProductInCart($id): \Illuminate\Support\Collection
    {
        return Cart::search(function ($cartItem) use ($id) {
            return $cartItem->options->code === (string)$id;
        });
    }
    protected function collectCartStockStateByRow(): array
    {
        $rows = [];
        $hasOutOfStock = false;
        foreach (Cart::content() as $cartItem) {
            $rowId = (string) ($cartItem->rowId ?? '');
            if ($rowId === '') {
                continue;
            }

            $stockState = $this->resolveCartItemStockState($cartItem, (int) ($cartItem->qty ?? 1));
            $rows[$rowId] = $stockState;
            if (empty($stockState['in_stock'])) {
                $hasOutOfStock = true;
            }
        }

        return [
            'rows' => $rows,
            'has_out_of_stock' => $hasOutOfStock,
        ];
    }
    protected function validateCartStockLines(): array
    {
        $stockScan = $this->collectCartStockStateByRow();
        $errors = [];
        foreach (($stockScan['rows'] ?? []) as $rowId => $stockState) {
            if (!empty($stockState['in_stock'])) {
                continue;
            }

            $message = trim((string) ($stockState['message'] ?? ''));
            if ($message === '') {
                $message = "Tồn kho đã thay đổi. Vui lòng kiểm tra lại giỏ hàng.";
            }
            $errors[] = [
                'row_id' => (string) $rowId,
                'message' => $message,
                'available_qty' => array_key_exists('available_qty', $stockState) && $stockState['available_qty'] !== null
                    ? (int) $stockState['available_qty']
                    : null,
            ];
        }

        return [
            'is_valid' => empty($errors),
            'errors' => $errors,
            'first_error' => $errors[0] ?? null,
            'stock_by_row' => $stockScan['rows'] ?? [],
            'has_out_of_stock' => !empty($stockScan['has_out_of_stock']),
        ];
    }
    protected function resolveCartItemStockState($cartItem, ?int $requestedQty = null): array
    {
        $requestedQty = max(1, (int) ($requestedQty ?? ($cartItem->qty ?? 1)));
        $default = [
            'in_stock' => true,
            'available_qty' => null,
            'message' => '',
        ];

        if (empty($cartItem)) {
            return $default;
        }

        $itemProduct = $cartItem->options->itemProduct ?? null;
        $productId = (int) ($itemProduct->id ?? $cartItem->id ?? 0);
        if ($productId <= 0) {
            return [
                'in_stock' => false,
                'available_qty' => 0,
                'message' => "Sản phẩm không còn tồn tại.",
            ];
        }

        $propertyIds = $this->extractPropertyIds($cartItem->options->properties ?? []);
        if (!empty($propertyIds)) {
            $variantRow = $this->resolveVariantRowByProperties($productId, $propertyIds);
            if (empty($variantRow?->id)) {
                return [
                    'in_stock' => false,
                    'available_qty' => 0,
                    'message' => "Phiên bản sản phẩm không còn tồn tại.",
                ];
            }

            $availableQty = max(0, (int) ($variantRow->quantity ?? 0));
            $variantStatus = strtolower(trim((string) ($variantRow->status ?? 'active')));
            if ($variantStatus === 'inactive' || $availableQty <= 0) {
                return [
                    'in_stock' => false,
                    'available_qty' => $availableQty,
                    'message' => "Phiên bản đã chọn đang hết hàng.",
                ];
            }

            if ($requestedQty > $availableQty) {
                return [
                    'in_stock' => false,
                    'available_qty' => $availableQty,
                    'message' => "Số lượng vượt quá tồn kho. Còn lại: " . $availableQty,
                ];
            }

            return [
                'in_stock' => true,
                'available_qty' => $availableQty,
                'message' => '',
            ];
        }

        $productStock = ProductModel::select('quantity', 'status')->find($productId);
        if (empty($productStock)) {
            return [
                'in_stock' => false,
                'available_qty' => 0,
                'message' => "Sản phẩm không còn tồn tại.",
            ];
        }

        $rawQty = $productStock->quantity ?? null;
        if ($rawQty === null || $rawQty === '') {
            return $default;
        }

        $availableQty = max(0, (int) $rawQty);
        $productStatus = strtolower(trim((string) ($productStock->status ?? 'active')));
        if ($productStatus === 'inactive' || $availableQty <= 0) {
            return [
                'in_stock' => false,
                'available_qty' => $availableQty,
                'message' => "Sản phẩm đang hết hàng.",
            ];
        }

        if ($requestedQty > $availableQty) {
            return [
                'in_stock' => false,
                'available_qty' => $availableQty,
                'message' => "Số lượng vượt quá tồn kho. Còn lại: " . $availableQty,
            ];
        }

        return [
            'in_stock' => true,
            'available_qty' => $availableQty,
            'message' => '',
        ];
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
            error_log('[CartController][track] ' . $e->getMessage());
        }
    }

    private function trackPurchaseFromOrder($order, bool $paid, ?Request $request = null): void
    {
        $items = [];
        $totalAmount = 0.0;
        $orderDetail = $order->order_detail ?? [];

        if ($orderDetail instanceof \Illuminate\Support\Collection) {
            $orderDetail = $orderDetail->all();
        }
        if (is_object($orderDetail)) {
            $orderDetail = (array) $orderDetail;
        }
        if (!is_array($orderDetail)) {
            $decoded = json_decode((string) $orderDetail, true);
            $orderDetail = is_array($decoded) ? $decoded : [];
        }

        foreach ($orderDetail as $line) {
            if (is_object($line)) {
                $line = (array) $line;
            }
            if (!is_array($line)) {
                continue;
            }
            $productId = (int) ($line['id'] ?? $line['product_id'] ?? 0);
            $qty = (int) ($line['qty'] ?? $line['quantity'] ?? 0);
            $price = (float) ($line['price'] ?? 0);
            if ($productId <= 0 || $qty <= 0) {
                continue;
            }
            $items[] = [
                'product_id' => (string) $productId,
                'quantity' => $qty,
                'price' => $price,
            ];
            $totalAmount += $price * $qty;
        }

        if (empty($items)) {
            return;
        }

        $this->safeTrackEvent(EventSchemaValidator::EVENT_PURCHASE, [
            'order_id' => (string) (($order->code ?? $order->id ?? '') ?: ''),
            'items' => $items,
            'total_amount' => (float) (($order->total_price ?? 0) > 0 ? $order->total_price : $totalAmount),
            'paid' => $paid,
        ], $request);
    }
}
