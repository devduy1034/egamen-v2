@php
    $selectedItems = is_array($selectedOrder->order_detail ?? null) ? array_values($selectedOrder->order_detail) : [];
    $extraItems = count($selectedItems) > 1 ? array_slice($selectedItems, 1) : [];
    $deliveredOrderStatusId = (int) ($deliveredOrderStatusId ?? 0);
    $isDeliveredOrder = $deliveredOrderStatusId > 0 && (int) ($selectedOrder->order_status ?? 0) === $deliveredOrderStatusId;
    $totalProductQty = collect($selectedItems)->reduce(function ($carry, $item) {
        return $carry + max(1, (int) ($item['qty'] ?? 1));
    }, 0);
    $tempPrice = max(0, (float) ($selectedOrder->temp_price ?? 0));
    $shipPrice = max(0, (float) ($selectedOrder->ship_price ?? 0));
    $totalPrice = max(0, (float) ($selectedOrder->total_price ?? 0));
    $voucherDiscount = isset($selectedOrder->voucher_discount)
        ? max(0, (float) ($selectedOrder->voucher_discount ?? 0))
        : max(0, $tempPrice + $shipPrice - $totalPrice);
    $requirementsText = (string) ($selectedOrder->requirements ?? '');
    $voucherCode = '';
    if (preg_match('/Voucher:\s*([A-Z0-9\-_]+)/iu', $requirementsText, $voucherMatch)) {
        $voucherCode = strtoupper(trim((string) ($voucherMatch[1] ?? '')));
    }
@endphp

@foreach ($extraItems as $line)
    @php
        $linePhoto = (string) (data_get($line, 'options.variantPhoto') ?? data_get($line, 'options.itemProduct.photo') ?? '');
        $linePhotoUrl = !empty($linePhoto) ? assets_photo('product', '100x100x1', $linePhoto, 'thumbs') : '';
        $lineProperties = data_get($line, 'options.properties', []);
        if ($lineProperties instanceof \Illuminate\Support\Collection) {
            $lineProperties = $lineProperties->toArray();
        }
        if (!is_array($lineProperties)) {
            $lineProperties = [];
        }
        $linePropertyNames = collect($lineProperties)->map(function ($property) {
            return trim((string) (data_get($property, 'namevi') ?? data_get($property, 'name') ?? ''));
        })->filter()->values()->all();
        $lineProductCode = '';
        $linePropertyIds = collect($lineProperties)
            ->map(function ($property) {
                return (int) (data_get($property, 'id') ?? 0);
            })
            ->filter()
            ->values()
            ->all();
        $lineProductId = (int) (data_get($line, 'options.itemProduct.id') ?? 0);
        $lineProductType = trim((string) (data_get($line, 'options.itemProduct.type') ?? 'san-pham'));
        if ($lineProductType === '') {
            $lineProductType = 'san-pham';
        }
        $lineProductName = trim((string) ($line['name'] ?? 'Sản phẩm'));
        if ($lineProductId > 0 && !empty($linePropertyIds)) {
            $lineVariantCodeQuery = \LARAVEL\Models\ProductPropertiesModel::select('code')
                ->where('id_parent', $lineProductId);
            foreach ($linePropertyIds as $propertyId) {
                $lineVariantCodeQuery->whereRaw('FIND_IN_SET(?, id_properties)', [$propertyId]);
            }
            $lineVariantCodeQuery->whereRaw(
                "(LENGTH(id_properties) - LENGTH(REPLACE(id_properties, ',', '')) + 1) = ?",
                [count($linePropertyIds)],
            );
            $lineVariantCodeRow = $lineVariantCodeQuery->first();
            if (!empty($lineVariantCodeRow?->code)) {
                $lineProductCode = trim((string) $lineVariantCodeRow->code);
            }
        }
        if ($lineProductCode === '') {
            $lineProductCode = trim((string) (data_get($line, 'options.productCode') ?? ''));
        }
        if ($lineProductCode === '') {
            $lineProductCode = trim((string) (data_get($line, 'options.itemProduct.code') ?? ''));
        }
        if ($lineProductCode === '' && $lineProductId > 0) {
            $lineProductCode = trim(
                (string) (\LARAVEL\Models\ProductModel::where('id', $lineProductId)->value('code') ?? ''),
            );
        }
        $linePropsAndCode = [];
        if (!empty($linePropertyNames)) {
            $linePropsAndCode[] = implode(', ', $linePropertyNames);
        }
        if (!empty($lineProductCode)) {
            $linePropsAndCode[] = __('web.code') . ': ' . $lineProductCode;
        }
    @endphp
    <div class="account-order-item">
        <div class="account-order-thumb">
            @if (!empty($linePhotoUrl))
                <img src="{{ $linePhotoUrl }}" alt="{{ $line['name'] ?? 'Sản phẩm' }}" loading="lazy" onerror="this.remove()">
            @endif
        </div>
        <div>
            <p class="mb-0 fw-bold">{{ $line['name'] ?? 'Sản phẩm' }}</p>
            @if (!empty($linePropsAndCode))
                <p class="account-order-props">{{ implode(' | ', $linePropsAndCode) }}</p>
            @endif
            <p>SL: {{ $line['qty'] ?? 1 }} - Giá: {{ \Func::formatMoney((float) ($line['price'] ?? 0)) }}</p>
            @if ($isDeliveredOrder && $lineProductId > 0)
                <button type="button"
                    class="btn account-btn account-btn--outline account-order-review-trigger js-order-review-trigger"
                    data-product-id="{{ $lineProductId }}" data-product-type="{{ $lineProductType }}"
                    data-product-name="{{ $lineProductName }}" data-product-photo="{{ $linePhotoUrl }}">
                    Đánh giá sản phẩm
                </button>
            @endif
        </div>
    </div>
@endforeach

<div class="account-order-total">
    <p><span>Tổng sản phẩm:</span> <strong>{{ $totalProductQty }} sản phẩm</strong></p>
    @if (!empty($voucherCode))
        <p><span>Mã voucher:</span> <strong>{{ $voucherCode }}</strong></p>
    @endif
    <p><span>Tạm tính:</span> <strong>{{ \Func::formatMoney($tempPrice) }}</strong></p>
    <p><span>Phí ship:</span> <strong>{{ \Func::formatMoney($shipPrice) }}</strong></p>
    <p class="is-discount">
        <span>Giảm voucher:</span>
        <strong>{{ $voucherDiscount > 0 ? '-' . \Func::formatMoney($voucherDiscount) : '0đ' }}</strong>
    </p>
    <p><span>Tổng tiền:</span> <strong>{{ \Func::formatMoney($totalPrice) }}</strong></p>
</div>
