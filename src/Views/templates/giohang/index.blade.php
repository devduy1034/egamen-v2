@extends('layout')
@section('content')

    <div class="wrap-content py-4">
        @php
            $cartStockJson = collect($cartStockByRow ?? [])->toJson(JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
        @endphp
        <form class="form-cart" method="post" action="cart/send-to-cart" enctype="multipart/form-data"
            data-cart-stock="{{ e($cartStockJson) }}"
            data-cart-has-out-of-stock="{{ !empty($cartHasOutOfStock) ? '1' : '0' }}">
            <div class="wrap-cart flex items-stretch justify-between">
                @if (Cart::count() > 0)
                    @php
                        $cartSubtotal = (float) Cart::subtotalFloat();
                        $defaultShipPrice = (float) ($defaultShipPrice ?? 0);
                        $voucherItems = collect($vouchers ?? [])->map(function ($voucher) {
                            $discountType = strtoupper((string) ($voucher['discount_type'] ?? ''));
                            $discountValue = (float) ($voucher['discount_value'] ?? 0);
                            $maxDiscount = (float) ($voucher['max_discount'] ?? 0);
                            $minOrderValue = (float) ($voucher['min_order_value'] ?? 0);

                            if ($discountType === 'PERCENT') {
                                $discountText = 'Giảm ' . rtrim(rtrim(number_format($discountValue, 2, '.', ''), '0'), '.') . '%';
                                if ($maxDiscount > 0) {
                                    $discountText .= ' tối đa ' . number_format($maxDiscount, 0, ',', '.') . 'đ';
                                }
                            } elseif ($discountType === 'FIXED_AMOUNT') {
                                $discountText = 'Giảm ' . number_format($discountValue, 0, ',', '.') . 'đ';
                            } else {
                                $discountText = 'Miễn phí vận chuyển';
                                if ($discountValue > 0) {
                                    $discountText .= ' tối đa ' . number_format($discountValue, 0, ',', '.') . 'đ';
                                }
                            }

                            $summaryText = trim((string) ($voucher['description'] ?? ''));
                            if ($summaryText === '') {
                                $summaryText = $discountText;
                                if ($minOrderValue > 0) {
                                    $summaryText .= ' cho đơn từ ' . number_format($minOrderValue, 0, ',', '.') . 'đ';
                                }
                            }

                            $expiryTimestamp = !empty($voucher['end_at']) ? strtotime((string) $voucher['end_at']) : false;

                            return [
                                'id' => (int) ($voucher['id'] ?? 0),
                                'code' => strtoupper(trim((string) ($voucher['code'] ?? ''))),
                                'discount_type' => $discountType ?: 'FIXED_AMOUNT',
                                'discount_value' => $discountValue,
                                'max_discount' => $maxDiscount,
                                'min_order_value' => $minOrderValue,
                                'summary' => $summaryText,
                                'expiry' => $expiryTimestamp ? date('d/m/Y', $expiryTimestamp) : ''
                            ];
                        })->filter(function ($voucher) {
                            return !empty($voucher['code']);
                        })->values();
                    @endphp
                    <div class="top-cart">
                        <p class="title-cart">{{ __('web.giohangcuaban') }}:</p>
                        <div class="list-procart">
                            <div class="procart procart-label d-flex align-items-start justify-content-between">
                                <div class="pic-procart ">{{ __('web.hinhanh') }}</div>
                                <div class="info-procart ">{{ __('web.tensanpham') }}</div>
                                <div class="quantity-procart ">
                                    <p>{{ __('web.soluong') }}</p>
                                    <p>{{ __('web.thanhtien') }}</p>
                                </div>
                                <div class="price-procart ">{{ __('web.tongtien') }}</div>
                            </div>
                            @foreach (Cart::content() as $k => $v)
                                @php
                                    $color = $v->options->color ?? '';
                                    $size = $v->options->size ?? '';
                                    $proInfo = $v->options->itemProduct;
                                    $productCode = $cartCodesByRow[(string) $v->rowId] ?? ($v->options->productCode ?? ($proInfo->code ?? ''));
                                    $cartPhoto = $v->options->variantPhoto ?? ($proInfo->photo ?? '');
                                    $rowStock = $cartStockByRow[(string) $v->rowId] ?? ['in_stock' => true, 'available_qty' => null, 'message' => ''];
                                    $rowOutOfStock = empty($rowStock['in_stock']);
                                    $rowStockMessage = trim((string) ($rowStock['message'] ?? ''));
                                    if ($rowOutOfStock && $rowStockMessage === '') {
                                        $rowStockMessage = 'Sản phẩm đã hết hàng.';
                                    }

                                    $pro_price = $proInfo->regular_price;
                                    $pro_price_new = $proInfo->sale_price;
                                    $pro_price_qty = $pro_price * $v->qty;
                                    $pro_price_new_qty = $pro_price_new * $v->qty;
                                @endphp

                                <div class="procart flex items-start justify-between procart-{{ $v->rowId }}">
                                    <div class="pic-procart">
                                        <a class="text-decoration-none"
                                            href="{{ url('slugweb', ['slug' => $proInfo->slugvi]) }}" target="_blank"
                                            title="{{ $proInfo->namevi }}">
                                            <img src="{{ assets_photo('product', '100x100x1', $cartPhoto, 'thumbs') }}"
                                                alt="{{ $proInfo->namevi }}" />
                                        </a>
                                        <a class="del-procart text-decoration-none" data-rowId="{{ $v->rowId }}">
                                            <i class="fa fa-times-circle"></i>
                                            <span>{{ __('web.xoa') }}</span>
                                        </a>
                                    </div>
                                    <div class="info-procart">
                                        <h3 class="name-procart"><a class="text-decoration-none"
                                                href="{{ url('slugweb', ['slug' => $proInfo->slugvi]) }}" target="_blank"
                                                title="{{ $proInfo->namevi }}">{{ $proInfo->namevi }}</a></h3>
                                        @if (!empty($productCode))
                                            <p class="code-procart">{{ __('web.code') }}: <span>{{ $productCode }}</span></p>
                                        @endif
                                        @if (!empty($v->options->properties->toArray()))
                                            <div class="properties-procart">
                                                @foreach ($v->options->properties as $kp => $vp)
                                                    <p>{{ $vp->namevi }}</p> <br />
                                                @endforeach
                                            </div>
                                        @endif
                                        <p class="cart-stock-state js-cart-stock-state {{ $rowOutOfStock ? 'is-error' : '' }}"
                                            data-row-id="{{ $v->rowId }}" {{ $rowOutOfStock ? '' : 'hidden' }}>
                                            {{ $rowStockMessage }}
                                        </p>
                                    </div>
                                    <div class="quantity-procart">
                                        <div class="price-procart price-procart-rp">
                                            @if (!empty($proInfo->sale_price))
                                                <p class="price-new-cart load-price-new-{{ $v->rowId }}">
                                                    {!! Func::formatMoney((float) $pro_price_new_qty) !!}</p>
                                                <p class="price-old-cart load-price-{{ $v->rowId }}">
                                                    {!! Func::formatMoney((float) $pro_price_qty) !!}</p>
                                            @else
                                                <p class="price-new-cart load-price-{{ $v->rowId }}">
                                                    {!! Func::formatMoney((float) $pro_price_qty) !!}</p>
                                            @endif
                                        </div>
                                        <div
                                            class="quantity-counter-procart quantity-counter-procart-{{ $v->rowId }} flex items-stretch justify-between">
                                            <span class="counter-procart-minus counter-procart">-</span>
                                            <input type="text" readonly class="quantity-procat" min="1"
                                                value="{{ $v->qty }}" data-pid="{{ $v->id }}"
                                                data-rowId="{{ $v->rowId }}"
                                                data-stock-blocked="{{ $rowOutOfStock ? '1' : '0' }}"
                                                data-available-qty="{{ $rowStock['available_qty'] ?? '' }}" />
                                            <span
                                                class="counter-procart-plus counter-procart {{ $rowOutOfStock ? 'is-disabled' : '' }}">+</span>
                                        </div>

                                    </div>
                                    <div class="price-procart">
                                        @if (!empty($proInfo->sale_price))
                                            <p class="price-new-cart load-price-new-{{ $v->rowId }}">
                                                {!! Func::formatMoney((float) $pro_price_new_qty) !!}</p>
                                            <p class="price-old-cart load-price-{{ $v->rowId }}">
                                                {!! Func::formatMoney((float) $pro_price_qty) !!}</p>
                                        @else
                                            <p class="price-new-cart load-price-{{ $v->rowId }}">
                                                {!! Func::formatMoney((float) $pro_price_qty) !!}</p>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="voucher-procart js-voucher-cart"
                            data-vouchers="{{ e($voucherItems->toJson(JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT)) }}">
                            <p class="title-voucher-procart">Ưu đãi dành cho bạn</p>
                            <div class="voucher-input-procart">
                                <input type="text" class="form-control js-voucher-input" placeholder="Nhập mã voucher">
                                <button type="button" class="btn js-voucher-apply">Áp dụng voucher</button>
                            </div>
                            <input type="hidden" name="dataOrder[voucher_code]" class="js-voucher-code-input"
                                value="">
                            <input type="hidden" name="dataOrder[voucher_discount]" class="js-voucher-discount-input"
                                value="0">
                            <input type="hidden" name="dataOrder[ship_price]" class="js-ship-price-input"
                                value="{{ $defaultShipPrice }}">
                            <input type="hidden" name="dataOrder[total_price]" class="js-total-price-input"
                                value="{{ $cartSubtotal + $defaultShipPrice }}">
                            @if ($voucherItems->isNotEmpty())
                                <div class="voucher-list-procart">
                                    @foreach ($voucherItems as $voucher)
                                        <button type="button" class="voucher-card-procart js-voucher-card"
                                            data-code="{{ $voucher['code'] }}"
                                            data-discount-type="{{ $voucher['discount_type'] }}"
                                            data-discount-value="{{ $voucher['discount_value'] }}"
                                            data-max-discount="{{ $voucher['max_discount'] }}"
                                            data-min-order-value="{{ $voucher['min_order_value'] }}">
                                            <span class="voucher-card-radio"></span>
                                            <div class="voucher-card-content">
                                                <p class="voucher-card-code">{{ $voucher['code'] }}</p>
                                                <p class="voucher-card-summary">{{ $voucher['summary'] }}</p>
                                                @if (!empty($voucher['expiry']))
                                                    <p class="voucher-card-expiry">HSD: {{ $voucher['expiry'] }}</p>
                                                @endif
                                            </div>
                                        </button>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                        <div class="money-procart mb-2 mb-md-4 js-cart-pricing"
                            data-base-ship="{{ $defaultShipPrice }}">
                            <div class="total-procart flex items-center justify-between">
                                <p>Tạm tính:</p>
                                <p class="load-price-subtotal">{!! Func::formatMoney($cartSubtotal) !!}</p>
                            </div>
                            <div class="total-procart flex items-center justify-between">
                                <p>Giảm voucher:</p>
                                <p class="load-price-discount text-danger">0đ</p>
                            </div>
                            <div class="total-procart flex items-center justify-between">
                                <p>Phí vận chuyển:</p>
                                <p class="load-price-ship">
                                    {{ $defaultShipPrice > 0 ? Func::formatMoney($defaultShipPrice) : 'Miễn phí' }}</p>
                            </div>
                            <div class="total-procart flex items-center justify-between">
                                <p>{{ __('web.tongtien') }}:</p>
                                <p class="total-price load-price-total">{!! Func::formatMoney($cartSubtotal + $defaultShipPrice) !!}</p>
                            </div>
                        </div>
                        <p class="title-cart">{{ __('web.hinhthucthanhtoan') }}:</p>
                        <div class="information-cart">
                            @foreach ($httt as $k => $v)
                                <div class="payments-cart">
                                    <input type="radio" class="payments-radio" id="payments-{{ $v->id }}"
                                        name="dataOrder[payments]" value="{{ $v->id }}" required>
                                    <label class="payments-label" for="payments-{{ $v->id }}"
                                        data-payments="{{ $v->id }}">
                                        <span class="payments-title">{{ $v->namevi }}</span>
                                    </label>
                                    @if (!empty($v->descvi))
                                        <div class="payments-info payments-info-{{ $v->id }} transition">
                                            {!! nl2br($v->descvi) !!}</div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="bottom-cart">
                        <div class="section-cart">

                            <p class="title-cart">{{ __('web.thongtingiohang') }}:</p>
                            <div class="information-cart !mb-[10px]">
                                @if (!empty($memberAddresses))
                                    <div class="input-cart">
                                        <select class="form-select form-control text-sm js-saved-address">
                                            <option value="">Chọn địa chỉ đã lưu</option>
                                            @foreach ($memberAddresses as $savedAddress)
                                                @php
                                                    $savedAddressLine = trim((string) ($savedAddress['address_line'] ?? ''));
                                                    $savedWard = trim((string) ($savedAddress['ward'] ?? ''));
                                                    $savedCity = trim((string) ($savedAddress['city'] ?? ''));
                                                    $savedAddressLabel = $savedAddressLine .
                                                        ($savedWard !== '' ? ', ' . $savedWard : '') .
                                                        ($savedCity !== '' ? ', ' . $savedCity : '');
                                                    if ($savedAddressLabel === '') {
                                                        $savedAddressLabel = trim((string) ($savedAddress['recipient_name'] ?? 'Địa chỉ đã lưu'));
                                                    }
                                                @endphp
                                                <option value="{{ $savedAddress['id'] ?? '' }}"
                                                    data-fullname="{{ $savedAddress['recipient_name'] ?? '' }}"
                                                    data-phone="{{ $savedAddress['recipient_phone'] ?? '' }}"
                                                    data-address-line="{{ $savedAddress['address_line'] ?? '' }}"
                                                    data-city-id="{{ (int) ($savedAddress['city_id'] ?? 0) }}"
                                                    data-city-name="{{ $savedAddress['city'] ?? '' }}"
                                                    data-ward-id="{{ (int) ($savedAddress['ward_id'] ?? 0) }}"
                                                    data-ward-name="{{ $savedAddress['ward'] ?? '' }}"
                                                    {{ !empty($savedAddress['is_default']) ? 'selected' : '' }}>
                                                    {{ $savedAddressLabel }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                @endif
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-[10px]">
                                    <div class="input-cart">
                                        <div class="input-cart">
                                            <input type="text" class="form-control text-sm" id="fullname"
                                                name="dataOrder[fullname]" placeholder="{{ __('web.hoten') }}"
                                                value="{{ $checkoutPrefill['fullname'] ?? '' }}" required />
                                        </div>
                                        <div class="invalid-feedback">{{ __('web.vuilongnhaphoten') }}</div>
                                    </div>
                                    <div class="input-cart">
                                        <div class="input-cart">
                                            <input type="tel" class="form-control text-sm" id="phone"
                                                name="dataOrder[phone]" placeholder="{{ __('web.dienthoai') }}"
                                                value="{{ $checkoutPrefill['phone'] ?? '' }}"
                                                pattern="^0(2[4-9]|3[0-9]|5[6789]|7[0-89]|8[1-9]|9[1-46-9])[0-9]{7}$"
                                                title="Vui long dien dung cac dau so hien co o VN (03, 05, 07, 08, 09)"
                                                minlength="10" maxlength="10" required />
                                        </div>
                                        <div class="invalid-feedback">{{ __('web.vuilongnhapsodienthoai') }}</div>
                                    </div>
                                </div>
                                <div class="input-cart">
                                    <div class="input-cart">
                                        <input type="email" class="form-control text-sm" id="email"
                                            name="dataOrder[email]" placeholder="Email" value="{{ $checkoutPrefill['email'] ?? '' }}" required />
                                    </div>
                                    <div class="invalid-feedback">{{ __('web.vuilongnhapdiachiemail') }}</div>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-x-[10px]">
                                    <div class="input-cart">
                                        <select class="select-city-cart form-select form-control text-sm" required
                                            id="city" name="dataOrder[city]">
                                            <option value="">{{ __('web.tinhthanh') }}</option>
                                            @foreach ($city ?? [] as $k => $v)
                                                <option value="{{ $v->id }}"
                                                    data-city-name="{{ $v->namevi }}"
                                                    {{ (int) ($checkoutPrefill['city_id'] ?? 0) === (int) $v->id ? 'selected' : '' }}>
                                                    {{ $v->namevi }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <div class="invalid-feedback">{{ __('web.vuilongchontinhthanh') }}</div>
                                    </div>
                                    <div class="input-cart">
                                        <select class="select-ward-cart select-ward form-select form-control text-sm"
                                            required id="ward" name="dataOrder[ward]"
                                            data-selected="{{ (int) ($checkoutPrefill['ward_id'] ?? 0) }}"
                                            data-selected-name="{{ $checkoutPrefill['ward_name'] ?? '' }}">
                                            <option value="">{{ __('web.phuongxa') }}</option>
                                        </select>
                                        <div class="invalid-feedback">{{ __('web.vuilongchonphuongxa') }}</div>
                                    </div>
                                </div>
                                <div class="input-cart">
                                    <div class="input-cart">
                                        <input type="text" class="form-control text-sm" id="address"
                                            name="dataOrder[address]" placeholder="{{ __('web.diachi') }}"
                                            value="{{ $checkoutPrefill['address'] ?? '' }}" required />
                                    </div>
                                    <div class="invalid-feedback">{{ __('web.vuilongnhapdiachi') }}</div>
                                </div>
                                <div class="input-cart">
                                    <div class="input-cart">
                                        <textarea class="form-control text-sm" id="requirements" name="dataOrder[requirements]"
                                            placeholder="{{ __('web.yeucaukhac') }}"></textarea>
                                    </div>
                                </div>
                            </div>
                            <p class="cart-checkout-alert js-cart-checkout-alert {{ !empty($cartHasOutOfStock) ? '' : 'hidden' }}">
                                Giỏ hàng có sản phẩm hết hàng. Vui lòng cập nhật trước khi thanh toán.
                            </p>
                            <input type="submit" class="btn btn-primary btn-cart js-btn-checkout w-100" name="thanhtoan"
                                value="{{ __('web.thanhtoan') }}" {{ !empty($cartHasOutOfStock) ? 'disabled' : '' }} />
                        </div>
                    </div>
                @else
                    <div>{{ __('web.banchuacosanphamtronggiohang') }}</div>
                @endif
            </div>
        </form>
    </div>

@endsection

