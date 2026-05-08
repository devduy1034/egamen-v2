@extends('layout')
@section('content')
    @php
        $crawlerEnabled = !empty($crawlerSettings['enabled']);
    @endphp
    <div class="container-xxl flex-grow-1 container-p-y container-fluid">
        <h4>
            <span>Quản lý</span>/<span class="text-muted fw-light"></span>Crawl ICONDENIM
        </h4>

        <div class="card pd-15 bg-main mb-3 navbar-detached">
            <div class="d-flex flex-column flex-md-row gap-2 justify-content-between align-items-start align-items-md-center">
                <div>
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                        <div class="fw-semibold mb-0">Crawler ICONDENIM</div>
                        <span class="badge {{ $crawlerEnabled ? 'bg-label-success' : 'bg-label-danger' }}">
                            {{ $crawlerEnabled ? 'Đang bật' : 'Đang tắt' }}
                        </span>
                    </div>
                    @if (($sourceMode ?? 'collection') === 'product')
                        <div class="fw-semibold mb-1">Import theo link sản phẩm</div>
                        <div class="text-muted text-sm">
                            Đang chạy theo URL chỉ định: {{ $sourceDisplayUrl }}. Nếu sản phẩm đã tồn tại theo slug/code thì hệ thống sẽ tự bỏ qua.
                        </div>
                    @else
                        <div class="fw-semibold mb-1">Import sản phẩm từ {{ $sourceDisplayUrl }}</div>
                        <div class="text-muted text-sm">
                            @if ($fetchAll ?? true)
                                Mỗi lần chạy quét toàn bộ sản phẩm chưa có trong link collection. Sản phẩm đã có theo slug/code sẽ tự động bỏ qua.
                            @else
                                Mỗi lần chạy lấy tối đa {{ $batchSize }} sản phẩm mới. Sản phẩm đã có theo slug/code sẽ tự động bỏ qua.
                            @endif
                        </div>
                    @endif
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <form method="post" action="{{ url('admin', ['com' => 'product-crawler', 'act' => 'toggle', 'type' => $type]) }}">
                        <input type="hidden" name="csrf_token" value="{{ csrf_token() }}">
                        <input type="hidden" name="enabled" value="{{ $crawlerEnabled ? '0' : '1' }}">
                        <button type="submit" class="btn {{ $crawlerEnabled ? 'btn-outline-danger' : 'btn-success' }}">
                            {{ $crawlerEnabled ? 'Tắt crawler' : 'Bật crawler' }}
                        </button>
                    </form>
                    <a class="btn btn-outline-secondary"
                        href="{{ url('admin', ['com' => 'product', 'act' => 'man', 'type' => $type]) }}">
                        Quay lại danh sách
                    </a>
                </div>
            </div>
        </div>

        @unless ($crawlerEnabled)
            <div class="alert alert-warning mb-3" role="alert">
                Chức năng crawl ICONDENIM đang tắt. Bấm nút <strong>Bật crawler</strong> ở trên để mở lại trước khi import.
            </div>
        @endunless

        <div class="card mb-3">
            <div class="card-body">
                <form method="get" action="{{ url('admin', ['com' => 'product-crawler', 'act' => 'man', 'type' => $type]) }}"
                    class="row g-3 align-items-end">
                    <input type="hidden" name="run" value="1">
                    <fieldset class="col-12 row g-3 p-0 m-0 border-0" {{ $crawlerEnabled ? '' : 'disabled' }}>
                        <div class="col-md-6">
                        <label class="form-label" for="source_url">Link nguồn</label>
                        <input type="text" class="form-control" id="source_url" name="source_url"
                            value="{{ $sourceInputValue ?? '' }}"
                            placeholder="https://icondenim.com/products/... hoặc https://icondenim.com/collections/...">
                        <div class="form-text">
                            Để trống để dùng collection mặc định. Hỗ trợ link sản phẩm và collection của icondenim.com.
                        </div>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label" for="limit">Số sản phẩm mỗi lần</label>
                        <input type="number" min="1" max="50" class="form-control" id="limit" name="limit"
                            value="{{ $batchSize }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label" for="variant_quantity">Số lượng mỗi biến thể</label>
                        <input type="number" min="0" class="form-control" id="variant_quantity" name="variant_quantity"
                            value="{{ $variantQuantity }}">
                    </div>
                    <div class="col-md-12">
                        <input type="hidden" name="fetch_all" value="0">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="fetch_all" name="fetch_all" value="1"
                                {{ ($fetchAll ?? true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="fetch_all">
                                Lấy tất cả sản phẩm chưa có trong link collection
                            </label>
                        </div>
                        <div class="form-text">
                            Nếu bỏ chọn, hệ thống chỉ lấy tối đa số sản phẩm ở trường "Số sản phẩm mỗi lần". Khi dùng link sản phẩm, tùy chọn này không ảnh hưởng.
                        </div>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary">
                            Chạy import
                        </button>
                    </div>
                    </fieldset>
                </form>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-3 mb-3 mb-md-0">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="text-muted text-sm mb-1">Đã import</div>
                        <div class="fs-4 fw-semibold text-success">{{ $summary['imported'] ?? 0 }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3 mb-md-0">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="text-muted text-sm mb-1">Đã bỏ qua</div>
                        <div class="fs-4 fw-semibold text-warning">{{ $summary['skipped'] ?? 0 }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3 mb-md-0">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="text-muted text-sm mb-1">Lỗi</div>
                        <div class="fs-4 fw-semibold text-danger">{{ $summary['errors'] ?? 0 }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="text-muted text-sm mb-1">Sản phẩm chờ xử lý</div>
                        <div class="fs-4 fw-semibold text-primary">{{ $summary['pending'] ?? 0 }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Kết quả lần chạy gần nhất</h5>
            </div>
            <div class="card-body">
                @if (empty($results))
                    <div class="text-muted">Chưa có lần chạy nào trong phiên này.</div>
                @else
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="w-[120px]">Trạng thái</th>
                                    <th>Tên sản phẩm</th>
                                    <th class="w-[180px]">Mã</th>
                                    <th>Thông tin</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($results as $result)
                                    @php
                                        $status = $result['status'] ?? 'skip';
                                        $statusClass = match ($status) {
                                            'success' => 'bg-label-success',
                                            'error' => 'bg-label-danger',
                                            default => 'bg-label-warning',
                                        };
                                        $statusLabel = match ($status) {
                                            'success' => 'Thành công',
                                            'error' => 'Lỗi',
                                            default => 'Bỏ qua',
                                        };
                                    @endphp
                                    <tr>
                                        <td><span class="badge {{ $statusClass }}">{{ $statusLabel }}</span></td>
                                        <td>{{ $result['name'] ?? '' }}</td>
                                        <td>{{ $result['code'] ?? '' }}</td>
                                        <td>{{ $result['message'] ?? '' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <h5 class="card-title mb-0">Lịch sử lấy gần đây</h5>
            </div>
            <div class="card-body">
                @if (empty($history))
                    <div class="text-muted">Chưa có lịch sử lấy nào.</div>
                @else
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="w-[170px]">Thời gian</th>
                                    <th>Link nguồn</th>
                                    <th class="w-[160px]">Phạm vi</th>
                                    <th class="w-[140px]">Kết quả</th>
                                    <th>Ghi chú</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($history as $row)
                                    @php
                                        $historySummary = $row['summary'] ?? [];
                                        $historyStatus = $row['status'] ?? 'skip';
                                        $historyStatusClass = match ($historyStatus) {
                                            'success' => 'bg-label-success',
                                            'error' => 'bg-label-danger',
                                            default => 'bg-label-warning',
                                        };
                                        $historyScope = ($row['source_mode'] ?? '') === 'product'
                                            ? '1 sản phẩm'
                                            : (($row['fetch_all'] ?? false) ? 'Tất cả chưa có' : 'Giới hạn ' . (int) ($row['limit'] ?? 0));
                                    @endphp
                                    <tr>
                                        <td>{{ $row['created_at_text'] ?? '' }}</td>
                                        <td class="text-break">{{ $row['source_url'] ?? '' }}</td>
                                        <td>{{ $historyScope }}</td>
                                        <td>
                                            <div><span class="badge {{ $historyStatusClass }}">{{ ucfirst($historyStatus) }}</span></div>
                                            <div class="small text-muted mt-1">
                                                I: {{ $historySummary['imported'] ?? 0 }} |
                                                BQ: {{ $historySummary['skipped'] ?? 0 }} |
                                                L: {{ $historySummary['errors'] ?? 0 }}
                                            </div>
                                        </td>
                                        <td>{{ $row['message'] ?? '' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection