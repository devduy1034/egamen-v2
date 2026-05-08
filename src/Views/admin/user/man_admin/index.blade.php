@extends('layout')
@section('content')
    <div class="container-xxl flex-grow-1 container-p-y container-fluid">
        <h4>
            <span>Quản lý thành viên </span>
        </h4>
        @component('component.buttonMan',['linkAddC'=>url('user.add')])
        @endcomponent

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
                            <th>Username</th>
                            <th>Họ tên</th>
                            @if (config('type.users.permission'))
                                <th>Nhóm quyền</th>
                            @endif
                            <th>Email</th>
                            <th class="text-lg-center text-center">Kích hoạt</th>
                            <th class="text-lg-center text-center">Thao tác</th>
                        </tr>
                    </thead>
                    @if (empty($count))
                        <tbody>
                            <tr>
                                <td colspan="100" class="text-center">Không có dữ liệu thành viên</td>
                            </tr>
                        </tbody>
                    @else
                        <tbody>
                            @foreach ($items ?? [] as $k => $v)
                                <tr>
                                    <td class="align-middle">

                                        <div class="custom-control custom-checkbox my-checkbox">
                                            <input type="checkbox" class="form-check-input"
                                                   id="select-checkbox1" value="{{ $v['id'] }}">
                                        </div>
                                    </td>
                                    <td class="align-middle w-[70px] !pl-0">
                                        @component('component.inputNumb',['numb'=>$v['numb'],'idtbl'=>$v['id'],'table'=>'user'])@endcomponent
                                    </td>
                                    <td class="align-middle">
                                        <a href="javascript:void(0)" class="text-dark text-break">{{ $v['username'] }}</a>
                                    </td>
                                    <td class="align-middle">
                                        <a href="javascript:void(0)" class="text-dark text-break">{{ $v['fullname'] }}</a>
                                    </td>
                                    @if (config('type.users.permission'))
                                        <td class="align-middle">{{ $v->roles()->first()->name }}</td>
                                    @endif
                                    <td class="align-middle">
                                        <a href="javascript:void(0)" class="text-dark text-break">{{ $v['email'] }}</a>
                                    </td>
                                    @php $status_array = (!empty($v['status'])) ? explode(',', $v['status']) : []; @endphp
                                    <td class="align-middle text-center">
                                        <label class="switch switch-success">
                                            <input type="checkbox" class="switch-input custom-control-input show-checkbox"
                                                id="show-checkbox-hienthi-{{ $v['id'] }}" data-table="user"
                                                data-id="{{ $v['id'] }}" data-attr="hienthi"
                                                data-url="{{ url('status') }}"
                                                {{ in_array('hienthi', $status_array) ? 'checked' : '' }}>
                                            <span class="switch-toggle-slider">
                                                <span class="switch-on"> <i class="ti ti-check"></i> </span>
                                                <span class="switch-off"> <i class="ti ti-x"></i> </span>
                                            </span>
                                        </label>
                                    </td>

                                    <td class="align-middle text-center">
                                        @component('component.buttonList',['params'=>['id'=>$v['id']]])@endcomponent
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    @endif
                </table>
            </div>
        </div>

        {!! $items->appends(request()->query())->links() !!}

        @component('component.buttonMan',['linkAddC'=>url('user.add')])
        @endcomponent

    </div>
@endsection
