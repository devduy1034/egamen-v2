@extends('layout')
@section('content')
    <section class="order-lookup-page py-5">
        <div class="container">
            <div class="order-lookup-shell">
                <h1 class="order-lookup-title">{{ $titleMain ?? 'Tra cứu đơn hàng' }}</h1>
                <p class="order-lookup-subtitle">Nhập mã đơn hàng để kiểm tra trạng thái và chi tiết đơn.</p>

                <form class="order-lookup-form" method="get" action="{{ url('order.lookup') }}">
                    <input type="text" class="form-control" name="code" placeholder="Nhập mã đơn hàng"
                        value="{{ $lookupCode ?? '' }}" required>
                    <button type="submit" class="btn btn-primary">Tra cứu</button>
                </form>

                @if (!empty($lookupError))
                    <div class="order-lookup-alert alert alert-danger">{{ $lookupError }}</div>
                @endif

                @if (!empty($lookupOrder))
                    @php
                        $order = $lookupOrder;
                        $orderInfo = is_array($order->info_user ?? null) ? $order->info_user : [];
                        $orderItems = is_array($order->order_detail ?? null) ? array_values($order->order_detail) : [];
                    @endphp
                    <div class="order-lookup-result">
                        <div class="order-lookup-meta">
                            <p><strong>Mã đơn hàng:</strong> <span>{{ $order->code ?? '-' }}</span></p>
                            <p><strong>Thời gian:</strong> <span>{{ $lookupCreatedAt ?? '-' }}</span></p>
                            <p><strong>Trạng thái:</strong> <span>{{ $lookupStatusName ?? '-' }}</span></p>
                            <p><strong>Thanh toán:</strong> <span>{{ $lookupPaymentName ?? '-' }}</span></p>
                            <p><strong>Người nhận:</strong> <span>{{ $orderInfo['fullname'] ?? '-' }}</span></p>
                            <p><strong>Điện thoại:</strong> <span>{{ $orderInfo['phone'] ?? '-' }}</span></p>
                            <p><strong>Địa chỉ:</strong> <span>{{ !empty($lookupAddress) ? $lookupAddress : '-' }}</span></p>
                        </div>

                        @if (!empty($orderItems))
                            <div class="table-responsive">
                                <table class="table table-bordered order-lookup-table">
                                    <thead>
                                        <tr>
                                            <th class="text-center">Hình ảnh</th>
                                            <th>Sản phẩm</th>
                                            <th class="text-center">Số lượng</th>
                                            <th class="text-end">Đơn giá</th>
                                            <th class="text-end">Thành tiền</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($orderItems as $item)
                                            @php
                                                $itemProduct = data_get($item, 'options.itemProduct', []);
                                                $orderPhoto = (string) data_get($item, 'options.variantPhoto', data_get($itemProduct, 'photo', ''));
                                                $orderThumb = $orderPhoto !== ''
                                                    ? assets_photo('product', '100x100x1', $orderPhoto, 'thumbs')
                                                    : assets('assets/images/noimage.png');
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
                                                <td class="text-center">
                                                    <img class="order-lookup-thumb" src="{{ $orderThumb }}"
                                                        alt="{{ data_get($item, 'name', 'Sản phẩm') }}"
                                                        onerror="this.src='{{ assets('assets/images/noimage.png') }}';">
                                                </td>
                                                <td>{{ data_get($item, 'name', '-') }}</td>
                                                <td class="text-center">{{ $qty }}</td>
                                                <td class="text-end">{{ Func::formatMoney($unitPrice) }}</td>
                                                <td class="text-end"><strong>{{ Func::formatMoney($lineTotal) }}</strong></td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="4" class="text-end">Tạm tính</td>
                                            <td class="text-end">{{ Func::formatMoney((float) ($order->temp_price ?? 0)) }}</td>
                                        </tr>
                                        <tr>
                                            <td colspan="4" class="text-end">Phí vận chuyển</td>
                                            <td class="text-end">
                                                {{ (float) ($order->ship_price ?? 0) > 0 ? Func::formatMoney((float) ($order->ship_price ?? 0)) : 'Miễn phí' }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="4" class="text-end"><strong>Tổng thanh toán</strong></td>
                                            <td class="text-end"><strong>{{ Func::formatMoney((float) ($order->total_price ?? 0)) }}</strong></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </section>
@endsection
