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

                <div class="row">
                    <div class="col-12 col-lg-8">
                        <div class="card card-primary card-outline text-sm mb-4">
                            <div class="card-header">
                                <h3 class="card-title">Thông tin cơ bản</h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Tên voucher (nội bộ)</label>
                                        <input type="text" class="form-control" name="data[name]"
                                            value="{{ $item['name'] ?? '' }}" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Code</label>
                                        <input type="text" class="form-control text-uppercase" name="data[code]"
                                            value="{{ $item['code'] ?? '' }}" required>
                                    </div>
                                    <div class="col-12 mb-3">
                                        <label class="form-label">Mô tả ngắn</label>
                                        <textarea class="form-control" name="data[description]" rows="3" placeholder="Hiển thị cho user (tùy chọn)">{{ $item['description'] ?? '' }}</textarea>
                                    </div>
                                    <div class="col-12 mb-3">
                                        <label class="form-label">Điều kiện chi tiết</label>
                                        <textarea class="form-control" name="data[condition_text]" rows="3"
                                            placeholder="Nhập điều kiện hiển thị riêng, khác mô tả ngắn">{{ $item['condition_text'] ?? '' }}</textarea>
                                        <small class="text-muted">Trường này dùng cho phần
                                            điều kiện voucher, không dùng làm mô
                                            tả ngắn.</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card card-primary card-outline text-sm mb-4">
                            <div class="card-header">
                                <h3 class="card-title">Kiểu giảm giá</h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Loại giảm giá</label>
                                        <select id="js-discount-type" name="data[discount_type]" class="form-select">
                                            <option value="PERCENT"
                                                {{ ($item['discount_type'] ?? '') === 'PERCENT' ? 'selected' : '' }}>PERCENT
                                                (Giảm theo %)</option>
                                            <option value="FIXED_AMOUNT"
                                                {{ ($item['discount_type'] ?? '') === 'FIXED_AMOUNT' ? 'selected' : '' }}>
                                                FIXED_AMOUNT (Giảm số tiền cố định)</option>
                                            <option value="FREE_SHIP"
                                                {{ ($item['discount_type'] ?? '') === 'FREE_SHIP' ? 'selected' : '' }}>
                                                FREE_SHIP (Miễn phí vận chuyển)</option>
                                        </select>
                                        <small class="text-muted d-block mt-1">
                                            Chọn đúng loại để hệ thống tính giảm giá chính xác.
                                        </small>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Giá trị giảm</label>
                                        <input id="js-discount-value" type="number" class="form-control"
                                            name="data[discount_value]" min="0"
                                            value="{{ $item['discount_value'] ?? '' }}" placeholder="Ví dụ: 10 hoặc 50000">
                                        <small id="js-discount-value-help" class="text-muted d-block mt-1"></small>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Giảm tối đa (cap)</label>
                                        <input id="js-max-discount" type="number" class="form-control"
                                            name="data[max_discount]" min="0"
                                            value="{{ $item['max_discount'] ?? '' }}" placeholder="Áp dụng cho %">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card card-primary card-outline text-sm mb-4">
                            <div class="card-header">
                                <h3 class="card-title">Điều kiện áp dụng</h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Đơn tối thiểu</label>
                                        <input type="number" class="form-control" name="data[min_order_value]"
                                            min="0" value="{{ $item['min_order_value'] ?? '' }}">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Phạm vi áp dụng</label>
                                        <select id="js-scope-type" name="data[scope_type]" class="form-select">
                                            <option value="ALL"
                                                {{ ($item['scope_type'] ?? '') === 'ALL' ? 'selected' : '' }}>ALL</option>
                                            <option value="CATEGORY"
                                                {{ ($item['scope_type'] ?? '') === 'CATEGORY' ? 'selected' : '' }}>CATEGORY
                                            </option>
                                            <option value="PRODUCT"
                                                {{ ($item['scope_type'] ?? '') === 'PRODUCT' ? 'selected' : '' }}>PRODUCT
                                            </option>
                                        </select>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Loại trừ sản phẩm</label>
                                        <select name="data[exclude_products][]"
                                            class="form-select select2 js-voucher-scope-select" multiple
                                            data-placeholder="Chọn sản phẩm loại trừ">
                                            @if (empty($products) || count($products) === 0)
                                                <option value="" disabled>Chưa có sản phẩm để chọn</option>
                                            @else
                                                @foreach ($products as $product)
                                                    <option value="{{ $product['id'] }}"
                                                        {{ !empty($item['exclude_products']) && in_array($product['id'], $item['exclude_products']) ? 'selected' : '' }}>
                                                        {{ $product['name'] ?? ($product['namevi'] ?? '#' . $product['id']) }}
                                                    </option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3 js-scope-categories"
                                        style="{{ ($item['scope_type'] ?? 'ALL') === 'CATEGORY' ? '' : 'display:none;' }}">
                                        <label class="form-label">Danh mục áp dụng</label>
                                        <select name="data[scope_categories][]"
                                            class="form-select select2 js-voucher-scope-select" multiple
                                            data-placeholder="Chọn danh mục áp dụng">
                                            @if (empty($categories) || count($categories) === 0)
                                                <option value="" disabled>Chưa có danh mục để chọn</option>
                                            @else
                                                @foreach ($categories as $category)
                                                    <option value="{{ $category['id'] }}"
                                                        {{ !empty($item['scope_categories']) && in_array($category['id'], $item['scope_categories']) ? 'selected' : '' }}>
                                                        {{ $category['name'] ?? ($category['namevi'] ?? '#' . $category['id']) }}
                                                    </option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>

                                    <div class="col-md-6 mb-3 js-scope-categories"
                                        style="{{ ($item['scope_type'] ?? 'ALL') === 'CATEGORY' ? '' : 'display:none;' }}">
                                        <label class="form-label">Danh mục cấp 2 áp dụng</label>
                                        <select name="data[scope_categories_l2][]"
                                            class="form-select select2 js-voucher-scope-select" multiple
                                            data-placeholder="Chọn danh mục cấp 2 áp dụng">
                                            @if (empty($categories_level2) || count($categories_level2) === 0)
                                                <option value="" disabled>Chưa có danh mục cấp
                                                    2 để chọn</option>
                                            @else
                                                @php
                                                    $listNameById = [];
                                                    foreach ($categories ?? [] as $listCategory) {
                                                        $listNameById[(int) $listCategory['id']] =
                                                            $listCategory['name'] ??
                                                            ($listCategory['namevi'] ?? '#' . $listCategory['id']);
                                                    }
                                                @endphp
                                                @foreach ($categories_level2 as $categoryLevel2)
                                                    @php
                                                        $parentName =
                                                            $listNameById[(int) ($categoryLevel2['id_list'] ?? 0)] ??
                                                            'Danh mục';
                                                        $childName =
                                                            $categoryLevel2['name'] ??
                                                            ($categoryLevel2['namevi'] ?? '#' . $categoryLevel2['id']);
                                                    @endphp
                                                    <option value="{{ $categoryLevel2['id'] }}"
                                                        {{ !empty($item['scope_categories_l2']) && in_array($categoryLevel2['id'], $item['scope_categories_l2']) ? 'selected' : '' }}>
                                                        {{ $parentName }} / {{ $childName }}
                                                    </option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>

                                    <div class="col-md-6 mb-3 js-scope-products"
                                        style="{{ ($item['scope_type'] ?? 'ALL') === 'PRODUCT' ? '' : 'display:none;' }}">
                                        <label class="form-label">Sản phẩm áp dụng</label>
                                        <select name="data[scope_products][]"
                                            class="form-select select2 js-voucher-scope-select" multiple
                                            data-placeholder="Chọn sản phẩm áp dụng">
                                            @if (empty($products) || count($products) === 0)
                                                <option value="" disabled>Chưa có sản phẩm để chọn
                                                </option>
                                            @else
                                                @foreach ($products as $product)
                                                    <option value="{{ $product['id'] }}"
                                                        {{ !empty($item['scope_products']) && in_array($product['id'], $item['scope_products']) ? 'selected' : '' }}>
                                                        {{ $product['name'] ?? ($product['namevi'] ?? '#' . $product['id']) }}
                                                    </option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card card-primary card-outline text-sm mb-4">
                            <div class="card-header">
                                <h3 class="card-title">Giới hạn sử dụng</h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Giới hạn tổng</label>
                                        <input type="number" class="form-control" name="data[usage_limit_total]"
                                            min="0" value="{{ $item['usage_limit_total'] ?? '' }}">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Giới hạn mỗi người</label>
                                        <input type="number" class="form-control" name="data[usage_limit_per_user]"
                                            min="0" value="{{ $item['usage_limit_per_user'] ?? '' }}">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label d-block">1 đơn chỉ dùng 1 voucher</label>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox"
                                                name="data[one_voucher_per_order]" value="1"
                                                {{ empty($item) || !empty($item['one_voucher_per_order']) ? 'checked' : '' }}>
                                            <label class="form-check-label">Bật</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card card-primary card-outline text-sm mb-4">
                            <div class="card-header">
                                <h3 class="card-title">Thời gian</h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Thời gian bắt đầu</label>
                                        <input type="text" class="form-control js-voucher-datetime"
                                            name="data[start_at]" value="{{ $item['start_at'] ?? '' }}"
                                            placeholder="DD/MM/YYYY HH:mm">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Thời gian kết thúc</label>
                                        <input type="text" class="form-control js-voucher-datetime"
                                            name="data[end_at]" value="{{ $item['end_at'] ?? '' }}"
                                            placeholder="DD/MM/YYYY HH:mm">
                                    </div>
                                </div>
                            </div>
                        </div>
                        @if (!empty($item['id']))
                            <div class="card card-primary card-outline text-sm mb-4">
                                <div class="card-header">
                                    <h3 class="card-title">Thống kê sử dụng</h3>
                                </div>
                                <div class="card-body">
                                    <div class="mb-2"><b>Đã dùng:</b> {{ $item['used_count'] ?? 0 }}</div>
                                    <div class="mb-2"><b>Giới hạn tổng:</b>
                                        {{ $item['usage_limit_total'] ?? 'Không giới hạn' }}</div>
                                    <div><b>Giới hạn mỗi người:</b>
                                        {{ $item['usage_limit_per_user'] ?? 'Không giới hạn' }}
                                    </div>
                                </div>
                            </div>

                            <div class="card card-primary card-outline text-sm mb-4">
                                <div class="card-header">
                                    <h3 class="card-title">Đơn hàng gần nhất</h3>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-sm mb-0">
                                            <thead>
                                                <tr>
                                                    <th>Mã đơn hàng</th>
                                                    <th>Giảm</th>
                                                    <th>Ngày</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse(($usages ?? []) as $usage)
                                                    <tr>
                                                        <td>{{ $usage['order_code'] ?? '#' . ($usage['order_id'] ?? '') }}
                                                        </td>
                                                        <td>{{ Func::formatMoney($usage['discount_amount'] ?? 0) }}</td>
                                                        <td>{{ $usage['used_at'] ?? '-' }}</td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="3" class="text-center">Chưa có dữ liệu</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="col-12 col-lg-4">
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Hình ảnh voucher</h5>
                            </div>
                            <div class="card-body">
                                @php
                                    $photoWidth = $configMan->images->photo->width ?? 600;
                                    $photoHeight = $configMan->images->photo->height ?? 400;
                                    $photoDetail = [];
                                    $photoDetail['upload'] = 'voucher';
                                    $photoDetail['image'] = !empty($item) ? $item['photo'] ?? '' : '';
                                    $photoDetail['id'] = !empty($item) ? $item['id'] ?? 0 : 0;
                                    $photoDetail['dimension'] =
                                        'Width: ' .
                                        $photoWidth .
                                        ' px - Height: ' .
                                        $photoHeight .
                                        ' px (' .
                                        config('type.type_img') .
                                        ')';
                                @endphp
                                @component('component.image', ['photoDetail' => $photoDetail, 'photoAction' => 'photo', 'key' => 'photo'])
                                @endcomponent
                            </div>
                        </div>

                        <div class="card mb-4">
                            @component('component.tinhtrang', ['item' => $item ?? [], 'status' => $configMan->status ?? [], 'stt' => true])
                            @endcomponent
                        </div>


                    </div>
                </div>

                <input type="hidden" name="id"
                    value="{{ !empty($item['id']) && $item['id'] > 0 ? $item['id'] : '' }}">
                <input name="csrf_token" type="hidden" value="{{ csrf_token() }}">
                @component('component.buttonAdd')
                @endcomponent
            </form>
        </div>
    </div>
