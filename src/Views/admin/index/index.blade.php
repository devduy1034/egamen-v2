@extends('layout')
@section('content')
    @php
        $sessionAdmin = $_SESSION['admin'] ?? null;
        $adminId = is_array($sessionAdmin) ? $sessionAdmin['admin'] ?? null : $sessionAdmin;
        $adminUser = \LARAVEL\Models\UserModel::find($adminId);
    @endphp
    <div class="container-xxl flex-grow-1 container-p-y container-fluid">
        <div class="row">
            <!-- Earning Reports -->
            <div class="mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Bảng điều khiển</h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-sm">
                            <div class="col-xl-3 col-md-6">
                                <a class="my-info-box info-box mb-lg-0"
                                    href="{{ url('admin', ['com' => 'setting', 'act' => 'man', 'type' => 'cau-hinh']) }}"
                                    title="Cấu hình website">
                                    <span class="my-info-box-icon info-box-icon bg-primary"><i
                                            class="ti ti-world"></i></span>
                                    <div class="info-box-content text-dark">
                                        <span class="info-box-text text-capitalize">Cấu hình website</span>
                                        <span class="info-box-number">View more</span>
                                    </div>
                                </a>
                            </div>
                            @if ($adminUser->role == 3)
                                <div class="col-xl-3 col-md-6">
                                    <a class="my-info-box info-box mb-lg-0"
                                        href="{{ url('admin', ['com' => 'user-admin', 'act' => 'man', 'type' => 'tai-khoan']) }}"
                                        title="Tài khoản">
                                        <span class="my-info-box-icon info-box-icon bg-danger"><i
                                                class="ti ti-users"></i></span>
                                        <div class="info-box-content text-dark">
                                            <span class="info-box-text text-capitalize">Tài khoản</span>
                                            <span class="info-box-number">View more</span>
                                        </div>
                                    </a>
                                </div>

                                <div class="col-xl-3 col-md-6">
                                    <a class="my-info-box info-box mb-lg-0"
                                        href="{{ url('admin', ['com' => 'user-admin', 'act' => 'man', 'type' => 'tai-khoan']) }}?changepass=1"
                                        title="Đổi mật khẩu">
                                        <span class="my-info-box-icon info-box-icon bg-success"><i
                                                class="ti ti-key"></i></span>
                                        <div class="info-box-content text-dark">
                                            <span class="info-box-text text-capitalize">Đổi mật khẩu</span>
                                            <span class="info-box-number">View more</span>
                                        </div>
                                    </a>
                                </div>
                            @endif
                            <div class="col-xl-3 col-md-6">
                                <a class="my-info-box info-box mb-lg-0"
                                    href="{{ url('admin', ['com' => 'newsletters', 'act' => 'man', 'type' => 'lien-he']) }}"
                                    title="Thư liên hệ">
                                    <span class="my-info-box-icon info-box-icon bg-info"><i
                                            class="ti ti-address-book"></i></span>
                                    <div class="info-box-content text-dark">
                                        <span class="info-box-text text-capitalize">Thư liên hệ</span>
                                        <span class="info-box-number">View more</span>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @if ($adminUser->role == 3)
                <div class="mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Cấu hình tiện ích</h5>
                        </div>
                        <div class="card-body">
                            <div class="row text-sm">
                                <div class="col-xl-4 col-md-6">
                                    <a class="my-info-box info-box mb-lg-0 bg-primary" href="extensions/man/popup"
                                        title="Cấu hình popup">
                                        <span class="my-info-box-icon  info-box-icon info-box-icon-setting "><i
                                                class="ti ti-device-camera-phone"></i></span>
                                        <div class="info-box-content text-white">
                                            <span class="info-box-text text-capitalize">Cấu hình popup</span>
                                            <span class="info-box-number font-italic">Chi tiết</span>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-xl-4 col-md-6">
                                    <a class="my-info-box info-box mb-lg-0 bg-danger" href="extensions/man/hotline"
                                        title="Dien thoai">
                                        <span class="my-info-box-icon info-box-icon info-box-icon-setting"><i
                                                class="ti ti-phone"></i></span>
                                        <div class="info-box-content text-white">
                                            <span class="info-box-text text-capitalize">Cấu hình điện thoại</span>
                                            <span class="info-box-number font-italic">Chi tiết</span>
                                        </div>
                                    </a>
                                </div>

                                <div class="col-xl-4 col-md-6">
                                    <a class="my-info-box info-box mb-lg-0  bg-success" href="extensions/man/social"
                                        title="Mang xa hoi">
                                        <span class="my-info-box-icon info-box-icon info-box-icon-setting"><i
                                                class="ti ti-social"></i></span>
                                        <div class="info-box-content text-white">
                                            <span class="info-box-text text-capitalize">Cấu hình Mạng xã hội</span>
                                            <span class="info-box-number font-italic">Chi tiết</span>
                                        </div>
                                    </a>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            @endif
            <div class="col-12 mb-4">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                    <div>
                        <h5 class="mb-1">Thống kê nhanh đơn hàng</h5>
                        <small class="text-muted">Cập nhật lúc
                            {{ $quickOrderStats['updated_at'] ?? date('d/m/Y H:i') }}</small>
                    </div>
                    <a class="btn btn-sm btn-primary" href="{{ $orderDashboardUrl }}">Xem danh sách đơn hàng</a>
                </div>
                <div class="row g-4 order-quick-stats">
                    <div class="col-xl-4 col-md-6">
                        <div class="card h-100 order-quick-card">
                            <div class="card-body">
                                <div class="order-quick-card__head">
                                    <span class="order-quick-card__icon bg-label-primary">
                                        <i class="ti ti-shopping-cart"></i>
                                    </span>
                                    <div>
                                        <h5 class="mb-1">Nhịp độ đơn hàng</h5>
                                        <small class="text-muted">Khối lượng phát sinh trong tháng</small>
                                    </div>
                                </div>
                                <div class="order-quick-card__value">
                                    {{ number_format((int) ($quickOrderStats['month'] ?? 0)) }}</div>
                                <div class="order-quick-card__caption">Tổng đơn tháng này</div>
                                <div class="order-quick-list">
                                    <div class="order-quick-list__item">
                                        <span>Hôm nay</span>
                                        <strong>{{ number_format((int) ($quickOrderStats['today'] ?? 0)) }}</strong>
                                    </div>
                                    <div class="order-quick-list__item">
                                        <span>7 ngày gần đây</span>
                                        <strong>{{ number_format((int) ($quickOrderStats['week'] ?? 0)) }}</strong>
                                    </div>
                                    <div class="order-quick-list__item">
                                        <span>Doanh thu tháng</span>
                                        <strong>{{ Func::formatMoney((float) ($quickOrderStats['month_revenue'] ?? 0)) }}</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-4 col-md-6">
                        <div class="card h-100 order-quick-card">
                            <div class="card-body">
                                <div class="order-quick-card__head">
                                    <span class="order-quick-card__icon bg-label-warning">
                                        <i class="ti ti-clock-hour-4"></i>
                                    </span>
                                    <div>
                                        <h5 class="mb-1">Đơn cần xử lý</h5>
                                        <small class="text-muted">Các đơn đang chờ thao tác trong tháng</small>
                                    </div>
                                </div>
                                <div class="order-quick-card__value text-warning">
                                    {{ number_format((int) ($quickOrderStats['need_attention'] ?? 0)) }}
                                </div>
                                <div class="order-quick-card__caption">Tổng đơn cần theo dõi</div>
                                <div class="order-quick-list">
                                    <div class="order-quick-list__item">
                                        <span>Chờ xác nhận</span>
                                        <strong>{{ number_format((int) ($quickOrderStats['pending_confirm'] ?? 0)) }}</strong>
                                    </div>
                                    <div class="order-quick-list__item">
                                        <span>Đang xử lý / đóng gói</span>
                                        <strong>{{ number_format((int) ($quickOrderStats['processing_packing'] ?? 0)) }}</strong>
                                    </div>
                                    <div class="order-quick-list__item">
                                        <span>Đang giao</span>
                                        <strong>{{ number_format((int) ($quickOrderStats['shipping'] ?? 0)) }}</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-4 col-md-12">
                        <div class="card h-100 order-quick-card">
                            <div class="card-body">
                                <div class="order-quick-card__head">
                                    <span class="order-quick-card__icon bg-label-success">
                                        <i class="ti ti-truck-delivery"></i>
                                    </span>
                                    <div>
                                        <h5 class="mb-1">Kết quả giao hàng</h5>
                                        <small class="text-muted">Hiệu suất hoàn tất đơn trong tháng</small>
                                    </div>
                                </div>
                                <div class="order-quick-card__value text-success">
                                    {{ number_format((int) ($quickOrderStats['delivered'] ?? 0)) }}
                                </div>
                                <div class="order-quick-card__caption">Đơn giao thành công</div>
                                <div class="order-quick-list">
                                    <div class="order-quick-list__item">
                                        <span>Đơn đã hủy</span>
                                        <strong>{{ number_format((int) ($quickOrderStats['canceled'] ?? 0)) }}</strong>
                                    </div>
                                    <div class="order-quick-list__item">
                                        <span>Tỷ lệ giao thành công</span>
                                        <strong>{{ number_format((float) ($quickOrderStats['delivery_rate'] ?? 0), 1) }}%</strong>
                                    </div>
                                    <div class="order-quick-list__item">
                                        <span>Chênh lệch so với tháng trước</span>
                                        <strong
                                            class="{{ ((int) ($quickOrderStats['month_diff'] ?? 0)) >= 0 ? 'text-success' : 'text-danger' }}">
                                            {{ ((int) ($quickOrderStats['month_diff'] ?? 0)) >= 0 ? '+' : '' }}{{ number_format((int) ($quickOrderStats['month_diff'] ?? 0)) }}
                                        </strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mb-4">
                <div class="card h-100">
                    <div class="card-header">
                        <div class="d-flex justify-content-between">
                            <h5 class="mb-0">Thống kê truy cập</h5>
                            <small class="text-muted">Tháng {{ date('m/Y', time()) }}</small>
                            @php
                                $counter = Statistic::getCounter();
                            @endphp
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row gy-3 chart-online">
                            <div class="col-md-3 col-6">
                                <div class="d-flex align-items-center">
                                    <div class="badge rounded-pill bg-label-primary me-3 p-2">
                                        <i class="ti ti-chart-bar"></i>
                                    </div>
                                    <div class="card-info">
                                        <h5 class="mb-0">{{ Statistic::getOnline() }}</h5>
                                        <small>Đang online</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="d-flex align-items-center">
                                    <div class="badge rounded-pill bg-label-info me-3 p-2">
                                        <i class="ti ti-chart-bar"></i>
                                    </div>
                                    <div class="card-info">
                                        <h5 class="mb-0">{{ Statistic::getTodayRecord() }}</h5>
                                        <small>Trong ngày</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="d-flex align-items-center">
                                    <div class="badge rounded-pill bg-label-danger me-3 p-2">
                                        <i class="ti ti-chart-bar"></i>
                                    </div>
                                    <div class="card-info">
                                        <h5 class="mb-0">{{ Statistic::getWeekRecord() }}</h5>
                                        <small>Trong tuần</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="d-flex align-items-center">
                                    <div class="badge rounded-pill bg-label-success me-3 p-2">
                                        <i class="ti ti-chart-bar"></i>
                                    </div>
                                    <div class="card-info">
                                        <h5 class="mb-0">{{ Statistic::getMonthRecord() }}</h5>
                                        <small>Trong tháng</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mb-4">
                <div class="card">

                    <div class="card-body">
                        <form class="form-filter-charts row align-items-center mb-1" action="" method="get"
                            name="form-thongke" accept-charset="utf-8">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <select class="form-control select2" name="month" id="month">
                                        <option value="">Chọn tháng</option>
                                        @for ($i = 1; $i <= 12; $i++)
                                            @if (isset($_GET['year']))
                                                {{ $selected = $i == $_GET['month'] ? 'selected' : '' }}
                                            @else
                                                {{ $selected = $i == date('m') ? 'selected' : '' }}
                                            @endif

                                            <option value="{{ $i }}" {{ $selected }}>Tháng
                                                {{ $i }}
                                            </option>
                                        @endfor
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <select class="form-control select2" name="year" id="year">
                                        <option value="">Chọn năm</option>
                                        @for ($i = 2000; $i <= date('Y') + 20; $i++)
                                            @if (isset($_GET['year']))
                                                {{ $selected = $i == $_GET['year'] ? 'selected' : '' }}
                                            @else
                                                {{ $selected = $i == date('Y') ? 'selected' : '' }}
                                            @endif

                                            <option value="{{ $i }}" {{ $selected }}>Năm
                                                {{ $i }}
                                            </option>
                                        @endfor
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group"><button type="submit" class="btn btn-success">Thống
                                        kê</button></div>
                            </div>
                        </form>
                        <div id="apexMixedChart"></div>
                    </div>
                </div>
            </div>


        </div>
    </div>
