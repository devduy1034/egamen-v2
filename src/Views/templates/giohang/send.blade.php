@php
    $orderCode = (string) ($params['code'] ?? '');
    $orderDate = (string) ($params['created_at'] ?? '');
    $infoUser = (array) ($params['info_user'] ?? []);
    $orderDetails = (array) ($params['order_detail'] ?? []);
    $requirements = trim((string) ($params['requirements'] ?? ''));
    $paymentName = Func::showName('news', (int) ($params['order_payment'] ?? 0), 'namevi');

    $subtotal = max(0, (float) ($params['temp_price'] ?? 0));
    $shipPrice = max(0, (float) ($params['ship_price'] ?? 0));
    $totalPrice = max(0, (float) ($params['total_price'] ?? 0));
    $voucherDiscount = max(0, $subtotal + $shipPrice - $totalPrice);

    $wardName = trim((string) ($infoUser['ward_name'] ?? ($infoUser['ward'] ?? '')));
    $cityName = trim((string) ($infoUser['city_name'] ?? ($infoUser['city'] ?? '')));
    $addressLine = trim((string) ($infoUser['address'] ?? ''));
    $addressFull = implode(', ', array_filter([$addressLine, $wardName, $cityName], static fn($v) => $v !== ''));

    $orderLookupUrl = (string) url('order.lookup', null, ['code' => $orderCode]);
@endphp

