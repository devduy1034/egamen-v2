@extends('layout')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y container-fluid">
        <h4>
            <span>Quản lý</span>/<span class="text-muted fw-light"></span>{{ $configMan->title_main ?? 'Voucher' }}
        </h4>

        @component('component.buttonMan')
        @endcomponent

        <div class="card pd-15 bg-main mb-3">
            <form method="get" action="{{ url('admin', ['com' => $com, 'act' => 'man', 'type' => $type]) }}"
                class="row g-2 align-items-end">
                <div class="col-md-4">
                    @component('component.inputSearch', ['title' => 'Tìm theo mã voucher'])
                    @endcomponent
                </div>
                <div class="col-md-3">
                    <label class="form-label">Trạng thái</label>
                    <select name="status" class="form-select">
                        <option value="">Tất cả trạng thái</option>
                        <option value="active" {{ request()->query('status') === 'active' ? 'selected' : '' }}>Active - Đang áp dụng</option>
                        <option value="scheduled" {{ request()->query('status') === 'scheduled' ? 'selected' : '' }}>Scheduled - Chưa tới thời gian chạy</option>
                        <option value="expired" {{ request()->query('status') === 'expired' ? 'selected' : '' }}>Expired - Đã hết hạn</option>
                        <option value="out_of_uses" {{ request()->query('status') === 'out_of_uses' ? 'selected' : '' }}>Out of uses - Hết lượt sử dụng</option>
                        <option value="disabled" {{ request()->query('status') === 'disabled' ? 'selected' : '' }}>Disabled - Đang tắt</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Ngày hết hạn</label>
                    <input type="text" class="form-control js-voucher-expire-range" name="expire_range"
                        value="{{ request()->query('expire_range') }}" placeholder="DD/MM/YYYY đến DD/MM/YYYY">
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-search mr-1"></i>Lọc
                    </button>
                    <a href="{{ url('admin', ['com' => $com, 'act' => 'man', 'type' => $type]) }}" class="btn btn-secondary">Xóa lọc</a>
                </div>
            </form>
        </div>

        <div class="card mb-3">
            <div class="card-datatable table-responsive">
                <table class="datatables-category-list table border-top text-sm">
                    <thead>
                        <tr>
                            <th class="align-middle w-[60px]">
                                <div class="custom-control custom-checkbox my-checkbox">
                                    <input type="checkbox" class="form-check-input" id="selectall-checkbox"
                                        {{ !isPermissions(str_replace('-', '.', $com) . '.' . $type . '.delete') ? 'disabled' : '' }}>
                                </div>
                            </th>
                            <th class="text-center w-[70px] !pl-0">STT</th>
                            <th>Hình ảnh</th>
                            <th>Tên voucher</th>
                            <th>Mã</th>
                            <th>Loại</th>
                            <th>Giá trị</th>
                            <th>Điều kiện</th>
                            <th>Thời gian</th>
                            <th>Usage</th>
                            <th class="text-center">Trạng thái</th>
                            @if (!empty($configMan->status))
                                @foreach ($configMan->status as $key => $value)
                                    <th class="text-lg-center text-center">{{ $value }}</th>
                                @endforeach
                            @endif
                            <th class="text-lg-center text-center">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $k => $v)
                            @php
                                $usageTotal = $v['usage_limit_total'] ?? null;
                                $usedCount = (int) ($v['used_count'] ?? 0);
                                $usageText = !empty($usageTotal) ? $usedCount . '/' . $usageTotal : (string) $usedCount;
                                $statusLabel = $v['status_label'] ?? ($v['status_text'] ?? 'Active');
                                $statusClass = $v['status_class'] ?? 'success';
                                $discountType = $v['discount_type'] ?? '';
                                $discountValue = $v['discount_value'] ?? 0;
                                $maxDiscount = $v['max_discount'] ?? null;
                                $imageSrc = !empty($v['photo'])
                                    ? assets_photo('voucher', '70x70x1', $v['photo'], 'thumbs')
                                    : assets('assets/images/noimage.png');
                            @endphp
                            <tr>
                                <td class="align-middle">
                                    <div class="custom-control custom-checkbox my-checkbox">
                                        <input type="checkbox" class="form-check-input" id="select-checkbox1" value="{{ $v['id'] }}"
                                            {{ !isPermissions(str_replace('-', '.', $com) . '.' . $type . '.delete') ? 'disabled' : '' }}>
                                    </div>
                                </td>
                                <td class="align-middle !pl-0">
                                    @component('component.inputNumb', ['numb' => $v['numb'] ?? 1, 'idtbl' => $v['id'], 'table' => 'vouchers'])
                                    @endcomponent
                                </td>
                                <td class="align-middle">
                                    <img class="img-preview" src="{{ $imageSrc }}" alt="{{ $v['name'] ?? 'Voucher' }}"
                                        onerror="this.src='{{ assets('assets/images/noimage.png') }}';" title="{{ $v['name'] ?? 'Voucher' }}" />
                                </td>
                                <td class="align-middle">
                                    @component('component.name', ['name' => $v['name'] ?? '-', 'params' => ['id' => $v['id']]])
                                    @endcomponent
                                </td>
                                <td class="align-middle">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge bg-label-primary text-uppercase">{{ $v['code'] ?? '-' }}</span>
                                        <button type="button" class="btn btn-sm btn-outline-secondary js-copy-voucher"
                                            data-clipboard-text="{{ $v['code'] ?? '' }}" title="Sao chép mã">
                                            <i class="ti ti-copy"></i>
                                        </button>
                                    </div>
                                </td>
                                <td class="align-middle">
                                    <span class="badge bg-label-info">
                                        {{ $discountType === 'PERCENT' ? '%' : ($discountType === 'FIXED_AMOUNT' ? 'Tiền' : 'Freeship') }}
                                    </span>
                                </td>
                                <td class="align-middle">
                                    @if ($discountType === 'PERCENT')
                                        {{ $discountValue }}%
                                        @if (!empty($maxDiscount))
                                            <div class="text-muted text-sm">Tối đa {{ Func::formatMoney($maxDiscount) }}</div>
                                        @endif
                                    @elseif ($discountType === 'FIXED_AMOUNT')
                                        {{ Func::formatMoney($discountValue) }}
                                    @else
                                        @if (!empty($discountValue))
                                            Freeship tối đa {{ Func::formatMoney($discountValue) }}
                                        @else
                                            Freeship
                                        @endif
                                    @endif
                                </td>
                                <td class="align-middle">
                                    @if (!empty($v['min_order_value']))
                                        <div class="text-muted text-sm">&#272;&#417;n t&#7889;i thi&#7875;u {{ Func::formatMoney($v['min_order_value']) }}</div>
                                    @else
                                        Kh&#244;ng y&#234;u c&#7847;u
                                    @endif
                                </td>
                                <td class="align-middle">
                                    <div>{{ $v['start_at'] ?? '-' }}</div>
                                    <div class="text-muted text-sm">{{ $v['end_at'] ?? '-' }}</div>
                                </td>
                                <td class="align-middle text-center">
                                    <span class="text-dark">{{ $usageText }}</span>
                                    @if (empty($usageTotal))
                                        <div class="text-muted text-sm">Không giới hạn</div>
                                    @endif
                                </td>
                                <td class="align-middle text-center">
                                    <span class="badge bg-{{ $statusClass }}">{{ $statusLabel }}</span>
                                </td>
                                @if (!empty($configMan->status))
                                    @foreach ($configMan->status as $key => $value)
                                        @php $statusArray = !empty($v['status']) ? explode(',', $v['status']) : []; @endphp
                                        <td class="align-middle text-center">
                                            <label class="switch switch-success">
                                                @component('component.switchButton', [
                                                    'keyC' => $key,
                                                    'idC' => $v['id'],
                                                    'tableC' => 'vouchers',
                                                    'status_arrayC' => $statusArray,
                                                ])
                                                @endcomponent
                                            </label>
                                        </td>
                                    @endforeach
                                @endif
                                <td class="align-middle text-center">
                                    @component('component.buttonList', ['params' => ['id' => $v['id']]])
                                        <a class="text-info mr-2" href="{{ url('admin', ['com' => $com, 'act' => 'edit', 'type' => $type], ['id' => $v['id']]) }}">
                                            <i class="ti ti-eye" data-bs-toggle="tooltip" data-bs-trigger="hover"
                                                data-bs-placement="top" title="Xem"></i>
                                        </a>
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
    </div>
@endsection

@pushonce('styles')
    <link rel="stylesheet" href="@asset('assets/admin/vendor/libs/flatpickr/flatpickr.css')" />
@endpushonce

@pushonce('scripts')
    <script src="@asset('assets/admin/vendor/libs/flatpickr/flatpickr.js')"></script>
    <script>
        (function () {
            var rangeInput = document.querySelector('.js-voucher-expire-range');
            if (!rangeInput || typeof flatpickr !== 'function') return;

            flatpickr(rangeInput, {
                dateFormat: 'd/m/Y',
                mode: 'range',
                allowInput: true
            });
        })();
    </script>
@endpushonce