@endsection
@push('styles')
    <link rel="stylesheet" href="@asset('assets/admin/vendor/libs/apex-charts/apex-charts.css')" />
    <style>
        .order-quick-card {
            border: 1px solid rgba(67, 89, 113, 0.12);
            box-shadow: 0 10px 28px rgba(67, 89, 113, 0.08);
        }

        .order-quick-card__head {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 20px;
        }

        .order-quick-card__icon {
            width: 48px;
            height: 48px;
            border-radius: 16px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            flex: 0 0 48px;
        }

        .order-quick-card__value {
            font-size: 34px;
            font-weight: 700;
            line-height: 1.1;
            color: #566a7f;
        }

        .order-quick-card__caption {
            color: #8592a3;
            font-size: 13px;
            margin-top: 6px;
            margin-bottom: 18px;
        }

        .order-quick-list {
            display: grid;
            gap: 10px;
        }

        .order-quick-list__item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 10px 12px;
            border-radius: 12px;
            background: #f8fafc;
        }

        .order-quick-list__item span {
            color: #6b7280;
            font-size: 13px;
        }

        .order-quick-list__item strong {
            color: #111827;
            font-size: 15px;
            font-weight: 700;
        }
    </style>
@endpush
@push('scripts')
    <script src="@asset('assets/admin/vendor/libs/apex-charts/apexcharts.js')"></script>
    <script>
        if ($('#apexMixedChart').length) {
            var options = {
                colors: [window.templateCustomizer.getColorPrimaryUse()],
                chart: {
                    id: 'apexMixedChart',
                    height: 450,
                    type: 'area',
                    dropShadow: {
                        enabled: true,
                        color: '#000',
                        top: 18,
                        left: 7,
                        blur: 20,
                        opacity: 0.2
                    }
                },
                fill: {
                    type: 'gradient',
                    gradient: {
                        shadeIntensity: 1,
                        inverseColors: false,
                        opacityFrom: 0.8,
                        opacityTo: 0.3,
                        stops: [0, 90, 100]
                    },
                },
                series: [{
                    name: 'Thống kê truy cập tháng ' + CHARTS['month'],
                    type: 'line',
                    data: CHARTS['series']
                }],
                stroke: {
                    curve: 'smooth'
                },
                grid: {
                    borderColor: '#e7e7e7',
                    row: {
                        colors: ['#f3f3f3', 'transparent'],
                        opacity: 0.5
                    }
                },
                markers: {
                    size: 1
                },
                dataLabels: {
                    enabled: false
                },
                labels: CHARTS['labels'],
                legend: {
                    position: 'top',
                    horizontalAlign: 'right',
                    floating: true,
                    offsetY: -25,
                    offsetX: -5
                }
            };
            var apexMixedChart = new ApexCharts(document.querySelector('#apexMixedChart'), options);
            apexMixedChart.render();
            window.addEventListener('storageChanged', (e) => {
                const variableColors = window.templateCustomizer.getColorPrimaryUse();
                apexMixedChart.updateOptions({
                    colors: [variableColors]
                });
            });
        }
    </script>
@endpush
