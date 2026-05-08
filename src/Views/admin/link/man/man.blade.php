@extends('layout')

@section('content')

    <div class="container-xxl flex-grow-1 container-p-y container-fluid">
        <h4>
            <span>Quản lý</span>/<span class="text-muted fw-light"></span>{{ $configMan->title_main }}
        </h4>

        <div class="card pd-15 bg-main mb-3 navbar-detached">
            <div class="d-flex gap-2">
                <a class="btn btn-primary text-white check-link" title="Thêm mới"><i class="ti ti-plus mr-2"></i> Lấy link</a>
            </div>
        </div>

        <div class="card pd-15 bg-main mb-3 navbar-detached">


            <div class="card pd-15 bg-main mb-3">
                <div class="col-md-3">
                    @component('component.inputSearch', ['title' => 'Tìm kiếm link'])
                    @endcomponent
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-datatable table-responsive">
                    <table class="datatables-category-list table border-top text-sm">
                        <thead>
                            <tr>
                                <th width="25%">Link</th>
                                <th width="20%" class="text-center">Nội dung</th>
                                <th width="15%" class="text-center">Trạng thái</th>
                                <th width="20%">Bài viết</th>
                                <th width="10%">Loại</th>
                                <th class="text-lg-center text-center">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($items as $k => $v)
                                <tr>
                                    <td class="align-middle">
                                        <a class="text-dark text-break">{{ $v['link'] }}</a>
                                    </td>
                                    <td class="align-middle text-center content-link">
                                        <a class="text-dark text-break text-center">{!! $v['content'] !!}</a>
                                    </td>
                                    <td class="align-middle text-center">
                                        <a class="text-break text-center result-link">{!! Func::decodeHtmlChars(Func::checkWebsiteStatus($v['link'])) !!}</a>
                                    </td>
                                    <td class="align-middle">
                                        <a class="text-dark text-break">{{ $v['namevi'] }}</a>
                                    </td>
                                     <td class="align-middle">
                                        <a class="text-dark text-break">{{ $v['type_parent'] }}</a>
                                    </td>
                                    <td class="align-middle text-center">
                                        @component('component.buttonList', [
                                            'params' => [
                                                'id' => $v['id'],
                                                'id_parent' => $id_parent,
                                            ],
                                            
                                        ])
                                         {{-- <a class="text-primary mr-2 reset-link" data-link="{{$v['link']}}"><i class="ti ti-refresh" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="top" title="Reset link"></i></a> --}}
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
        </div>
    </div>
@endsection
