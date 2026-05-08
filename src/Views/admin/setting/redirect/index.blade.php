@extends('layout')
@section('content')
    <div class="container-xxl flex-grow-1 container-p-y container-fluid">
        <h4 class="py-3 mb-2"> Quản lý điều hướng link </h4>
        @component('component.buttonMan') @endcomponent
        <div class="app-ecommerce-category mb-3">
            <div class="card">
                <div class="card-datatable table-responsive">
                    <table class="datatables-category-list table border-top text-sm">
                        <thead>
                        <tr>
                            <th class="align-middle w-[60px]">
                                <div class="custom-control custom-checkbox my-checkbox">
                                    <input {{ !isPermissions(str_replace('-','.',$com).'.'.$type.'.delete')?'disabled':'' }} type="checkbox" class="form-check-input" id="selectall-checkbox">
                                </div>
                            </th>
                            <th class="text-center w-[70px] !pl-0">STT</th>
                            <th width="30%">Url chuyển hướng</th>
                            <th width="30%">Url ??ch</th>
                            <th width="15%" class="text-center">Loại chuyển hướng</th>
                            <th class="text-lg-center text-center">Thao tác</th>
                        </tr>
                        </thead>
                        <body>
                            @forelse($items as $k => $v)
                                <tr>
                                    <td class="align-middle">
                                        <div class="custom-control custom-checkbox my-checkbox">
                                            <input {{ !isPermissions(str_replace('-','.',$com).'.'.$type.'.delete')?'disabled':'' }} value="{{ $v['id'] }}" type="checkbox" class="form-check-input" id="">
                                        </div>
                                    </td>
                                    <td class="align-middle w-[70px] !pl-0">
                                        @component('component.inputNumb',['numb'=>$v['numb'],'idtbl'=>$v['id'],'table'=>'photo'])@endcomponent
                                    </td>
                                    <td class="align-middle">
                                        <a class="text-dark text-break"><b class="text-primary">{{ $v['link'] }}</b></a>
                                    </td>
                                    <td class="align-middle">
                                        <a class="text-dark text-break"><b class="text-success">{{ $v['link_redirect'] }}</b></a>
                                    </td>
                                    <td class="align-middle text-center">
                                        <span class="badge bg-label-{{($v['redirect']==301)?'primary':'info'}} me-1">{{ $v['redirect'] }}</span>
                                    </td>
                                    <td class="align-middle text-center">
                                        <a class="text-primary mr-2" href="{{url('admin',['com'=>$com,'act'=>'edit','type'=>$type])}}?id={{ $v['id'] }}" title="Chỉnh sửa"><i class="ti ti-edit"></i></a>
                                        <a class="text-danger" id="delete-item" data-url="{{url('admin',['com'=>$com,'act'=>'delete','type'=>$type])}}?id={{ $v['id'] }}" title="Xóa"><i class="ti ti-trash"></i></a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="100" class="text-center">Không có dữ liệu</td>
                                </tr>
                            @endforelse
                        </body>
                    </table>
                </div>
            </div>
            {!! $items->appends(request()->query())->links() !!}

        </div>
        @component('component.buttonMan')@endcomponent
    </div>
@endsection