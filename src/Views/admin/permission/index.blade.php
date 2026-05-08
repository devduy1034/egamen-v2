@extends('layout')
@section('content')
<div class="container-xxl flex-grow-1 container-p-y container-fluid">
    <h4>
        <span>Quản lý nhóm quyền </span>
    </h4>

    <div class="card pd-15 bg-main mb-3">
        <div class="d-flex">
            <a class="btn bg-gradient-primary text-white" href="{{url('permission_add')}}" title="Thêm mới"><i class="fas fa-plus mr-2"></i>Thêm mới</a>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-datatable table-responsive">
            <table class="datatables-category-list table border-top text-sm">
                <thead>
                    <tr>
                        <th class="align-middle w-[60px]">
                            <div class="custom-control custom-checkbox my-checkbox">
                                <input type="checkbox" class="form-check-input" id="selectall-checkbox">
                            </div>
                        </th>
                        <th class="text-center w-[70px] !pl-0">STT</th>
                        <th>Tên nhóm quyền</th>
                        <th class="text-lg-center text-center">Kích hoạt</th>
                        <th class="text-lg-center text-center">Thao tác</th>
                    </tr>
                </thead>
                @if (empty($count))
                <tbody>
                    <tr>
                        <td colspan="100" class="text-center">Không có dữ liệu nhóm quyền</td>
                    </tr>
                </tbody>
                @else
                <tbody>
                    @foreach($items??[] as $k => $v)
                    <tr>
                        <td class="align-middle">
                            <div class="custom-control custom-checkbox my-checkbox">
                                <input type="checkbox" class="form-check-input" id="select-checkbox1" value="{{ $v['id'] }}">
                            </div>
                        </td>
                        <td class="align-middle w-[70px] !pl-0">
                            @component('component.inputNumb',['numb'=>$v['numb'],'idtbl'=>$v['id'],'table'=>'roles'])@endcomponent
                        </td>
                        <td class="align-middle">
                            <a class="text-dark text-break">{{ $v['name'] }}</a>
                        </td>
                        @php $status_array = (!empty($v['status'])) ? explode(',', $v['status']) : []; @endphp
                        <td class="align-middle text-center">
                            <label class="switch switch-success">
                                <input type="checkbox" class="switch-input custom-control-input show-checkbox" id="show-checkbox-hienthi-{{ $v['id'] }}" data-table="roles" data-id="{{ $v['id'] }}" data-attr="hienthi" data-url="{{ url('status') }}" {{ in_array('hienthi', $status_array) ? 'checked' : '' }}>
                                <span class="switch-toggle-slider">
                                    <span class="switch-on"> <i class="ti ti-check"></i> </span>
                                    <span class="switch-off"> <i class="ti ti-x"></i> </span>
                                </span>
                            </label>
                        </td>

                        <td class="align-middle text-center">
                            <a class="text-primary mr-2" href="{{ url('permission_edit', null, ['id' => $v['id']]) }}"><i class="ti ti-edit" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="top" title="Chỉnh sửa"></i></a>
                            <a class="text-danger cursor-pointer" id="delete-item" data-url="{{ url('permission_delete', null, ['id' => $v['id']]) }}"><i class="ti ti-trash" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="top" title="Xóa"></i></a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                @endif
            </table>
        </div>
    </div>

    {!! $items->appends(request()->query())->links() !!}

    <div class="card bg-main mt-4 pd-15">
        <div class="d-flex">
            <a class="btn bg-gradient-primary text-white " href="{{url('permission_add')}}" title="Thêm mới"><i class="fas fa-plus mr-2"></i>Thêm mới</a>
        </div>
    </div>

</div>
@endsection