@endsection

@pushonce('styles')
    <link rel="stylesheet" href="@asset('assets/admin/vendor/libs/flatpickr/flatpickr.css')" />
@endpushonce

@pushonce('scripts')
    <script src="@asset('assets/admin/vendor/libs/flatpickr/flatpickr.js')"></script>
    <script>
        (function() {
            var inputs = document.querySelectorAll('.js-voucher-datetime');
            if (!inputs.length || typeof flatpickr !== 'function') return;

            inputs.forEach(function(input) {
                flatpickr(input, {
                    enableTime: true,
                    dateFormat: 'd/m/Y H:i',
                    time_24hr: true,
                    allowInput: true
                });
            });
        })
        ();

        (function() {
            var discountTypeSelect = document.getElementById('js-discount-type');
            var discountValueInput = document.getElementById('js-discount-value');
            var maxDiscountInput = document.getElementById('js-max-discount');
            var valueHelp = document.getElementById('js-discount-value-help');

            if (!discountTypeSelect || !discountValueInput) return;

            var updateDiscountInput = function() {
                var type = discountTypeSelect.value;

                if (type === 'PERCENT') {
                    discountValueInput.placeholder = 'Ví dụ: 10 (giảm 10%)';
                    if (valueHelp) valueHelp.textContent =
                        'Nhập phần trăm giảm. Ví dụ: 10 = giảm 10%.';
                    if (maxDiscountInput) {
                        maxDiscountInput.disabled = false;
                    }
                    return;
                }

                if (type === 'FIXED_AMOUNT') {
                    discountValueInput.placeholder = 'Ví dụ: 50000';
                    if (valueHelp) valueHelp.textContent =
                        'Nhập số tiền giảm cố định.';
                    if (maxDiscountInput) {
                        maxDiscountInput.value = '';
                        maxDiscountInput.disabled = true;
                    }
                    return;
                }

                discountValueInput.placeholder = 'Ví dụ: 30000 (freeship tối đa)';
                if (valueHelp) valueHelp.textContent =
                    'Nhập mức phí ship được giảm tối đa (0 = freeship toàn bộ).';
                if (maxDiscountInput) {
                    maxDiscountInput.value = '';
                    maxDiscountInput.disabled = true;
                }
            };

            discountTypeSelect.addEventListener('change', updateDiscountInput);
            updateDiscountInput();
        })();

        (function() {
            var initVoucherSelect2 = function() {
                if (!window.jQuery || !window.jQuery.fn || !window.jQuery.fn.select2) {
                    setTimeout(initVoucherSelect2, 150);
                    return;
                }

                window.jQuery('.js-voucher-scope-select').each(function() {
                    var $select = window.jQuery(this);
                    if ($select.data('select2')) return;
                    if (!$select.parent().hasClass('position-relative')) {
                        $select.wrap('<div class="position-relative"></div>');
                    }

                    $select.select2({
                        placeholder: $select.data('placeholder') || 'Chọn dữ liệu',
                        dropdownParent: $select.parent(),
                        width: '100%'
                    });
                });
            };

            initVoucherSelect2();

            var scopeSelect = document.getElementById('js-scope-type');
            if (!scopeSelect) return;

            var categoryWraps = document.querySelectorAll('.js-scope-categories');
            var productWraps = document.querySelectorAll('.js-scope-products');

            var toggleScopeBlocks = function() {
                var scope = scopeSelect.value;
                var showCategory = scope === 'CATEGORY';
                var showProduct = scope === 'PRODUCT';

                categoryWraps.forEach(function(categoryWrap) {
                    categoryWrap.style.display = showCategory ? '' : 'none';
                    var categorySelect = categoryWrap.querySelector('select');
                    if (categorySelect) {
                        categorySelect.disabled = !showCategory;
                        if (showCategory && window.jQuery) {
                            var $categorySelect = window.jQuery(categorySelect);
                            var $categoryContainer = $categorySelect.next('.select2-container');
                            if ($categoryContainer.length) $categoryContainer.css('width', '100%');
                        }
                    }
                });

                productWraps.forEach(function(productWrap) {
                    productWrap.style.display = showProduct ? '' : 'none';
                    var productSelect = productWrap.querySelector('select');
                    if (productSelect) {
                        productSelect.disabled = !showProduct;
                        if (showProduct && window.jQuery) {
                            var $productSelect = window.jQuery(productSelect);
                            var $productContainer = $productSelect.next('.select2-container');
                            if ($productContainer.length) $productContainer.css('width', '100%');
                        }
                    }
                });
            };

            scopeSelect.addEventListener('change', toggleScopeBlocks);
            toggleScopeBlocks();
        })();
    </script>
@endpushonce