<table align="center" bgcolor="#dcf0f8" border="0" cellpadding="0" cellspacing="0"
    style="margin:0;padding:0;background-color:#f2f2f2;width:100%!important;font-family:Arial,Helvetica,sans-serif;font-size:12px;color:#444;line-height:18px"
    width="100%">
    <tbody>
        @component('component.email.header')
        @endcomponent

        <tr>
            <td align="center"
                style="font-family:Arial,Helvetica,sans-serif;font-size:12px;color:#444;line-height:18px;font-weight:normal"
                valign="top">
                <table border="0" cellpadding="0" cellspacing="0" width="600">
                    <tbody>
                        <tr style="background:#fff">
                            <td align="left" height="auto" style="padding:15px" width="600">
                                <table width="100%">
                                    <tbody>
                                        <tr>
                                            <td>
                                                <h1
                                                    style="font-size:17px;font-weight:bold;color:#444;padding:0 0 5px 0;margin:0">
                                                    Cảm ơn quý khách đã đặt hàng tại {{ $optSetting['website'] }}
                                                </h1>
                                                <p
                                                    style="margin:4px 0;font-family:Arial,Helvetica,sans-serif;font-size:12px;color:#444;line-height:18px;font-weight:normal">
                                                    Chúng tôi rất vui thông báo đơn hàng #{{ $orderCode }} của quý khách đã
                                                    được tiếp nhận và đang trong quá trình xử lý.
                                                    {{ $setting['namevi'] }} sẽ thông báo đến quý khách ngay khi hàng
                                                    chuẩn bị được giao.
                                                </p>
                                                <h3
                                                    style="font-size:13px;font-weight:bold;color:{{ $params['emailColor'] }};text-transform:uppercase;margin:20px 0 0 0;padding:0 0 5px;border-bottom:1px solid #ddd">
                                                    Thông tin đơn hàng #{{ $orderCode }}
                                                    <span
                                                        style="font-size:12px;color:#777;text-transform:none;font-weight:normal">({{ $orderDate }})</span>
                                                </h3>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td style="font-family:Arial,Helvetica,sans-serif;font-size:12px;color:#444;line-height:18px">
                                                <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                                    <thead>
                                                        <tr>
                                                            <th align="left"
                                                                style="padding:6px 9px 0 0;font-family:Arial,Helvetica,sans-serif;font-size:12px;color:#444;font-weight:bold"
                                                                width="50%">
                                                                Thông tin thanh toán
                                                            </th>
                                                            <th align="left"
                                                                style="padding:6px 0 0 9px;font-family:Arial,Helvetica,sans-serif;font-size:12px;color:#444;font-weight:bold"
                                                                width="50%">
                                                                Địa chỉ giao hàng
                                                            </th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr>
                                                            <td style="padding:3px 9px 9px 0;border-top:0;font-family:Arial,Helvetica,sans-serif;font-size:12px;color:#444;line-height:18px;font-weight:normal"
                                                                valign="top">
                                                                <span
                                                                    style="text-transform:capitalize">{{ $infoUser['fullname'] ?? '' }}</span><br>
                                                                <a href="mailto:{{ $infoUser['email'] ?? '' }}"
                                                                    target="_blank">{{ $infoUser['email'] ?? '' }}</a><br>
                                                                {{ $infoUser['phone'] ?? '' }}
                                                            </td>
                                                            <td style="padding:3px 0 9px 9px;border-top:0;border-left:0;font-family:Arial,Helvetica,sans-serif;font-size:12px;color:#444;line-height:18px;font-weight:normal"
                                                                valign="top">
                                                                <span
                                                                    style="text-transform:capitalize">{{ $infoUser['fullname'] ?? '' }}</span><br>
                                                                <a href="mailto:{{ $infoUser['email'] ?? '' }}"
                                                                    target="_blank">{{ $infoUser['email'] ?? '' }}</a><br>
                                                                {{ $addressFull !== '' ? $addressFull : '-' }}<br>
                                                                Tel: {{ $infoUser['phone'] ?? '' }}
                                                            </td>
                                                        </tr>

                                                        <tr>
                                                            <td colspan="2"
                                                                style="padding:7px 0 0 0;border-top:0;font-family:Arial,Helvetica,sans-serif;font-size:12px;color:#444"
                                                                valign="top">
                                                                <p
                                                                    style="margin:0;font-family:Arial,Helvetica,sans-serif;font-size:12px;color:#444;line-height:18px;font-weight:normal">
                                                                    <strong>Hình thức thanh toán:</strong>
                                                                    {{ $paymentName ?: 'Chưa cập nhật' }}
                                                                    <br><strong>Phí vận chuyển:</strong>
                                                                    {{ $shipPrice > 0 ? Func::formatMoney($shipPrice) : 'Miễn phí' }}
                                                                </p>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </td>
                                        </tr>

                                        @if ($requirements !== '')
                                            <tr>
                                                <td>
                                                    <p
                                                        style="margin:8px 0 4px;font-family:Arial,Helvetica,sans-serif;font-size:12px;color:#444;line-height:18px;font-weight:normal">
                                                        <strong>Yêu cầu khác:</strong>
                                                        {!! nl2br(e($requirements)) !!}
                                                    </p>
                                                </td>
                                            </tr>
                                        @endif

                                        <tr>
                                            <td>
                                                <h2
                                                    style="text-align:left;margin:10px 0;border-bottom:1px solid #ddd;padding-bottom:5px;font-size:13px;color:{{ $params['emailColor'] }}">
                                                    CHI TIẾT ĐƠN HÀNG
                                                </h2>

                                                <table border="1" cellpadding="0" cellspacing="0" style="background:#f5f5f5"
                                                    width="100%">
                                                    <thead>
                                                        <tr>
                                                            <th align="center" bgcolor="{{ $params['emailColor'] }}"
                                                                style="padding:6px 9px;color:#333;font-family:Arial,Helvetica,sans-serif;font-size:12px;line-height:14px">
                                                                Hình ảnh
                                                            </th>
                                                            <th align="left" bgcolor="{{ $params['emailColor'] }}"
                                                                style="padding:6px 9px;color:#333;font-family:Arial,Helvetica,sans-serif;font-size:12px;line-height:14px">
                                                                Sản phẩm
                                                            </th>
                                                            <th align="right" bgcolor="{{ $params['emailColor'] }}"
                                                                style="padding:6px 9px;color:#333;font-family:Arial,Helvetica,sans-serif;font-size:12px;line-height:14px">
                                                                Đơn giá
                                                            </th>
                                                            <th align="center" bgcolor="{{ $params['emailColor'] }}"
                                                                style="padding:6px 9px;color:#333;font-family:Arial,Helvetica,sans-serif;font-size:12px;line-height:14px;min-width:55px;">
                                                                Số lượng
                                                            </th>
                                                            <th align="right" bgcolor="{{ $params['emailColor'] }}"
                                                                style="padding:6px 9px;color:#333;font-family:Arial,Helvetica,sans-serif;font-size:12px;line-height:14px">
                                                                Thành tiền
                                                            </th>
                                                        </tr>
                                                    </thead>

                                                    <tbody bgcolor="#f7f7f7"
                                                        style="font-family:Arial,Helvetica,sans-serif;font-size:12px;color:#444;line-height:18px">
                                                        @foreach ($orderDetails as $item)
                                                            @php
                                                                $itemProduct = data_get($item, 'options.itemProduct', []);
                                                                $properties = (array) (data_get($item, 'options.properties', []) ?? []);
                                                                $orderPhoto = data_get($item, 'options.variantPhoto', data_get($itemProduct, 'photo', ''));

                                                                $qty = max(1, (int) data_get($item, 'qty', 1));
                                                                $salePrice = max(0, (float) data_get($itemProduct, 'sale_price', 0));
                                                                $regularPrice = max(0, (float) data_get($itemProduct, 'regular_price', 0));
                                                                $unitPrice = max(0, (float) data_get($item, 'price', 0));
                                                                if ($unitPrice <= 0) {
                                                                    $unitPrice = $salePrice > 0 ? $salePrice : $regularPrice;
                                                                }
                                                                $lineTotal = $unitPrice * $qty;
                                                            @endphp
                                                            <tr>
                                                                <td align="center" style="padding:6px 9px" valign="top">
                                                                    <img style="width:90px"
                                                                        onerror="this.src='../assets/images/noimage.png';"
                                                                        src="{{ upload('product', $orderPhoto) }}"
                                                                        alt="{{ data_get($item, 'name', 'Sản phẩm') }}"
                                                                        title="{{ data_get($item, 'name', 'Sản phẩm') }}">
                                                                </td>

                                                                <td align="left" style="padding:6px 9px" valign="top">
                                                                    <h3 style="margin:0 0 4px">
                                                                        {{ data_get($item, 'name', 'Sản phẩm') }}
                                                                    </h3>
                                                                    @foreach ($properties as $property)
                                                                        @php
                                                                            $propertyName = '';
                                                                            if (is_array($property)) {
                                                                                $propertyName = (string) ($property['namevi'] ?? '');
                                                                            } elseif (is_object($property)) {
                                                                                $propertyName = (string) ($property->namevi ?? '');
                                                                            }
                                                                            $propertyName = trim($propertyName);
                                                                        @endphp
                                                                        @if ($propertyName !== '')
                                                                            <p style="margin:0;color:#0f5db7">{{ $propertyName }}</p>
                                                                        @endif
                                                                    @endforeach
                                                                </td>

                                                                <td align="right" style="padding:6px 9px" valign="top">
                                                                    <div>
                                                                        <strong>{{ Func::formatMoney($unitPrice) }}</strong>
                                                                        @if ($salePrice > 0 && $regularPrice > $salePrice)
                                                                            <div style="color:#888;text-decoration:line-through">
                                                                                {{ Func::formatMoney($regularPrice) }}
                                                                            </div>
                                                                        @endif
                                                                    </div>
                                                                </td>

                                                                <td align="center" style="padding:6px 9px" valign="top">
                                                                    {{ $qty }}
                                                                </td>

                                                                <td align="right" style="padding:6px 9px" valign="top">
                                                                    <strong>{{ Func::formatMoney($lineTotal) }}</strong>
                                                                </td>
                                                            </tr>
                                                        @endforeach

                                                        <tr>
                                                            <td colspan="4" align="right" style="padding:6px 9px">
                                                                Tạm tính:
                                                            </td>
                                                            <td align="right" style="padding:6px 9px">
                                                                {{ Func::formatMoney($subtotal) }}
                                                            </td>
                                                        </tr>

                                                        <tr>
                                                            <td colspan="4" align="right" style="padding:6px 9px">
                                                                Phí vận chuyển:
                                                            </td>
                                                            <td align="right" style="padding:6px 9px">
                                                                {{ $shipPrice > 0 ? Func::formatMoney($shipPrice) : 'Miễn phí' }}
                                                            </td>
                                                        </tr>

                                                        @if ($voucherDiscount > 0)
                                                            <tr>
                                                                <td colspan="4" align="right" style="padding:6px 9px">
                                                                    Giảm voucher:
                                                                </td>
                                                                <td align="right" style="padding:6px 9px;color:#d0021b">
                                                                    -{{ Func::formatMoney($voucherDiscount) }}
                                                                </td>
                                                            </tr>
                                                        @endif

                                                        <tr>
                                                            <td colspan="4" align="right" style="padding:6px 9px">
                                                                <strong>Tổng thanh toán:</strong>
                                                            </td>
                                                            <td align="right" style="padding:6px 9px">
                                                                <strong
                                                                    style="color:#d0021b">{{ Func::formatMoney($totalPrice) }}</strong>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>

                                                <div style="margin:auto;text-align:center">
                                                    <a href="{{ $orderLookupUrl }}"
                                                        style="display:inline-block;text-decoration:none;background-color:{{ $params['emailColor'] }}!important;text-align:center;border-radius:3px;color:#333;padding:5px 10px;font-size:12px;font-weight:bold;margin-top:8px"
                                                        target="_blank">
                                                        Kiểm tra đơn hàng tại {{ $optSetting['website'] }}
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </td>
        </tr>

        @component('component.email.footer')
        @endcomponent
    </tbody>
</table>
