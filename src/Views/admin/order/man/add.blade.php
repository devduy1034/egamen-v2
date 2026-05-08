@extends('layout')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y container-fluid">
        <div class="app-ecommerce">
            <form class="validation-form" novalidate method="post"
                action="{{ url('admin', ['com' => $com, 'act' => 'save', 'type' => $type], ['id' => $item['id'] ?? 0, 'page' => $page]) }}"
                enctype="multipart/form-data">
                @component('component.buttonAdd')
                @endcomponent
                {!! Flash::getMessages('admin') !!}
                <div class="row-card">
                    <div class="row">

                        <div class="col-12 col-lg-8">
                            <div class="card card-primary card-outline text-sm mb-4">
                                <div class="card-header">
                                    <h3 class="card-title">Chi tiết đơn hàng</h3>
                                </div>

                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th class="align-middle text-center" width="10%">STT</th>
                                            <th class="align-middle">Hình ảnh</th>
                                            <th class="align-middle" style="width:30%">Tên sản phẩm</th>
                                            <th class="align-middle text-center">Đơn giá</th>
                                            <th class="align-middle text-right">Số lượng</th>
                                            <th class="align-middle text-right">Tạm tính</th>
                                        </tr>
                                    </thead>

                                    @if (empty($infoOrder))
                                        <tbody>
                                            <tr>
                                                <td colspan="100" class="text-center">Không có dữ liệu</td>
                                            </tr>
                                        </tbody>
                                    @else
                                        <tbody>
                                            @php $num = 0 ; @endphp
                                            @foreach ($infoOrder as $k => $v)
                                                @php
                                                    $num = $num + 1;
                                                    $options = $v['options'];

                                                    $itemProduct = $v['options']['itemProduct'];
                                                    $orderPhoto =
                                                        $options['variantPhoto'] ?? ($itemProduct['photo'] ?? '');
                                                    $productCode = '';

                                                    if (!empty($itemProduct['id']) && !empty($options['properties'])) {
                                                        $propertyIds = [];
                                                        foreach ((array) $options['properties'] as $property) {
                                                            $propertyId = (int) ($property['id'] ?? 0);
                                                            if ($propertyId > 0) {
                                                                $propertyIds[] = $propertyId;
                                                            }
                                                        }
                                                        $propertyIds = array_values(array_unique($propertyIds));

                                                        if (!empty($propertyIds)) {
                                                            $variantCodeQuery = \LARAVEL\Models\ProductPropertiesModel::select(
                                                                'code',
                                                            )->where('id_parent', (int) $itemProduct['id']);
                                                            foreach ($propertyIds as $propertyId) {
                                                                $variantCodeQuery->whereRaw(
                                                                    'FIND_IN_SET(?, id_properties)',
                                                                    [$propertyId],
                                                                );
                                                            }
                                                            $variantCodeQuery->whereRaw(
                                                                "(LENGTH(id_properties) - LENGTH(REPLACE(id_properties, ',', '')) + 1) = ?",
                                                                [count($propertyIds)],
                                                            );
                                                            $variantCodeRow = $variantCodeQuery->first();
                                                            if (!empty($variantCodeRow?->code)) {
                                                                $productCode = trim((string) $variantCodeRow->code);
                                                            }
                                                        }
                                                    }

                                                    if ($productCode === '') {
                                                        $productCode = trim((string) ($options['productCode'] ?? ''));
                                                    }

                                                    if ($productCode === '') {
                                                        $productCode = trim((string) ($itemProduct['code'] ?? ''));
                                                    }
                                                @endphp
                                                @php
                                                    $orderPhotoSrc = !empty($orderPhoto)
                                                        ? assets_photo('product', '100x100x1', $orderPhoto, 'thumbs')
                                                        : assets('assets/images/noimage.png');
                                                @endphp
                                                <tr>
                                                    <td class="align-middle text-center">{{ $num }}</td>
                                                    <td class="align-middle">
                                                        <a title="{{ $v['name'] }}">
                                                            <img class="img-preview"
                                                                onerror="this.src='{{ assets('assets/images/noimage.png') }}';"
                                                                src="{{ $orderPhotoSrc }}" loading="lazy" decoding="async"
                                                                alt="{{ $v['name'] }}" title="{{ $v['name'] }}" />
                                                        </a>
                                                    </td>
                                                    <td class="align-middle">
                                                        <p class="order-product-name">{{ $v['name'] }}</p>
                                                        @if (!empty($productCode))
                                                            <p class="order-product-code">Mã SP: <span>{{ $productCode }}</span></p>
                                                        @endif
                                                        @if (!empty($options['properties']))
                                                            <div class="order-product-properties">
                                                                @foreach ($options['properties'] as $kp => $vp)
                                                                    <span
                                                                        class="order-product-property">{{ $vp['namevi'] }}</span>
                                                                @endforeach
                                                            </div>
                                                        @endif
                                                    </td>
                                                    <td class="align-middle text-center">
                                                        <div class="price-cart-detail">
                                                            @if ($itemProduct['sale_price'])
                                                                <span
                                                                    class="price-new-cart-detail">{{ Func::formatMoney($itemProduct['sale_price']) }}</span>
                                                            @else
                                                                <span
                                                                    class="price-new-cart-detail">{{ Func::formatMoney($itemProduct['regular_price']) }}</span>
                                                            @endif
                                                        </div>
                                                    </td>
                                                    <td class="align-middle text-right">{{ $v['qty'] }}</td>
                                                    <td class="align-middle text-right">
                                                        <div class="price-cart-detail">
                                                            @if ($itemProduct['sale_price'])
                                                                <span
                                                                    class="price-new-cart-detail">{{ Func::formatMoney($itemProduct['sale_price'] * $v['qty']) }}</span>
                                                            @else
                                                                <span
                                                                    class="price-new-cart-detail">{{ Func::formatMoney($itemProduct['regular_price'] * $v['qty']) }}</span>
                                                            @endif
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                            @php
                                                $tempPriceOrder = (float) ($item['temp_price'] ?? 0);
                                                $shipPriceOrder = (float) ($item['ship_price'] ?? 0);
                                                $totalPriceOrder = (float) ($item['total_price'] ?? 0);
                                                $voucherDiscountOrder = isset($item['voucher_discount'])
                                                    ? max(0, (float) ($item['voucher_discount'] ?? 0))
                                                    : max(0, $tempPriceOrder + $shipPriceOrder - $totalPriceOrder);
                                            @endphp
                                            @if (!empty($configMan->ship))
                                                <tr>
                                                    <td colspan="5" class="title-money-cart-detail">Tạm tính:</td>
                                                    <td colspan="1" class="cast-money-cart-detail text-right">
                                                        {{ Func::formatMoney($item['temp_price']) }}
                                                    </td>
                                                </tr>
                                            @endif
                                            @if (!empty($configMan->ship))
                                                <tr>
                                                    <td colspan="5" class="title-money-cart-detail">Phí vận chuyển:</td>
                                                    <td colspan="1" class="cast-money-cart-detail text-right">
                                                        @if ($item['ship_price'])
                                                            {{ Func::formatMoney($item['ship_price']) }}
                                                        @else
                                                            0d
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endif
                                            <tr>
                                                <td colspan="5" class="title-money-cart-detail">Giảm voucher:</td>
                                                <td colspan="1" class="cast-money-cart-detail text-right">
                                                    @if ($voucherDiscountOrder > 0)
                                                        -{{ Func::formatMoney($voucherDiscountOrder) }}
                                                    @else
                                                        0&#273;
                                                    @endif
                                                </td>
                                            </tr>
                                            <tr>
                                                <td colspan="5" class="title-money-cart-detail text-right">Tổng giá trị
                                                    đơn hàng:
                                                </td>
                                                <td colspan="1" class="cast-money-cart-detail text-right">
                                                    <strong
                                                        class="text-danger">{{ Func::formatMoney($item['total_price']) }}</strong>
                                                </td>
                                            </tr>
                                        </tbody>
                                    @endif
                                </table>

                            </div>

                            <div class="card card-primary card-outline text-sm mb-4">
                                <div class="card-header">
                                    <h3 class="card-title">Lịch sử đơn hàng</h3>
                                </div>
                                <div class="card-body">
                                    <div class="timeline-container_new">
                                        <div class="timeline-new__wrapper__content--body">
                                            @foreach ($history ?? [] as $value)
                                                @php
                                                    $historyNotesRaw = trim((string) ($value['notes'] ?? ''));
                                                    $historyActorName = '';
                                                    $historyNotes = $historyNotesRaw;

                                                    if (
                                                        $historyNotesRaw !== '' &&
                                                        preg_match('/\[__admin__:(.*?)\]\s*$/', $historyNotesRaw, $actorMatch)
                                                    ) {
                                                        $decodedActor = base64_decode((string) ($actorMatch[1] ?? ''), true);
                                                        if ($decodedActor !== false) {
                                                            $historyActorName = trim((string) $decodedActor);
                                                            $historyNotes = trim((string) preg_replace('/\s*\[__admin__:.*?\]\s*$/', '', $historyNotesRaw));
                                                        }
                                                    }
                                                @endphp
                                                <div class="timeline-container_new--position">
                                                    <div class="timeline-event-contentnew__icon">
                                                        <div class="icon icon-login"><i
                                                                class="fa-regular fa-calendar-days"></i>
                                                        </div>
                                                    </div>
                                                    <div class="timeline-item-new--border--padding">
                                                        <div class="timeline-new__infomation">
                                                            <div>
                                                                <span class="timeline-new__infomation__name"></span>
                                                                <span
                                                                    class="timeline-new__infomation__time text-primary">{{ $value['updated_at'] }}</span>
                                                            </div>

                                                            <div class="timeline-new__infomation__message">
                                                                <div class="hs-usname">
                                                                    + Cập nhật trạng thái thành <span
                                                                        class="text-success">{{ Func::showName('order_status', $value['order_status'], 'namevi') }}</span>
                                                                    @if ($historyActorName !== '')
                                                                        bởi <strong class="text-primary">{{ $historyActorName }}</strong>
                                                                    @endif
                                                                </div>
                                                                @if (!empty($historyNotes))
                                                                    <div class="hs-usname">
                                                                        + Cập nhật ghi chú đơn hàng thành <span
                                                                            class="text-success">{{ $historyNotes }}</span>
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                            <div class="timeline-container_new--position">
                                                <div class="timeline-event-contentnew__icon">
                                                    <div class="icon icon-login"><i class="fa-regular fa-calendar-days"></i>
                                                    </div>
                                                </div>
                                                <div class="timeline-item-new--border--padding">
                                                    <div class="timeline-new__infomation">
                                                        <div>
                                                            <span class="timeline-new__infomation__name"></span>
                                                            <span
                                                                class="timeline-new__infomation__time text-primary">{{ $item['updated_at'] }}</span>
                                                        </div>
                                                        <div class="timeline-new__infomation__message">
                                                            <div class="hs-usname">
                                                                + Đơn hàng được tạo bởi <strong
                                                                    class="text-danger">{{ @$infoUser['fullname'] }}</strong>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-lg-4">
                            <div class="card card-primary card-outline text-sm mb-4">
                                <div class="card-header">
                                    <h3 class="card-title">Thông tin đơn hàng</h3>
                                </div>
                                <div class="card-body">
                                    <div class="form-group">
                                        <p class="form-label">Ngày đặt:
                                            <span>{{ @$item['created_at'] }}</span>
                                        </p>
                                    </div>
                                    <div class="form-group">
                                        <p class="form-label">Mã đơn hàng: <span
                                                class="text-primary">{{ @$item['code'] }}</span></p>
                                    </div>

                                    <div class="form-group">
                                        <p class="form-label">Họ tên: <span
                                                class="text-uppercase text-success">{{ @$infoUser['fullname'] }}</span>
                                        </p>
                                    </div>
                                    <div class="form-group">
                                        <p class="form-label">Điện thoại: <span>{{ @$infoUser['phone'] }}</span></p>
                                    </div>
                                    <div class="form-group">
                                        <p class="form-label">Email: <span>{{ @$infoUser['email'] }}</span></p>
                                    </div>
                                    <div class="form-group">
                                        <p class="form-label">Địa chỉ: <span>{{ $infoUser['full_address'] ?? ($infoUser['address'] ?? '') }}</span></p>
                                    </div>
                                    <div class="form-group">
                                        <p class="form-label">Nội dung: <span
                                                style="line-height:2.4;display:block">{!! nl2br(e((string) (@$item['requirements'] ?? ''))) !!}</span></p>
                                    </div>
                                    @if (!empty($configMan->ship))
                                        <div class="form-group">
                                            <p class="form-label">Phí vận chuyển:
                                                @if (!empty($item['ship_price']) && $item['ship_price'] > 0)
                                                    {{ Func::formatMoney($item['ship_price']) }}
                                                @else
                                                    0d
                                                @endif
                                            </p>

                                        </div>
                                    @endif
                                    <div class="form-group">
                                        <p class="form-label">Hình thức thanh toán: <span
                                                class="text-primary">{{ Func::showName('news', $item['order_payment'], 'namevi') }}</span>
                                        </p>
                                    </div>

                                    <div class="form-group">
                                        <label class="form-label">Ghi chú:</label>
                                        <textarea class="form-control text-sm" name="data[notes]" id="notes" rows="5" placeholder="Ghi chú"><?= @$item['notes'] ?></textarea>
                                    </div>

                                </div>
                            </div>

                            <div class="card card-primary card-outline text-sm mb-4">
                                <div class="card-header">
                                    <h3 class="card-title">Tình trạng đơn hàng</h3>
                                </div>
                                <div class="card-body">
                                    <div class="form-group">
                                        <label for="order_status" class="mr-2">Tình trạng:</label>
                                        @php
                                            $currentOrderStatusId = (int) ($item['order_status'] ?? 0);
                                            $allowedOrderStatusIds = array_map(
                                                'intval',
                                                (array) ($allowedOrderStatusIds ?? []),
                                            );
                                        @endphp
                                        <select id="order_status" name="data[order_status]"
                                            class="select2 form-select form-select-lg">
                                            <option value="0">Chọn tình trạng</option>
                                            @foreach ($orderStatuses ?? collect() as $statusOption)
                                                @php
                                                    $statusId = (int) ($statusOption['id'] ?? $statusOption->id ?? 0);
                                                    $statusName = (string) ($statusOption['namevi'] ?? $statusOption->namevi ?? '');
                                                    $isSelected = $statusId === $currentOrderStatusId;
                                                    $isAllowed = empty($allowedOrderStatusIds)
                                                        ? true
                                                        : in_array($statusId, $allowedOrderStatusIds, true);
                                                @endphp
                                                @if ($statusId > 0 && ($isAllowed || $isSelected))
                                                    <option value="{{ $statusId }}" {{ $isSelected ? 'selected' : '' }}>
                                                        {{ $statusName }}
                                                    </option>
                                                @endif
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>


                    <input type="hidden" name="id"
                        value="{{ !empty($item['id']) && $item['id'] > 0 ? $item['id'] : '' }}">
                    <input name="csrf_token" type="hidden" value="{{ csrf_token() }}">

                    @component('component.buttonAdd')
                    @endcomponent

                </div>
            </form>
        </div>
    </div>
@endsection

@pushonce('styles')
    <style>
        .order-product-name {
            margin: 0 0 6px;
            font-size: 14px;
            font-weight: 600;
            color: #111827;
            line-height: 1.4;
        }

        .order-product-properties {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }

        .order-product-code {
            margin: 0 0 8px;
            color: #6b7280;
            font-size: 12px;
            line-height: 1.4;
        }

        .order-product-code span {
            color: #0f172a;
            font-weight: 600;
        }

        .order-product-property {
            display: inline-flex;
            align-items: center;
            border: 1px solid #bfdbfe;
            border-radius: 999px;
            background: #eff6ff;
            color: #1d4ed8;
            font-size: 12px;
            line-height: 1.2;
            padding: 3px 9px;
        }
    </style>
@endpushonce
