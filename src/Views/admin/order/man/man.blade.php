@extends('layout')

@section('content')

    <div class="container-xxl flex-grow-1 container-p-y container-fluid">
        <h4>
            <span>Quản lý</span>/<span class="text-muted fw-light"></span>{{ $configMan->title_main }}
        </h4>
        @php
            $orderQuickStats = $orderQuickStats ?? [];
            $quickStatCards = [
                ['key' => 'total_today', 'label' => 'Tổng số đơn hôm nay', 'class' => 'primary', 'icon' => 'ti ti-calendar-event'],
                ['key' => 'total_week', 'label' => 'Tổng số đơn trong tuần', 'class' => 'info', 'icon' => 'ti ti-calendar-stats'],
                ['key' => 'total_month', 'label' => 'Tổng số đơn trong tháng', 'class' => 'success', 'icon' => 'ti ti-calendar-month'],
                ['key' => 'pending_confirm', 'label' => 'Đơn chờ xác nhận', 'class' => 'warning', 'icon' => 'ti ti-hourglass'],
                ['key' => 'processing_packing', 'label' => 'Đơn đang xử lý / đóng gói', 'class' => 'secondary', 'icon' => 'ti ti-package'],
                ['key' => 'shipping', 'label' => 'Đơn đang giao', 'class' => 'info', 'icon' => 'ti ti-truck-delivery'],
                ['key' => 'delivered', 'label' => 'Đơn giao thành công', 'class' => 'success', 'icon' => 'ti ti-circle-check'],
                ['key' => 'canceled', 'label' => 'Đơn đã hủy', 'class' => 'danger', 'icon' => 'ti ti-circle-x'],
            ];
            $monthDiff = (int) ($orderQuickStats['month_diff'] ?? 0);
            $monthDiffClass = $monthDiff > 0 ? 'warning' : ($monthDiff < 0 ? 'success' : 'secondary');
            $monthDiffPrefix = $monthDiff > 0 ? '+' : '';
            $dashboardFilters = $dashboardFilters ?? [];
            $dashboardData = $dashboardData ?? [];
            $dashboardConfig = (array) ($dashboardData['config'] ?? []);
            $dashboardTabsConfig = (array) ($dashboardConfig['tabs'] ?? []);
            $dashboardSectionsConfig = (array) ($dashboardConfig['sections'] ?? []);
            $dashboardEnabled = !empty($dashboardData['enabled']);
            $dashboardDrilldownEnabled = !array_key_exists('drilldown', $dashboardSectionsConfig) || !empty($dashboardSectionsConfig['drilldown']);
            $dashboardTab = (string) ($dashboardFilters['tab'] ?? 'list');
            $dashboardTabs = [
                'list' => 'Danh sách',
                'tong-quan' => 'Tổng quan',
                'san-pham' => 'Sản phẩm',
                'van-chuyen' => 'Vận chuyển',
                'khach-hang' => 'Khách hàng',
            ];
            $isDashboardTabVisible = function ($tabKey) use ($dashboardTabsConfig) {
                if ($tabKey === 'list') {
                    return true;
                }
                return match ($tabKey) {
                    'tong-quan' => !empty($dashboardTabsConfig['overview']),
                    'san-pham' => !empty($dashboardTabsConfig['product']),
                    'van-chuyen' => !empty($dashboardTabsConfig['shipping']),
                    'khach-hang' => !empty($dashboardTabsConfig['customer']),
                    default => false,
                };
            };
            $buildDashboardTabLink = function (string $tabKey) use ($com, $type) {
                $query = request()->query();
                unset($query['page']);
                $query['dashboard_tab'] = $tabKey;
                $url = url('admin', ['com' => $com, 'act' => 'man', 'type' => $type]);
                return !empty($query) ? ($url . '?' . http_build_query($query)) : $url;
            };
        @endphp
        @if ($dashboardEnabled)
            <div class="card mb-3">
                <div class="card-body p-2">
                    <ul class="nav nav-pills dashboard-tab-nav flex-wrap gap-1">
                        @foreach ($dashboardTabs as $tabKey => $tabLabel)
                            @if ($isDashboardTabVisible($tabKey))
                                <li class="nav-item">
                                    <a class="nav-link {{ $dashboardTab === $tabKey ? 'active' : '' }}"
                                        href="{{ $buildDashboardTabLink($tabKey) }}">
                                        {{ $tabLabel }}
                                    </a>
                                </li>
                            @endif
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif
        @if (!$dashboardEnabled || $dashboardTab === 'list')
        <div class="card mb-3 border-warning">
            <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                <h5 class="mb-0">Thống kê nhanh đơn hàng (trong tháng)</h5>
                <div class="d-flex align-items-center gap-2 order-quick-header-badges">
                    <span class="badge bg-label-warning">Cần xử lý ngay: {{ number_format((int) ($orderQuickStats['need_attention'] ?? 0)) }} đơn</span>
                    <span class="badge bg-label-primary">Tỷ lệ giao thành công: {{ number_format((float) ($orderQuickStats['delivery_rate'] ?? 0), 1) }}%</span>
                    <span class="badge bg-label-{{ $monthDiffClass }}">So với tháng trước: {{ $monthDiffPrefix }}{{ number_format($monthDiff) }}</span>
                </div>
                <small class="text-muted">Cập nhật: {{ date('d/m/Y H:i') }}</small>
            </div>
            <div class="card-body">
                <div class="row gy-3">
                    @foreach ($quickStatCards as $statCard)
                        <div class="col-xl-3 col-md-4 col-sm-6">
                            <div class="d-flex align-items-center">
                                <div class="badge rounded-pill bg-label-{{ $statCard['class'] }} me-3 p-2">
                                    <i class="{{ $statCard['icon'] }}"></i>
                                </div>
                                <div class="card-info">
                                    <h5 class="mb-0">{{ number_format((int) ($orderQuickStats[$statCard['key']] ?? 0)) }}</h5>
                                    <small>{{ $statCard['label'] }}</small>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="card pd-15 bg-main mb-3">
            <div class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label mb-1">Tìm kiếm mã đơn hàng</label>
                    <div class="input-group input-group-sm">
                        <input class="form-control text-sm" type="search" id="keyword_code" placeholder="Nhập mã đơn hàng"
                            value="{{ request()->query('keyword_code', '') }}"
                            onkeypress="if(event.key==='Enter'){actionOrderQuickSearch('{{ url('admin', ['com' => $com, 'act' => 'man', 'type' => $type]) }}');}">
                        <button class="btn btn-primary" type="button"
                            onclick="actionOrderQuickSearch('{{ url('admin', ['com' => $com, 'act' => 'man', 'type' => $type]) }}')">
                            <i class="ti ti-search"></i>
                        </button>
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label mb-1">Tìm theo tên / số điện thoại khách</label>
                    <div class="input-group input-group-sm">
                        <input class="form-control text-sm" type="search" id="keyword_customer"
                            placeholder="Nhập tên hoặc số điện thoại"
                            value="{{ request()->query('keyword_customer', request()->query('keyword', '')) }}"
                            onkeypress="if(event.key==='Enter'){actionOrderQuickSearch('{{ url('admin', ['com' => $com, 'act' => 'man', 'type' => $type]) }}');}">
                        <button class="btn btn-primary" type="button"
                            onclick="actionOrderQuickSearch('{{ url('admin', ['com' => $com, 'act' => 'man', 'type' => $type]) }}')">
                            <i class="ti ti-search"></i>
                        </button>
                    </div>
                </div>
                <div class="col-md-4">
                    <a class="btn btn-outline-secondary btn-sm"
                        href="{{ url('admin', ['com' => $com, 'act' => 'man', 'type' => $type]) }}">Xóa tìm kiếm nhanh</a>
                </div>
            </div>
        </div>
        @if (!empty($configMan->search))
            <div class="card card-primary card-outline text-sm mb-3">
                <div class="card-header">
                    <h3 class="card-title">Tìm kiếm đơn hàng</h3>
                </div>
                <div class="card-body row form-group-category">
                    <div class="form-group col-md-3 col-sm-3">
                        <label for="flatpickr-range" class="form-label">Ngày đặt</label>
                        <input type="text" class="form-control" placeholder="DD/MM/YYYY to DD/MM/YYYY" name="order_date"
                            id="flatpickr-range" value="{{ request()->query('order_date', '') }}" />
                    </div>
                    <div class="form-group col-md-3 col-sm-3">
                        <label>Tình trạng:</label>
                        {!! Func::orderStatus() !!}
                    </div>
                    <div class="form-group col-md-3 col-sm-3">
                        <label>Hình thức thanh toán:</label>
                        {!! Func::orderPayments() !!}
                    </div>
                    <div class="form-group col-md-3 col-sm-3">
                        <label>Tỉnh thành:</label>
                        {!! Func::getAjaxPlace('city') !!}
                    </div>

                    <div class="form-group col-md-3 col-sm-3">
                        <label>Phường xã:</label>
                        {!! Func::getAjaxPlace('ward') !!}
                    </div>

                    <div class="form-group col-md-6 col-sm-6">
                        <label>Khoảng giá:</label>
                        <div class="noUi-primary mt-4 mb-5" id="slider-primary"></div>
                        <input type="hidden" name="price_from" class="price_from" id="input-with-keypress-0">
                        <input type="hidden" name="price_to" class="price_to" id="input-with-keypress-1">
                    </div>
                    <div class="form-group text-center mt-2 mb-0 col-12">
                        <a class="btn btn-primary text-white waves-effect waves-light"
                            onclick="actionOrderFilter('{{ url('admin', ['com' => $com, 'act' => 'man', 'type' => $type]) }}')"
                            title="Tìm kiếm"><i class="fas fa-search mr-1"></i>Tìm kiếm</a>
                        <a class="ml-1 btn btn-secondary text-white waves-effect waves-light"
                            href="{{ url('admin', ['com' => $com, 'act' => 'man', 'type' => $type]) }}"
                            title="Hủy lọc"><i class="fas fa-times mr-1"></i>Hủy lọc</a>
                    </div>
                </div>
            </div>
        @endif

        <div class="card mb-3">
            <div class="card-datatable table-responsive">
                <table class="datatables-category-list table border-top text-sm">
                    <thead>
                        <tr>
                            <th class="align-middle w-[60px]">
                                <div class="custom-control custom-checkbox my-checkbox">
                                    <input type="checkbox"
                                        {{ !isPermissions(str_replace('-', '.', $com) . '.' . $type . '.delete') ? 'disabled' : '' }}
                                        class="form-check-input" id="selectall-checkbox">
                                </div>
                            </th>
                            <th class="text-center w-[70px] !pl-0">STT</th>
                            <th class="text-left">Mã</th>
                            <th class="text-left">Họ tên</th>
                            <th class="text-left">Ngày đặt</th>
                            <th class="text-left">HT thanh toán</th>
                            <th class="text-center">Tổng giá</th>
                            <th class="text-left">Tình trạng</th>
                            <th class="text-lg-center text-center">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $k => $v)
                            <tr>
                                <td class="align-middle">
                                    <div class="custom-control custom-checkbox my-checkbox">
                                        <input type="checkbox"
                                            {{ !isPermissions(str_replace('-', '.', $com) . '.' . $type . '.delete') ? 'disabled' : '' }}
                                            class="form-check-input" id="select-checkbox1" value="{{ $v['id'] }}">
                                    </div>
                                </td>
                                <td class="align-left w-[70px] !pl-0">
                                    @component('component.inputNumb', ['numb' => $v['numb'], 'idtbl' => $v['id'], 'table' => 'orders'])
                                    @endcomponent
                                </td>
                                <td class="align-left">
                                    @component('component.name', ['name' => $v['code'], 'params' => ['id' => $v['id']]])
                                    @endcomponent
                                    @if (!empty($v['is_new_order']))
                                        <span class="badge bg-danger ms-2">NEW</span>
                                    @endif
                                </td>
                                <td class="align-left">
                                    <a class="text-dark text-break">{{ $v['fullname'] }}</a>
                                </td>
                                <td class="align-left">
                                    <a
                                        class="text-dark text-break">{{ $v['created_at_display'] ?? '-' }}</a>
                                </td>
                                <td class="align-left">
                                    <a
                                        class="text-dark text-break">{{ Func::showName('news', $v['order_payment'], 'namevi') }}</a>
                                </td>
                                <td class="align-middle text-center">
                                    <a class="text-dark text-break">{{ Func::formatMoney($v['total_price']) }}</a>
                                </td>
                                <td class="align-right">
                                    <a
                                        class="text-{{ Func::showName('order_status', $v['order_status'], 'class_order') }} py-1 px-2 fs-6 rounded-1 bg-{{ Func::showName('order_status', $v['order_status'], 'class_order') }}-bg-subtle">{{ $v['order_status_name'] ?? Func::showName('order_status', $v['order_status'], 'namevi') }}</a>
                                </td>
                                <td class="align-middle text-center">
                                    @component('component.buttonList', ['params' => ['id' => $v['id']]])
                                        @if (!empty($configMan->excel))
                                            <a class="text-success mr-2 {{ !isPermissions(str_replace('-', '.', $com . '-excel') . '.' . $type . '.man') ? 'disabled' : '' }}"
                                                href="{{ url('admin', ['com' => 'order-excel', 'act' => 'man', 'type' => $type], ['id' => $v['id']]) }}"><i
                                                    class="ti ti-file-spreadsheet" data-bs-toggle="tooltip"
                                                    data-bs-trigger="hover" data-bs-placement="bottom"
                                                    title="Xuất file excel"></i></a>
                                        @endif
                                    @endcomponent
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="100" class="text-center">Không có dữ liệu</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {!! $items->appends(request()->query())->links() !!}
        @component('component.buttonMan')
        @endcomponent
        @else
            @include('order.man.dashboard')
        @endif
    </div>
