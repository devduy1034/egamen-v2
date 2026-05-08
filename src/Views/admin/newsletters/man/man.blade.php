@extends('layout')
@section('content')
    <div class="container-xxl flex-grow-1 container-p-y container-fluid">
        <h4><span>Quản lý</span>/<span class="text-muted fw-light">{{ $configMan->title_main }}</h4>
        <div class="card pd-15 bg-main mb-3">
            <div class="col-md-3">
                <input type="hidden" id="status" class="filter-category" value="{{ !empty(request()->status) ? request()->status : '' }}">
                <input type="hidden" id="starred" class="filter-category" value="{{ !empty(request()->starred) ? request()->starred : '' }}">
                @component('component.inputSearch', ['title' => 'Tìm kiếm']) @endcomponent
            </div>
        </div>
        <div class="app-email card">
            <div class="row g-0">
                <div class="col app-email-sidebar border-end flex-grow-0" id="app-email-sidebar">
                    <div class="btn-compost-wrapper d-grid">
                        <button class="btn btn-primary btn-compose" id="emailComposeSidebarLabel"> Soạn thư </button>
                    </div>
                    <div class="email-filters py-2">
                        <ul class="email-filter-folders list-unstyled mb-4">
                            <li class="{{ (empty(request()->status) || request()->status == 'inbox') && empty(request()->starred) ? 'active' : '' }} d-flex justify-content-between" data-target="inbox">
                                <a href="{{ url('admin',['com'=>$com,'act'=>'man','type'=>$type]) }}" class="d-flex flex-wrap align-items-center">
                                    <i class="ti ti-mail ti-sm"></i>
                                    <span class="align-middle ms-2">Thư đến</span>
                                </a>
                                <div class="badge bg-label-primary rounded-pill badge-center count-inbox">
                                    {{ \LARAVEL\Models\NewslettersModel::where('confirm_status', 1)->where('type', $type)->count() }}
                                </div>
                            </li>
                            <li class="d-flex {{ !empty(request()->status) && request()->status == '2' ? 'active' : '' }}" data-target="sent">
                                <a href="{{ url('admin',['com'=>$com,'act'=>'man','type'=>$type],['status'=>2]) }}" class="d-flex flex-wrap align-items-center">
                                    <i class="ti ti-send ti-sm"></i>
                                    <span class="align-middle ms-2">Thu di</span>
                                </a>
                            </li>
                            <li class="{{ !empty(request()->starred) ? 'active' : '' }} d-flex justify-content-between" data-target="starred">
                                <a href="{{ url('admin',['com'=>$com,'act'=>'man','type'=>$type],['starred'=>1]) }}" class="d-flex flex-wrap align-items-center">
                                    <i class="ti ti-star ti-sm"></i>
                                    <span class="align-middle ms-2">Thư quan trọng</span>
                                </a>
                                <div class="badge bg-label-warning rounded-pill badge-center">
                                    {{ \LARAVEL\Models\NewslettersModel::where('starred', 1)->where('type', $type)->count() }}
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="col app-emails-list">
                    <div class="shadow-none border-0">
                        <div class="emails-list-header p-3 py-lg-3 py-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center w-100">
                                    <i class="ti ti-menu-2 ti-sm cursor-pointer d-block d-lg-none me-3" data-bs-toggle="sidebar" data-target="#app-email-sidebar" data-overlay></i>
                                    <div class="mb-0 mb-lg-2 w-100">
                                        <div class="input-group input-group-merge shadow-none">
                                            <span class="input-group-text border-0 ps-0" id="email-search">
                                                <i class="ti ti-search"></i>
                                            </span>
                                            <input type="text" class="form-control email-search-input border-0" placeholder="Tìm kiếm tên" aria-label="Search mail" aria-describedby="email-search" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <hr class="mx-n3 emails-list-header-hr" />
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center">
                                    <div class="form-check mb-0 me-2">
                                        <input class="form-check-input" type="checkbox" id="email-select-all" />
                                        <label class="form-check-label" for="email-select-all"></label>
                                    </div>
                                    <i class="ti ti-trash ti-sm email-list-delete cursor-pointer me-2" id="delete-all" data-url="{{ url('admin',['com'=>$com,'act'=>'delete','type'=>$type]) }}"></i>
                                </div>
                                <div
                                    class="email-pagination d-sm-flex d-none align-items-center flex-wrap justify-content-between justify-sm-content-end">
                                    <span class="d-sm-block d-none mx-3 text-muted">{{ $page * 10 - 9 }}-{{ $page * 10 > $counts ? $counts : $page * 10 }} of {{ $counts }}</span>
                                </div>
                            </div>
                        </div>
                        <hr class="container-m-nx m-0" />
                        <div class="email-list pt-0">
                            <ul class="list-unstyled m-0">
                                @foreach($items as $k => $v)
                                    <li class="email-list-item {{ $v['confirm_status'] == 1 ? 'email-marked-read' : '' }}" data-id="{{ $v['id'] }}" data-bs-toggle="sidebar" data-target="#app-email-view-{{ $k }}" data-starred="{{ $v['starred'] == 1 ? 'true' : '' }}">
                                        <div class="d-flex align-items-center">
                                            <div class="form-check mb-0">
                                                <input class="email-list-item-input form-check-input select-checkbox" type="checkbox" value="{{ $v['id'] }}" />
                                                <label class="form-check-label" for="email-1"></label>
                                            </div>
                                            <i class="email-list-item-bookmark ti ti-star ti-sm d-sm-inline-block d-none cursor-pointer ms-2 me-3"></i>
                                            <img src="@asset('assets/admin/img/avatars/user.png')" alt="user-avatar" class="d-block flex-shrink-0 rounded-circle me-sm-3 me-2" height="32" width="32" />
                                            <div class="email-list-item-content ms-2 ms-sm-0 me-2">
                                                <span class="email-list-item-username me-2">{{ $v['fullname']??$v['email'] }}</span>
                                                <span class="email-list-item-subject d-xl-inline-block d-block">{{ $v['subject'] }}</span>
                                            </div>
                                            <div class="email-list-item-meta ms-auto d-flex align-items-center">
                                                <span class="email-list-item-label badge badge-dot bg-danger d-none d-md-inline-block me-2" data-label="private"></span>
                                                <small class="email-list-item-time ">{{ date('d/m/Y', $v['date_created']) }}</small>
                                                <ul class="list-inline email-list-item-actions text-nowrap">
                                                    <li class="list-inline-item view-email {{ $v['confirm_status'] == 1 ? 'email-unread' : 'email-read' }}">
                                                        {!! $v['confirm_status'] == 1 ? '<i class="ti ti-mail ti-sm"></i>' : '<i class="ti ti-mail-opened ti-sm"></i>' !!}
                                                    </li>
                                                    <li class="list-inline-item email-delete" data-url="{{ url('admin',['com'=>$com,'act'=>'delete','type'=>$type],['id'=>$v['id']])}}"><a><i class="ti ti-trash ti-sm"></i></a></li>
                                                </ul>
                                            </div>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                            <ul class="list-unstyled m-0">
                                <li class="email-list-empty text-center d-none">No items found.</li>
                            </ul>
                        </div>
                    </div>
                    <div class="app-overlay"></div>
                </div>
                @foreach($items as $k =>$v)
                    <div class="col app-email-view flex-grow-0 bg-body" id="app-email-view-{{ $k }}">
                        <div class="card shadow-none border-0 rounded-0 app-email-view-header p-3 py-md-3 py-2">
                            <div class="d-flex justify-content-between align-items-center py-2">
                                <div class="d-flex align-items-center overflow-hidden">
                                    <i class="ti ti-chevron-left ti-sm cursor-pointer me-2" data-bs-toggle="sidebar" data-target="#app-email-view-{{ $k }}"></i>
                                    <h6 class="text-truncate mb-0 me-2">Quay lại</h6>
                                </div>
                            </div>
                        </div>
                        <hr class="m-0" />
                        <div class="app-email-view-content py-4">
                            @component('component.infoEmail', ['items' => $v]) @endcomponent
                        </div>
                    </div>
                @endforeach
            </div>
            @component('component.composeEmail', ['items' => $items]) @endcomponent
        </div>
        {!! $items->appends(request()->query())->links() !!}
    </div>
@endsection
