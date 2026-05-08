@extends('layout')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y container-fluid member-detail-page">
    @php
        $isLocked = ((string)($member->status ?? '') === 'locked');
    @endphp

    <h4>
        <span>Quản lý</span>/<span class="text-muted fw-light">Chi tiết user {{ $member->fullname ?: ('#' . $member->id) }}</span>
    </h4>

    <div class="card mb-3 member-detail-card">
        <div class="card-body">
            <ul class="nav nav-tabs mb-3" role="tablist">
                <li class="nav-item">
                    <button type="button" class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-info">Thông tin cơ bản</button>
                </li>
                <li class="nav-item">
                    <button type="button" class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-orders">đơn hàng</button>
                </li>
            </ul>

            <div class="tab-content p-0">
                <div class="tab-pane fade show active" id="tab-info">
                    <div class="row mb-3 member-detail-summary">
                        <div class="col-md-6"><b>Ngày đăng ký:</b> {{ $member->created_at ?: '-' }}</div>
                        <div class="col-md-6">
                            <b>Trạng thái:</b>
                            @if ($isLocked)
                            <span class="badge bg-danger">Bị khóa</span>
                            @else
                            <span class="badge bg-success">Active</span>
                            @endif
                        </div>
                        <div class="col-md-6 mt-2"><b>Tổng số đơn:</b> {{ $ordersCount }}</div>
                        <div class="col-md-6 mt-2"><b>Tổng tiền đã mua:</b> {{ \LARAVEL\Core\Support\Facades\Func::formatMoney($ordersTotal) }}</div>
                    </div>

                    <form method="post" action="{{ url('admin', ['com' => 'members', 'act' => 'save', 'type' => $type], ['id' => $member->id]) }}" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="{{ csrf_token() }}">
                        <input type="hidden" name="id" value="{{ $member->id }}">
                        <input type="hidden" name="member_action" value="update_info">

                        <div class="row">
                            <div class="col-lg-8">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Họ tên</label>
                                        <input type="text" class="form-control" name="fullname" value="{{ $member->fullname ?: '' }}" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Email</label>
                                        <input type="email" class="form-control" name="email" value="{{ $member->email ?: '' }}">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Số điện thoại</label>
                                        <input type="text" class="form-control" name="phone" value="{{ $member->phone ?: '' }}">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Ngày sinh</label>
                                        <input type="text" class="form-control js-member-birthday-picker" name="birthday" value="{{ !empty($member->birthday) ? date('d/m/Y', (int) $member->birthday) : '' }}" placeholder="dd/mm/yyyy" autocomplete="off">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Giới tính</label>
                                        <select name="gender" class="form-select">
                                            <option value="0" {{ (int) ($member->gender ?? 0) === 0 ? 'selected' : '' }}>Chưa chọn</option>
                                            <option value="1" {{ (int) ($member->gender ?? 0) === 1 ? 'selected' : '' }}>Nam</option>
                                            <option value="2" {{ (int) ($member->gender ?? 0) === 2 ? 'selected' : '' }}>Nữ</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Avatar</label>
                                        <input type="file" class="form-control" name="avatar" accept=".jpg,.jpeg,.png,.webp,.gif">
                                        <div class="form-check mt-2">
                                            <input class="form-check-input" type="checkbox" name="remove_avatar" value="1" id="remove-avatar">
                                            <label class="form-check-label" for="remove-avatar">Xóa avatar hiện tại</label>
                                        </div>
                                    </div>
                                    <div class="col-12 mb-3">
                                        <label class="form-label">địa chỉ (mặc định)</label>
                                        <input type="text" class="form-control" name="address" value="{{ $member->address ?: '' }}">
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="border rounded p-3 text-center member-avatar-box">
                                    <div class="mb-2 fw-semibold">Ảnh đại diện</div>
                                    @if (!empty($member->avatar))
                                        <img src="{{ assets_photo('user', '300x300x1', $member->avatar, 'thumbs') }}" alt="Avatar" class="img-fluid rounded" style="max-height:220px;object-fit:cover;">
                                    @else
                                        <img src="@asset('assets/images/noimage.png')" alt="No avatar" class="img-fluid rounded" style="max-height:220px;object-fit:cover;">
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="d-flex gap-2 mb-3 member-detail-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="ti ti-device-floppy"></i> Lưu thông tin
                            </button>
                    </form>

                    <form method="post" action="{{ url('admin', ['com' => 'members', 'act' => 'save', 'type' => $type], ['id' => $member->id]) }}" style="display:inline-block;">
                        <input type="hidden" name="csrf_token" value="{{ csrf_token() }}">
                        <input type="hidden" name="id" value="{{ $member->id }}">
                        <input type="hidden" name="member_action" value="{{ $isLocked ? 'unlock' : 'lock' }}">
                        <button type="submit" class="btn {{ $isLocked ? 'member-action-unlock' : 'member-action-lock' }}">
                            @if ($isLocked)
                            <i class="ti ti-lock-open"></i> Mở tài khoản
                            @else
                            <i class="ti ti-lock"></i> Khóa tài khoản
                            @endif
                        </button>
                    </form>
                        </div>

                    <div class="card border mt-3 member-address-card">
                        <div class="card-header">
                            <h6 class="mb-0">Danh sách địa chỉ đã lưu</h6>
                        </div>
                        <div class="card-body">
                            @forelse(($addresses ?? []) as $address)
                                <div class="border rounded p-2 mb-2 member-address-item">
                                    <div class="d-flex justify-content-between">
                                        <b>{{ $address['recipient_name'] ?? '-' }}</b>
                                        @if (!empty($address['is_default']))
                                            <span class="badge bg-primary">Mặc định</span>
                                        @endif
                                    </div>
                                    <div>SĐT: {{ $address['recipient_phone'] ?? '-' }}</div>
                                    <div>
                                        {{ $address['address_line'] ?? '' }}
                                        {{ !empty($address['ward']) ? ', ' . $address['ward'] : '' }}
                                        {{ !empty($address['city']) ? ', ' . $address['city'] : '' }}
                                    </div>
                                </div>
                            @empty
                                <div class="text-muted">Chưa có địa chỉ đã lưu.</div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="tab-orders">
                    <div class="table-responsive">
                        <table class="table table-bordered text-sm member-orders-table">
                            <thead>
                                <tr>
                                    <th>Mã đơn</th>
                                    <th>Ngày đặt</th>
                                    <th>Tổng tiền</th>
                                    <th>Trạng thái</th>
                                    <th class="text-center">Xem</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($orders as $order)
                                <tr>
                                    <td>{{ $order->code }}</td>
                                    <td>{{ $order->created_at }}</td>
                                    <td>{{ \LARAVEL\Core\Support\Facades\Func::formatMoney((float) ($order->total_price ?? 0)) }}</td>
                                    <td>{{ \LARAVEL\Core\Support\Facades\Func::showName('order_status', (int) $order->order_status, 'namevi') }}</td>
                                    <td class="text-center">
                                        <a class="btn btn-sm btn-primary" href="{{ url('admin', ['com' => 'order', 'act' => 'edit', 'type' => 'don-hang'], ['id' => $order->id]) }}">
                                            Chi tiết
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center">Chưa có đơn hàng</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {!! $orders->appends(request()->query())->links() !!}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@pushonce('styles')
<link rel="stylesheet" href="@asset('assets/admin/vendor/libs/flatpickr/flatpickr.css')" />
@endpushonce

@pushonce('scripts')
<script src="@asset('assets/admin/vendor/libs/flatpickr/flatpickr.js')"></script>
<script>
    (function () {
        var birthdayInput = document.querySelector('.js-member-birthday-picker');
        if (!birthdayInput || typeof flatpickr !== 'function') return;

        flatpickr(birthdayInput, {
            dateFormat: 'd/m/Y',
            maxDate: 'today',
            minDate: '1900-01-01',
            allowInput: true
        });
    })();
</script>
@endpushonce
