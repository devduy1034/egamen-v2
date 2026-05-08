@php
    $dashboardFilters = is_array($dashboardFilters ?? null) ? $dashboardFilters : [];
    $dashboardTab = (string) ($dashboardTab ?? ($dashboardFilters['tab'] ?? 'tong-quan'));

    $dashboardSummary = (array) ($dashboardData['summary'] ?? []);
    $dashboardAlerts = (array) ($dashboardData['alerts'] ?? []);
    $dashboardProduct = (array) ($dashboardData['product'] ?? []);
    $dashboardShipping = (array) ($dashboardData['shipping'] ?? []);
    $dashboardCustomers = (array) ($dashboardData['customers'] ?? []);
    $dashboardStatusOptions = (array) ($dashboardData['status_options'] ?? []);
    $dashboardStatusGroups = (array) ($dashboardData['status_groups'] ?? []);
    $dashboardTargets = (array) ($dashboardSummary['targets'] ?? ($dashboardData['targets'] ?? []));
    $dashboardSections = (array) ($dashboardData['config']['sections'] ?? []);

    $dashboardSectionHeader =
        !array_key_exists('header_filter', $dashboardSections) || !empty($dashboardSections['header_filter']);
    $dashboardSectionKpi =
        !array_key_exists('kpi_cards', $dashboardSections) || !empty($dashboardSections['kpi_cards']);
    $dashboardSectionCharts =
        !array_key_exists('core_charts', $dashboardSections) || !empty($dashboardSections['core_charts']);
    $dashboardSectionAi =
        !array_key_exists('ai_actions', $dashboardSections) || !empty($dashboardSections['ai_actions']);
    $dashboardSectionDrilldown =
        !array_key_exists('drilldown', $dashboardSections) || !empty($dashboardSections['drilldown']);

    $canEditDashboardTarget = function_exists('isPermissions') ? isPermissions('setting.cau-hinh.edit') : true;
    $buildDashboardListLink = function (array $extraQuery = []) use ($com, $type, $dashboardData) {
        $query = (array) ($dashboardData['drilldown_base'] ?? []);
        foreach ($extraQuery as $queryKey => $queryValue) {
            if ($queryValue === '' || $queryValue === null) {
                unset($query[$queryKey]);
            } else {
                $query[$queryKey] = $queryValue;
            }
        }
        $url = url('admin', ['com' => $com, 'act' => 'man', 'type' => $type]);
        return !empty($query) ? $url . '?' . http_build_query($query) : $url;
    };

    $deliveredStatusId = (int) ($dashboardStatusGroups['delivered'][0] ?? 0);
    $canceledStatusId = (int) ($dashboardStatusGroups['canceled'][0] ?? 0);
    $orderDashboardBaseUrl = url('admin', ['com' => $com, 'act' => 'man', 'type' => $type]);
@endphp

