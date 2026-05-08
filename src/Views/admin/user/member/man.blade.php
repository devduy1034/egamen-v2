@extends('layout')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y container-fluid member-man-page">
    <h4>
        <span>Quản lý</span>/<span class="text-muted fw-light">User web</span>
    </h4>

    <div class="card pd-15 bg-main mb-3">
        <form method="get" action="{{ url('admin', ['com' => 'members', 'act' => 'man', 'type' => $type]) }}" class="row g-2 align-items-end">
            <div class="col-md-5">
                <label class="form-label">Tìm kiếm (email / SĐT)</label>
                <input type="text" name="keyword" class="form-control" value="{{ $filterKeyword }}" placeholder="Nhập email hoặc số điện thoại">
            </div>
            <div class="col-md-3">
                <label class="form-label">Trạng thái</label>
                <select name="status" class="form-select">
                    <option value="all" {{ $filterStatus === 'all' ? 'selected' : '' }}>Tất cả</option>
                    <option value="active" {{ $filterStatus === 'active' ? 'selected' : '' }}>Đang hoạt động</option>
                    <option value="locked" {{ $filterStatus === 'locked' ? 'selected' : '' }}>Đã khóa</option>
                </select>
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="ti ti-search mr-1"></i>Lọc
                </button>
                <a href="{{ url('admin', ['com' => 'members', 'act' => 'man', 'type' => $type]) }}" class="btn btn-secondary">Xóa lọc</a>
            </div>
        </form>
    </div>

    <div class="card mb-3">
        <div class="card-datatable table-responsive">
            <table class="datatables-category-list table border-top text-sm">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Họ tên</th>
                        <th>Email</th>
                        <th>SĐT</th>
                        <th class="text-center">Trạng thái</th>
                        <th>Ngày tạo</th>
                        <th class="text-center">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $member)
                    @php
                        $isLocked = ((string)($member->status ?? '') === 'locked');
                    @endphp
                    <tr>
                        <td>{{ $member->id }}</td>
                        <td>{{ $member->fullname ?: '-' }}</td>
                        <td>{{ $member->email ?: '-' }}</td>
                        <td>{{ $member->phone ?: '-' }}</td>
                        <td class="text-center">
                            @if ($isLocked)
                            <span class="badge bg-danger">Bị khóa</span>
                            @else
                            <span class="badge bg-success">Active</span>
                            @endif
                        </td>
                        <td>{{ $member->created_at ?: '-' }}</td>
                        <td class="text-center">
                            <a class="btn btn-sm btn-info text-white" href="{{ url('admin', ['com' => 'members', 'act' => 'edit', 'type' => $type], ['id' => $member->id]) }}">
                                <i class="ti ti-eye"></i> Xem
                            </a>
                            <form method="post" action="{{ url('admin', ['com' => 'members', 'act' => 'save', 'type' => $type], ['id' => $member->id]) }}" style="display:inline-block;">
                                <input type="hidden" name="csrf_token" value="{{ csrf_token() }}">
                                <input type="hidden" name="id" value="{{ $member->id }}">
                                <input type="hidden" name="member_action" value="{{ $isLocked ? 'unlock' : 'lock' }}">
                                <button type="submit" class="btn btn-sm {{ $isLocked ? 'member-action-unlock' : 'member-action-lock' }}">
                                    @if ($isLocked)
                                    <i class="ti ti-lock-open"></i> Mã
                                    @else
                                    <i class="ti ti-lock"></i> Khóa
                                    @endif
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center">Không có dữ liệu user</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {!! $items->appends(request()->query())->links() !!}
</div>
@endsection