@endsection
@pushonce('styles')
    <link rel="stylesheet" href="@asset('assets/admin/vendor/libs/nouislider/nouislider.css')" />
    <link rel="stylesheet" href="@asset('assets/admin/vendor/libs/flatpickr/flatpickr.css')" />
    <link rel="stylesheet" href="@asset('assets/admin/vendor/libs/apex-charts/apex-charts.css')" />
    <style>
        .dashboard-kpi-link {
            text-decoration: none;
            color: inherit;
        }

        .dashboard-kpi-link:hover {
            color: inherit;
        }

        .dashboard-tab-nav .nav-link {
            padding: 0.42rem 0.95rem;
            border-radius: 999px;
            border: 1px solid transparent;
            background: #f5f8ff;
            color: #5f6879;
            font-weight: 600;
            transition: all 0.2s ease;
        }

        .dashboard-tab-nav .nav-link:hover {
            color: #0d6efd;
            border-color: #d8e5ff;
            background: #eef4ff;
            transform: translateY(-1px);
        }

        .dashboard-tab-nav .nav-link.active {
            color: #fff;
            background: linear-gradient(90deg, #0d6efd 0%, #3d8bfd 100%);
            box-shadow: 0 8px 18px rgba(13, 110, 253, 0.28);
        }

        .order-dashboard .card {
            border: 1px solid #edf1f7;
            box-shadow: 0 8px 20px rgba(15, 23, 42, 0.05);
            transition: box-shadow 0.2s ease, transform 0.2s ease;
        }

        .order-dashboard .card:hover {
            box-shadow: 0 12px 28px rgba(15, 23, 42, 0.08);
            transform: translateY(-1px);
        }

        .order-dashboard .card-header {
            border-bottom: 1px solid #edf1f7;
            background: linear-gradient(180deg, #fbfcff 0%, #f6f9ff 100%);
        }

        .order-dashboard .card-header small {
            display: block;
            margin-top: 0.15rem;
        }

        .order-dashboard .dashboard-kpi-link .card-body {
            position: relative;
            overflow: hidden;
        }

        .order-dashboard .dashboard-kpi-link .card-body::after {
            content: '';
            position: absolute;
            right: -28px;
            bottom: -28px;
            width: 92px;
            height: 92px;
            border-radius: 50%;
            background: rgba(13, 110, 253, 0.08);
        }

        .order-dashboard .dashboard-kpi-link h5,
        .order-dashboard .dashboard-kpi-link h6 {
            position: relative;
            z-index: 1;
            font-weight: 700;
            letter-spacing: 0.2px;
        }

        .order-dashboard .table > tbody > tr:hover {
            background: #f8fbff;
        }

        .order-dashboard .alert {
            border: 1px solid rgba(0, 0, 0, 0.06);
        }

        .order-quick-header-badges {
            flex-wrap: nowrap;
            overflow-x: auto;
        }

        .order-quick-header-badges .badge {
            white-space: nowrap;
        }

        .order-quick-header-badges::-webkit-scrollbar {
            height: 4px;
        }

        @media (max-width: 991.98px) {
            .dashboard-tab-nav .nav-link {
                padding: 0.38rem 0.75rem;
                font-size: 0.875rem;
            }

            .order-dashboard .card {
                box-shadow: 0 6px 14px rgba(15, 23, 42, 0.05);
            }
        }
    </style>
@endpushonce
@pushonce('scripts')
    <script src="@asset('assets/admin/vendor/libs/flatpickr/flatpickr.js')"></script>
    <script src="@asset('assets/admin/vendor/libs/nouislider/nouislider.js')"></script>
    <script src="@asset('assets/admin/vendor/libs/apex-charts/apexcharts.js')"></script>
    {{-- <script>
        var input0 = document.getElementById('input-with-keypress-0');
        var input1 = document.getElementById('input-with-keypress-1');
        var inputs = [input0, input1];
        const flatpickrRange = document.querySelector('#flatpickr-range');
        const sliderPrimary = document.getElementById('slider-primary'),
            colorOptions = {
                start: [{
                    {
                        request() - > query('price_from') ?? \DTSCORE\ Models\ OrdersModel::min('total_price')
                    }
                }, {
                    {
                        request() - > query('price_to') ?? \DTSCORE\ Models\ OrdersModel::max('total_price')
                    }
                }],
                connect: true,
                step: 1000,
                behaviour: 'tap-drag',
                tooltips: {
                    to: function(numericValue) {
                        if (numericValue.toFixed(0) >= 1000000) {
                            return `${numericValue.toFixed(0) / 1000000}M`;
                        } else {
                            if (numericValue.toFixed(0) == 0) return '0';
                            else return (numericValue.toFixed(0) / 1000) + 'k';
                        }
                    }
                },
                range: {
                    min: {
                        {
                            \
                            DTSCORE\ Models\ OrdersModel::min('total_price')
                        }
                    },
                    max: {
                        {
                            \
                            DTSCORE\ Models\ OrdersModel::max('total_price')
                        }
                    }
                },
                direction: isRtl ? 'rtl' : 'ltr'
            };
        if (sliderPrimary) {
            noUiSlider.create(sliderPrimary, colorOptions);
            sliderPrimary.noUiSlider.on('update', function(values, handle) {
                inputs[handle].value = parseFloat(values[handle]);
            });
        }
        if (typeof flatpickrRange != undefined) {
            flatpickrRange.flatpickr({
                dateFormat: 'd/m/Y',
                mode: 'range',
            });
        }

        function actionOrder(url) {
            var listid = '';
            var order_status = parseInt($('#order_status').val());
            var order_payment = parseInt($('#order_payment').val());
            var order_date = $('#flatpickr-range').val();
            var price_from = $('#input-with-keypress-0').val();
            var price_to = $('#input-with-keypress-1').val();
            var city = parseInt($('#id_city').val());
            var district = parseInt($('#id_district').val());
            var ward = parseInt($('#id_ward').val());
            var keyword = $('#keyword').val();
            $('input.select-checkbox').each(function() {
                if (this.checked) listid = listid + ',' + this.value;
            });
            listid = listid.substr(1);
            url += '?search=' + COM;
            if (listid) url += '&listid=' + listid;
            if (order_status) url += '&order_status=' + order_status;
            if (order_payment) url += '&order_payment=' + order_payment;
            if (order_date) url += '&order_date=' + order_date;
            if (price_from) url += '&price_from=' + price_from;
            if (price_to) url += '&price_to=' + price_to;
            if (city) url += '&id_city=' + city;
            if (district) url += '&id_district=' + district;
            if (ward) url += '&id_ward=' + ward;
            if (keyword) url += '&keyword=' + encodeURI(keyword);
            window.location = url;
        }
    </script> --}}
    <script>
        var input0 = document.getElementById('input-with-keypress-0');
        var input1 = document.getElementById('input-with-keypress-1');
        var inputs = [input0, input1];
        const flatpickrRange = document.querySelector('#flatpickr-range');
        const sliderPrimary = document.getElementById('slider-primary');

        var minPrice = Number({{ \LARAVEL\Models\OrdersModel::min('total_price') ?? 0 }}) || 0;
        var maxPrice = Number({{ \LARAVEL\Models\OrdersModel::max('total_price') ?? 1000000 }}) || 0;
        if (maxPrice <= minPrice) maxPrice = minPrice + 1000;

        var priceFrom = Number('{{ request()->query('price_from', '') }}'.replace(/,/g, ''));
        var priceTo = Number('{{ request()->query('price_to', '') }}'.replace(/,/g, ''));
        if (!Number.isFinite(priceFrom) || priceFrom <= 0) priceFrom = minPrice;
        if (!Number.isFinite(priceTo) || priceTo <= 0) priceTo = maxPrice;
        if (priceFrom > priceTo) {
            var tempPrice = priceFrom;
            priceFrom = priceTo;
            priceTo = tempPrice;
        }

        const colorOptions = {
            start: [priceFrom, priceTo],
            connect: true,
            step: 1000,
            behaviour: 'tap-drag',
            tooltips: {
                to: function(numericValue) {
                    if (numericValue.toFixed(0) >= 1000000) {
                        return `${numericValue.toFixed(0) / 1000000}M`;
                    } else {
                        if (numericValue.toFixed(0) == 0) return '0';
                        else return (numericValue.toFixed(0) / 1000) + 'k';
                    }
                }
            },
            range: {
                min: minPrice,
                max: maxPrice
            },
            direction: (typeof isRtl !== 'undefined' && isRtl) ? 'rtl' : 'ltr'
        };

        if (sliderPrimary && input0 && input1) {
            noUiSlider.create(sliderPrimary, colorOptions);
            sliderPrimary.noUiSlider.on('update', function(values, handle) {
                inputs[handle].value = parseFloat(values[handle]);
            });
        }

        if (flatpickrRange && typeof flatpickr === 'function') {
            var selectedOrderDate = String(flatpickrRange.value || '').trim();
            flatpickrRange.flatpickr({
                dateFormat: 'd/m/Y',
                mode: 'range',
                defaultDate: selectedOrderDate ? selectedOrderDate.split(' to ') : undefined
            });
        }

        function actionOrderQuickSearch(url) {
            var params = new URLSearchParams(window.location.search || '');
            var keyword_code = String($('#keyword_code').val() || '').trim();
            var keyword_customer = String($('#keyword_customer').val() || '').trim();

            params.delete('keyword');
            if (keyword_code) params.set('keyword_code', keyword_code);
            else params.delete('keyword_code');
            if (keyword_customer) params.set('keyword_customer', keyword_customer);
            else params.delete('keyword_customer');

            var query = params.toString();
            window.location = query ? (url + '?' + query) : url;
        }

        function actionOrderFilter(url) {
            var params = new URLSearchParams();

            var order_status = parseInt($('#order_status').val() || 0, 10) || 0;
            var order_payment = parseInt($('#order_payment').val() || 0, 10) || 0;
            var order_date = String($('#flatpickr-range').val() || '').trim();
            var price_from = String($('#input-with-keypress-0').val() || '').trim();
            var price_to = String($('#input-with-keypress-1').val() || '').trim();
            var city = parseInt($('#id_city').val() || 0, 10) || 0;
            var district = parseInt($('#id_district').val() || 0, 10) || 0;
            var ward = parseInt($('#id_ward').val() || 0, 10) || 0;
            var keyword_code = String($('#keyword_code').val() || '').trim();
            var keyword_customer = String($('#keyword_customer').val() || '').trim();
            var keywordInput = document.getElementById('keyword');
            var keyword = keywordInput ? String(keywordInput.value || '').trim() : '';

            if (order_status > 0) params.set('order_status', order_status);
            if (order_payment > 0) params.set('order_payment', order_payment);
            if (order_date) params.set('order_date', order_date);
            if (price_from) params.set('price_from', price_from);
            if (price_to) params.set('price_to', price_to);
            if (city > 0) params.set('id_city', city);
            if (district > 0) params.set('id_district', district);
            if (ward > 0) params.set('id_ward', ward);
            if (keyword_code) params.set('keyword_code', keyword_code);
            if (keyword_customer) params.set('keyword_customer', keyword_customer);
            if (!keyword_code && !keyword_customer && keyword) params.set('keyword', keyword);

            var query = params.toString();
            window.location = query ? (url + '?' + query) : url;
        }

        var dashboardRange = document.getElementById('dashboard-range');
        var dashboardDateWrap = document.getElementById('dashboard-date-wrap');
        var dashboardDateInput = document.getElementById('dashboard-date-range');
        var dashboardFilterRange = @json($dashboardFilters['range'] ?? '30d');
        var dashboardFilterMonth = @json($dashboardFilters['dashboard_month'] ?? date('Y-m'));
        var dashboardFilterYear = @json($dashboardFilters['dashboard_year'] ?? date('Y'));
        var dashboardReturnQuery = document.getElementById('dashboard-return-query');
        var dashboardCharts = @json($dashboardData['charts'] ?? []);
        var dashboardDrilldownEnabled = {{ $dashboardDrilldownEnabled ? 'true' : 'false' }};
        var dashboardDrilldownBase = @json($dashboardData['drilldown_base'] ?? []);
        var dashboardOrderBaseUrl = {!! json_encode(url('admin', ['com' => $com, 'act' => 'man', 'type' => $type])) !!};

        function buildDashboardDrilldownUrl(extraParams) {
            var params = new URLSearchParams();
            var baseParams = dashboardDrilldownBase || {};
            Object.keys(baseParams).forEach(function(key) {
                var value = baseParams[key];
                if (value !== undefined && value !== null && String(value) !== '') {
                    params.set(key, String(value));
                }
            });

            var appendParams = extraParams || {};
            Object.keys(appendParams).forEach(function(key) {
                var value = appendParams[key];
                if (value === undefined || value === null || String(value) === '') {
                    params.delete(key);
                } else {
                    params.set(key, String(value));
                }
            });

            params.set('dashboard_tab', 'list');
            var query = params.toString();
            return query ? (dashboardOrderBaseUrl + '?' + query) : dashboardOrderBaseUrl;
        }

        if (dashboardReturnQuery) {
            dashboardReturnQuery.value = (window.location.search || '').replace(/^\?/, '');
        }

        function ensureDashboardRangeOption(value, label) {
            if (!dashboardRange) return;
            if (dashboardRange.querySelector('option[value="' + value + '"]')) return;
            var option = document.createElement('option');
            option.value = value;
            option.textContent = label;
            dashboardRange.appendChild(option);
        }

        function buildDashboardExtraInput(id, name, label, type, value) {
            if (!dashboardDateWrap || !dashboardDateWrap.parentNode) return null;
            var wrap = document.getElementById(id + '-wrap');
            if (wrap) return wrap;

            wrap = document.createElement('div');
            wrap.className = 'col-md-2 d-none';
            wrap.id = id + '-wrap';

            var labelEl = document.createElement('label');
            labelEl.className = 'form-label mb-1';
            labelEl.setAttribute('for', id);
            labelEl.textContent = label;

            var input = document.createElement('input');
            input.type = type;
            input.className = 'form-control form-control-sm';
            input.id = id;
            input.name = name;
            input.value = value || '';

            if (type === 'number') {
                input.min = '2000';
                input.max = String(new Date().getFullYear() + 5);
                input.step = '1';
            }

            wrap.appendChild(labelEl);
            wrap.appendChild(input);
            dashboardDateWrap.insertAdjacentElement('afterend', wrap);
            return wrap;
        }

        ensureDashboardRangeOption('month', 'Theo tháng');
        ensureDashboardRangeOption('year', 'Theo năm');

        if (dashboardRange && dashboardFilterRange) {
            dashboardRange.value = dashboardFilterRange;
        }

        var dashboardMonthWrap = buildDashboardExtraInput(
            'dashboard-month',
            'dashboard_month',
            'Tháng',
            'month',
            dashboardFilterMonth
        );
        var dashboardYearWrap = buildDashboardExtraInput(
            'dashboard-year',
            'dashboard_year',
            'Năm',
            'number',
            dashboardFilterYear
        );

        function toggleDashboardDateRange() {
            if (!dashboardDateWrap || !dashboardRange) return;
            var isCustom = dashboardRange.value === 'custom';
            var isMonth = dashboardRange.value === 'month';
            var isYear = dashboardRange.value === 'year';

            dashboardDateWrap.classList.toggle('d-none', !isCustom);
            if (dashboardMonthWrap) dashboardMonthWrap.classList.toggle('d-none', !isMonth);
            if (dashboardYearWrap) dashboardYearWrap.classList.toggle('d-none', !isYear);
        }

        if (dashboardRange) {
            dashboardRange.addEventListener('change', toggleDashboardDateRange);
            toggleDashboardDateRange();
        }

        if (dashboardDateInput && typeof flatpickr === 'function') {
            flatpickr(dashboardDateInput, {
                dateFormat: 'd/m/Y',
                mode: 'range'
            });
        }

        if (typeof ApexCharts === 'function') {
            var revenueEl = document.querySelector('#dashboardRevenueChart');
            if (revenueEl && dashboardCharts.line) {
                var revenueOptions = {
                    chart: {
                        type: 'line',
                        height: 320,
                        toolbar: { show: false },
                        events: {
                            dataPointSelection: function(event, chartContext, config) {
                                if (!dashboardDrilldownEnabled) return;
                                var index = Number(config.dataPointIndex || -1);
                                var dateKeys = (dashboardCharts.line && dashboardCharts.line.date_keys) ? dashboardCharts.line.date_keys : [];
                                if (index >= 0 && dateKeys[index]) {
                                    window.location = buildDashboardDrilldownUrl({ order_date: dateKeys[index] });
                                }
                            }
                        }
                    },
                    series: [
                        {
                            name: 'Doanh thu',
                            data: dashboardCharts.line.revenue_series || []
                        },
                        {
                            name: 'Số đơn',
                            data: dashboardCharts.line.order_series || []
                        }
                    ],
                    stroke: {
                        width: [3, 2],
                        curve: 'smooth'
                    },
                    xaxis: {
                        categories: dashboardCharts.line.labels || []
                    },
                    yaxis: [
                        {
                            labels: {
                                formatter: function(value) {
                                    return value.toLocaleString('vi-VN');
                                }
                            }
                        },
                        {
                            opposite: true,
                            labels: {
                                formatter: function(value) {
                                    return Math.round(value);
                                }
                            }
                        }
                    ],
                    dataLabels: {
                        enabled: false
                    },
                    tooltip: {
                        y: {
                            formatter: function(value, ctx) {
                                if ((ctx.seriesIndex || 0) === 0) return value.toLocaleString('vi-VN') + ' đ';
                                return Math.round(value) + ' đơn';
                            }
                        }
                    }
                };
                new ApexCharts(revenueEl, revenueOptions).render();
            }

            var funnelEl = document.querySelector('#dashboardFunnelChart');
            if (funnelEl && dashboardCharts.funnel) {
                var funnelOptions = {
                    chart: {
                        type: 'bar',
                        height: 320,
                        toolbar: { show: false },
                        events: {
                            dataPointSelection: function(event, chartContext, config) {
                                if (!dashboardDrilldownEnabled) return;
                                var index = Number(config.dataPointIndex || -1);
                                var statusIds = (dashboardCharts.funnel && dashboardCharts.funnel.status_ids) ? dashboardCharts.funnel.status_ids : [];
                                var statusId = Number(statusIds[index] || 0);
                                window.location = buildDashboardDrilldownUrl({
                                    order_status: statusId > 0 ? statusId : ''
                                });
                            }
                        }
                    },
                    plotOptions: {
                        bar: {
                            horizontal: true,
                            borderRadius: 4
                        }
                    },
                    series: [{
                        name: 'Số đơn',
                        data: dashboardCharts.funnel.series || []
                    }],
                    xaxis: {
                        categories: dashboardCharts.funnel.labels || []
                    },
                    dataLabels: {
                        enabled: true
                    }
                };
                new ApexCharts(funnelEl, funnelOptions).render();
            }

            var statusEl = document.querySelector('#dashboardStatusChart');
            if (statusEl && dashboardCharts.status) {
                var statusOptions = {
                    chart: {
                        type: 'donut',
                        height: 300,
                        events: {
                            dataPointSelection: function(event, chartContext, config) {
                                if (!dashboardDrilldownEnabled) return;
                                var index = Number(config.dataPointIndex || -1);
                                var statusIds = (dashboardCharts.status && dashboardCharts.status.ids) ? dashboardCharts.status.ids : [];
                                var statusId = Number(statusIds[index] || 0);
                                window.location = buildDashboardDrilldownUrl({
                                    order_status: statusId > 0 ? statusId : ''
                                });
                            }
                        }
                    },
                    labels: dashboardCharts.status.labels || [],
                    series: dashboardCharts.status.series || [],
                    legend: {
                        position: 'bottom'
                    },
                    dataLabels: {
                        enabled: true
                    }
                };
                new ApexCharts(statusEl, statusOptions).render();
            }
        }
    </script>
@endpushonce