<div class="order-dashboard">
@if ($dashboardSectionHeader)
    <div class="card mb-3">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-xl-8">
                    <form method="get" action="{{ $orderDashboardBaseUrl }}" class="row g-2 align-items-end">
                        <input type="hidden" name="dashboard_tab" value="{{ $dashboardTab }}">
                        <div class="col-md-3">
                            <label class="form-label mb-1">Thời gian</label>
                            <select class="form-select form-select-sm" id="dashboard-range" name="dashboard_range">
                                <option value="today"
                                    {{ ($dashboardFilters['range'] ?? '') === 'today' ? 'selected' : '' }}>Hôm nay
                                </option>
                                <option value="7d"
                                    {{ ($dashboardFilters['range'] ?? '') === '7d' ? 'selected' : '' }}>7 ngày</option>
                                <option value="30d"
                                    {{ ($dashboardFilters['range'] ?? '') === '30d' ? 'selected' : '' }}>30 ngày
                                </option>
                                <option value="custom"
                                    {{ ($dashboardFilters['range'] ?? '') === 'custom' ? 'selected' : '' }}>Tùy chọn
                                </option>
                            </select>
                        </div>
                        <div class="col-md-4" id="dashboard-date-wrap">
                            <label class="form-label mb-1">Khoảng ngày</label>
                            <input type="text" class="form-control form-control-sm" id="dashboard-date-range"
                                name="dashboard_date" placeholder="DD/MM/YYYY to DD/MM/YYYY"
                                value="{{ $dashboardFilters['dashboard_date'] ?? '' }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label mb-1">Trạng thái</label>
                            <select class="form-select form-select-sm" name="dashboard_status">
                                <option value="0">Tất cả</option>
                                @foreach ($dashboardStatusOptions as $statusOption)
                                    <option value="{{ (int) ($statusOption['id'] ?? 0) }}"
                                        {{ (int) ($dashboardFilters['status'] ?? 0) === (int) ($statusOption['id'] ?? 0) ? 'selected' : '' }}>
                                        {{ $statusOption['name'] ?? '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label mb-1">Kênh bán</label>
                            <select class="form-select form-select-sm" name="dashboard_channel">
                                <option value="website" selected>Website</option>
                            </select>
                        </div>
                        <div class="col-12 d-flex flex-wrap gap-2">
                            <button type="submit" class="btn btn-primary btn-sm">Áp dụng bộ lọc</button>
                            <a class="btn btn-outline-secondary btn-sm"
                                href="{{ $orderDashboardBaseUrl }}?{{ http_build_query(['dashboard_tab' => $dashboardTab, 'dashboard_range' => '30d', 'dashboard_channel' => 'website']) }}">
                                Đặt lại
                            </a>
                        </div>
                    </form>
                </div>
                <div class="col-xl-4">
                    <form method="post" action="{{ $orderDashboardBaseUrl }}" class="row g-2 align-items-end">
                        <input type="hidden" name="csrf_token" value="{{ csrf_token() }}">
                        <input type="hidden" name="dashboard_target_submit" value="1">
                        <input type="hidden" name="dashboard_return_query" id="dashboard-return-query"
                            value="{{ request()->getQueryString() }}">
                        <div class="col-12">
                            <h6 class="mb-1">Mục tiêu tháng</h6>
                        </div>
                        <div class="col-4">
                            <label class="form-label mb-1">Tháng</label>
                            <input type="month" class="form-control form-control-sm" name="dashboard_target_month"
                                value="{{ $dashboardTargets['month'] ?? date('Y-m') }}"
                                {{ !$canEditDashboardTarget ? 'disabled' : '' }}>
                        </div>
                        <div class="col-4">
                            <label class="form-label mb-1">Doanh thu</label>
                            <input type="number" min="0" step="1000" class="form-control form-control-sm"
                                name="dashboard_target_revenue"
                                value="{{ (int) ($dashboardTargets['revenue_target'] ?? 0) }}"
                                {{ !$canEditDashboardTarget ? 'disabled' : '' }}>
                        </div>
                        <div class="col-4">
                            <label class="form-label mb-1">Số đơn</label>
                            <input type="number" min="0" step="1" class="form-control form-control-sm"
                                name="dashboard_target_orders"
                                value="{{ (int) ($dashboardTargets['orders_target'] ?? 0) }}"
                                {{ !$canEditDashboardTarget ? 'disabled' : '' }}>
                        </div>
                        <div class="col-12 d-flex justify-content-end">
                            <button type="submit" class="btn btn-warning btn-sm"
                                {{ !$canEditDashboardTarget ? 'disabled' : '' }}>
                                Lưu mục tiêu
                            </button>
                        </div>
                        @if (!$canEditDashboardTarget)
                            <div class="col-12">
                                <small class="text-muted">Bạn cần quyền cấu hình để cập nhật mục tiêu tháng.</small>
                            </div>
                        @endif
                    </form>
                </div>
            </div>
        </div>
    </div>
@endif

@if ($dashboardTab === 'tong-quan')
    @if ($dashboardSectionKpi)
        <div class="row g-3 mb-3">
            <div class="col-xl-3 col-md-6">
                <a class="card dashboard-kpi-link h-100 {{ !$dashboardSectionDrilldown ? 'pe-none' : '' }}"
                    href="{{ $dashboardSectionDrilldown ? $buildDashboardListLink() : 'javascript:void(0);' }}">
                    <div class="card-body">
                        <small class="text-muted d-block mb-1">Doanh thu</small>
                        <h5 class="mb-0">{{ Func::formatMoney((float) ($dashboardSummary['revenue'] ?? 0)) }}</h5>
                    </div>
                </a>
            </div>
            <div class="col-xl-3 col-md-6">
                <a class="card dashboard-kpi-link h-100 {{ !$dashboardSectionDrilldown ? 'pe-none' : '' }}"
                    href="{{ $dashboardSectionDrilldown ? $buildDashboardListLink() : 'javascript:void(0);' }}">
                    <div class="card-body">
                        <small class="text-muted d-block mb-1">Số đơn</small>
                        <h5 class="mb-0">{{ number_format((int) ($dashboardSummary['orders'] ?? 0)) }}</h5>
                    </div>
                </a>
            </div>
            <div class="col-xl-2 col-md-6">
                <div class="card h-100">
                    <div class="card-body">
                        <small class="text-muted d-block mb-1">AOV</small>
                        <h5 class="mb-0">{{ Func::formatMoney((float) ($dashboardSummary['aov'] ?? 0)) }}</h5>
                    </div>
                </div>
            </div>
            <div class="col-xl-2 col-md-6">
                <a class="card dashboard-kpi-link h-100 {{ !$dashboardSectionDrilldown ? 'pe-none' : '' }}"
                    href="{{ $dashboardSectionDrilldown ? $buildDashboardListLink(['order_status' => $deliveredStatusId > 0 ? $deliveredStatusId : null]) : 'javascript:void(0);' }}">
                    <div class="card-body">
                        <small class="text-muted d-block mb-1">% Đã giao / % Hủy</small>
                        <h6 class="mb-1 text-success">
                            {{ number_format((float) ($dashboardSummary['delivered_rate'] ?? 0), 1) }}%</h6>
                        <small
                            class="text-danger">{{ number_format((float) ($dashboardSummary['canceled_rate'] ?? 0), 1) }}%</small>
                    </div>
                </a>
            </div>
            <div class="col-xl-2 col-md-6">
                <a class="card dashboard-kpi-link h-100 {{ !$dashboardSectionDrilldown ? 'pe-none' : '' }}"
                    href="{{ $dashboardSectionDrilldown ? $buildDashboardListLink(['order_status' => $canceledStatusId > 0 ? $canceledStatusId : null]) : 'javascript:void(0);' }}">
                    <div class="card-body">
                        <small class="text-muted d-block mb-1">Phí ship / Thất bại</small>
                        <h6 class="mb-1">{{ Func::formatMoney((float) ($dashboardSummary['ship_total'] ?? 0)) }}
                        </h6>
                        <small
                            class="text-warning">{{ number_format((float) ($dashboardSummary['failed_shipping_rate'] ?? 0), 1) }}%</small>
                    </div>
                </a>
            </div>
        </div>

        <div class="card mb-3 border-warning">
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-md-4">
                        <small class="text-muted">Mục tiêu doanh thu
                            {{ $dashboardTargets['month_label'] ?? '' }}</small>
                    </div>
                    <div class="col-md-8 text-md-end">
                        <span class="badge bg-label-primary me-1">Hiện tại:
                            {{ number_format((float) ($dashboardTargets['current_revenue_rate'] ?? 0), 1) }}%</span>
                        <span class="badge bg-label-warning">Dự báo cuối tháng:
                            {{ number_format((float) ($dashboardTargets['forecast_revenue_rate'] ?? 0), 1) }}%</span>
                            </div>
                            <div class="col-12">
                                <small class="text-muted">Ghi chú AI: Dự báo đang dùng AI rule-based theo run-rate dữ liệu hiện tại.</small>
                            </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if ($dashboardSectionCharts)
        <div class="row g-3 mb-3">
            <div class="col-xl-8">
                <div class="card h-100">
                    <div class="card-header">
                        <h6 class="mb-0">Doanh thu theo ngày</h6>
                    </div>
                    <div class="card-body">
                        <div id="dashboardRevenueChart" style="min-height: 320px;"></div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h6 class="mb-0">Funnel trạng thái</h6>
                    </div>
                    <div class="card-body">
                        <div id="dashboardFunnelChart" style="min-height: 320px;"></div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if ($dashboardSectionCharts || $dashboardSectionAi)
        <div class="row g-3 mb-3">
            @if ($dashboardSectionCharts)
                <div class="{{ $dashboardSectionAi ? 'col-xl-5' : 'col-xl-12' }}">
                    <div class="card h-100">
                        <div class="card-header">
                            <h6 class="mb-0">Tỷ trọng trạng thái đơn</h6>
                        </div>
                        <div class="card-body">
                            <div id="dashboardStatusChart" style="min-height: 300px;"></div>
                        </div>
                    </div>
                </div>
            @endif

            @if ($dashboardSectionAi)
                <div class="{{ $dashboardSectionCharts ? 'col-xl-7' : 'col-xl-12' }}">
                    <div class="card h-100">
                        <div class="card-header">
                            <h6 class="mb-0">AI Actions (rule-based)</h6>
                            <small class="text-muted">Ghi chú AI: Bản MVP dùng rule-based, chưa dùng mô hình AI nâng cao.</small>
                        </div>
                        <div class="card-body">
                            @foreach ($dashboardAlerts as $alert)
                                @php
                                    $alertType = trim((string) ($alert['type'] ?? 'info'));
                                    $alertClass = match ($alertType) {
                                        'danger' => 'danger',
                                        'warning' => 'warning',
                                        'success' => 'success',
                                        default => 'info',
                                    };
                                @endphp
                                <div class="alert alert-{{ $alertClass }} mb-2 py-2 px-3">
                                    <div class="fw-semibold">{{ $alert['title'] ?? '' }}</div>
                                    <small>{{ $alert['message'] ?? '' }}</small>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>
    @endif
@elseif ($dashboardTab === 'san-pham')
    <div class="row g-3">
        <div class="col-xl-7">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Top sản phẩm bán chạy</h6>
                    @if ($dashboardSectionDrilldown)
                        <a class="btn btn-outline-primary btn-sm" href="{{ $buildDashboardListLink() }}">Xem đơn liên
                            quan</a>
                    @endif
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered text-sm mb-0">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width: 60px;">#</th>
                                    <th>Sản phẩm</th>
                                    <th class="text-center">SL</th>
                                    <th class="text-end">Doanh thu</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse(($dashboardProduct['top_products'] ?? []) as $index => $row)
                                    <tr>
                                        <td class="text-center">{{ $index + 1 }}</td>
                                        <td>{{ $row['name'] ?? '' }}</td>
                                        <td class="text-center">{{ number_format((int) ($row['qty'] ?? 0)) }}</td>
                                        <td class="text-end">{{ Func::formatMoney((float) ($row['revenue'] ?? 0)) }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center">Không có dữ liệu</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-5">
            <div class="card h-100">
                <div class="card-header">
                    <h6 class="mb-0">Gợi ý nhập hàng (rule-based)</h6>
                    <small class="text-muted">Ghi chú AI: Gợi ý được tạo từ AI rule-based theo tốc độ bán và tồn kho.</small>
                </div>
                <div class="card-body">
                    @forelse(($dashboardProduct['restock_recommendations'] ?? []) as $row)
                        <div class="border rounded p-2 mb-2">
                            <div class="fw-semibold">{{ $row['name'] ?? '' }}</div>
                            <small class="d-block text-muted">Tồn kho:
                                {{ number_format((int) ($row['stock_qty'] ?? 0)) }}</small>
                            <small class="d-block text-muted">Dự kiến hết sau: {{ (int) ($row['days_left'] ?? 0) }}
                                ngày</small>
                            <small class="d-block text-primary">Đề xuất nhập:
                                {{ number_format((int) ($row['suggest_qty'] ?? 0)) }}</small>
                        </div>
                    @empty
                        <p class="text-muted mb-0">Chưa có sản phẩm cần gợi ý nhập thêm trong kỳ lọc này.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@elseif ($dashboardTab === 'van-chuyen')
    <div class="row g-3 mb-3">
        <div class="col-md-3">
            <div class="card h-100">
                <div class="card-body">
                    <small class="text-muted d-block mb-1">Tổng phí vận chuyển</small>
                    <h5 class="mb-0">{{ Func::formatMoney((float) ($dashboardShipping['ship_total'] ?? 0)) }}</h5>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100">
                <div class="card-body">
                    <small class="text-muted d-block mb-1">Phí ship trung bình/đơn</small>
                    <h5 class="mb-0">{{ Func::formatMoney((float) ($dashboardShipping['avg_ship'] ?? 0)) }}</h5>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card h-100">
                <div class="card-body">
                    <small class="text-muted d-block mb-1">% Đã giao</small>
                    <h5 class="mb-0 text-success">
                        {{ number_format((float) ($dashboardShipping['delivered_rate'] ?? 0), 1) }}%</h5>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card h-100">
                <div class="card-body">
                    <small class="text-muted d-block mb-1">% Hủy</small>
                    <h5 class="mb-0 text-danger">
                        {{ number_format((float) ($dashboardShipping['canceled_rate'] ?? 0), 1) }}%</h5>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card h-100">
                <div class="card-body">
                    <small class="text-muted d-block mb-1">% Thất bại</small>
                    <h5 class="mb-0 text-warning">
                        {{ number_format((float) ($dashboardShipping['failed_shipping_rate'] ?? 0), 1) }}%</h5>
                </div>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <p class="mb-0 text-muted">
                Tổng đơn trong kỳ lọc: <strong>{{ number_format((int) ($dashboardShipping['orders'] ?? 0)) }}</strong>
                đơn,
                đang giao: <strong>{{ number_format((int) ($dashboardShipping['shipping_count'] ?? 0)) }}</strong> đơn.
            </p>
        </div>
    </div>
@elseif ($dashboardTab === 'khach-hang')
    <div class="row g-3 mb-3">
        <div class="col-md-3">
            <div class="card h-100">
                <div class="card-body">
                    <small class="text-muted d-block mb-1">Tổng khách</small>
                    <h5 class="mb-0">{{ number_format((int) ($dashboardCustomers['total_customers'] ?? 0)) }}</h5>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100">
                <div class="card-body">
                    <small class="text-muted d-block mb-1">Khách mới trong kỳ</small>
                    <h5 class="mb-0">{{ number_format((int) ($dashboardCustomers['new_customers'] ?? 0)) }}</h5>
                </div>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-header">
            <h6 class="mb-0">Top khách hàng</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm table-bordered text-sm mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Khách hàng</th>
                            <th>SĐT</th>
                            <th class="text-center">Số đơn</th>
                            <th class="text-end">Doanh thu</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse(($dashboardCustomers['top_customers'] ?? []) as $index => $row)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $row['fullname'] ?? '' }}</td>
                                <td>{{ $row['phone'] ?? '-' }}</td>
                                <td class="text-center">{{ number_format((int) ($row['orders'] ?? 0)) }}</td>
                                <td class="text-end">{{ Func::formatMoney((float) ($row['revenue'] ?? 0)) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">Không có dữ liệu</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endif
</div>
