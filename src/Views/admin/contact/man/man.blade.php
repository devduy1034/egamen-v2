@extends('layout')

@section('content')

<div class="container-xxl flex-grow-1 container-p-y container-fluid">
    <h4>
        <span>Quản lý</span>/<span class="text-muted fw-light"></span>{{ $configMan->title_main }}
    </h4>
    <div class="app-ecommerce-category">
        <!-- Category List Table -->

        @component('component.buttonMan')
        @endcomponent

        <div class="card pd-15 bg-main mb-3">
            <div class="col-md-3">
                @component('component.inputSearch', ['title' => 'Tìm kiếm email', 'link' => $linkMan])
                @endcomponent
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-datatable table-responsive">
                <table class="datatables-category-list table border-top text-sm">
                    <thead>
                        <tr>
                            <th class="align-middle" width="5%">
                                <div class="custom-control custom-checkbox my-checkbox">
                                    <input type="checkbox" class="form-check-input" id="selectall-checkbox">
                                </div>
                            </th>
                            <th class="text-center">STT</th>

                            @if (!empty($configMan->fullname))
                            <th width="">Tên</th>
                            @endif

                            @if (!empty($configMan->email))
                            <th width="">Email</th>
                            @endif

                            @if (!empty($configMan->address))
                            <th width="">Địa chỉ</th>
                            @endif

                            @if (!empty($configMan->phone))
                            <th width="">Điện thoại</th>
                            @endif

                            @if (!empty($configMan->file))
                            <th width="">File</th>
                            @endif

                            <th width="">Trạng thái</th>

                            <th class="text-lg-center text-center">Thao tác</th>
                        </tr>
                    </thead>
                    @if (empty($items))
                    <tbody>
                        <tr>
                            <td colspan="100" class="text-center">Không có dữ liệu</td>
                        </tr>
                    </tbody>
                    @else
                    <tbody>
                        @for ($i = 0; $i < count($items); $i++) @php $linkID='?id=' . $items[$i]['id']; @endphp <tr>
                            <td class="align-middle">
                                <div class="custom-control custom-checkbox my-checkbox">
                                    <input type="checkbox" class="form-check-input" id="select-checkbox1" value="{{ $items[$i]['id'] }}">
                                </div>
                            </td>
                            <td class="align-middle">
                                <input type="number" class="form-control form-control-mini m-auto update-numb" min="0" value="{{ $items[$i]['numb'] }}" data-id="{{ $items[$i]['id'] }}" data-table="contact">
                            </td>

                            @if (!empty($configMan->fullname))
                            <td class="align-middle">
                                <a class="text-dark text-break">{{ $items[$i]['fullname'] }}</a>
                            </td>
                            @endif

                            @if (!empty($configMan->email))
                            <td class="align-middle">
                                <a class="text-dark text-break">{{ $items[$i]['email'] }}</a>
                            </td>
                            @endif

                            @if (!empty($configMan->address))
                            <td class="align-middle">
                                <a class="text-dark text-break">{{ $items[$i]['address'] }}</a>
                            </td>
                            @endif

                            @if (!empty($configMan->phone))
                            <td class="align-middle">
                                <a class="text-dark text-break">{{ $items[$i]['phone'] }}</a>
                            </td>
                            @endif

                            @if (!empty($configMan->file))
                            <td class="align-middle">
                                @if (!empty($items[$i]['file_attach']))
                                <a class="btn btn-sm bg-gradient-primary text-white d-inline-block p-1 rounded" href="{{ upload('file', $items[$i]['file_attach']) }}" title="Download tệp tin"><i class="fas fa-download mr-2"></i>Download
                                    tệp tin</a>
                                @else
                                <a class="bg-gradient-secondary text-white d-inline-block p-1 rounded" href="#" title="Tệp tin trống"><i class="fas fa-download mr-2"></i>Tệp tin trống</a>
                                @endif
                            </td>
                            @endif

                            <td class="align-middle">
                                {{ Func::getStatuscontact($items[$i]['confirm_status'], $type) }}
                            </td>

                            <td class="align-middle text-center">
                                <a class="text-primary mr-2" href="{{ $linkEdit . $linkID }}" title="Chỉnh sửa"><i class="ti ti-edit"></i></a>
                                <a class="text-danger" id="delete-item" data-url="{{ $linkDelete }}?id={{ $items[$i]['id'] }}" title="Xóa"><i class="ti ti-trash"></i></a>
                            </td>
                            </tr>
                            @endfor
                    </tbody>
                    @endif
                </table>
            </div>
        </div>

        {!! $items->appends(request()->query())->links() !!}

        @component('component.buttonMan')
        @endcomponent

    </div>
</div>
@endsection