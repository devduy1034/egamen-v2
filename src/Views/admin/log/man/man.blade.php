@extends('layout')

@section('content')

    <div class="container-xxl flex-grow-1 container-p-y container-fluid">
        <h4>
            <span>Quản lý</span>/<span class="text-muted fw-light"></span>Lịch sử
        </h4>
        <div class="app-ecommerce-category">
            <!-- Category List Table -->

            <div class="card pd-15 bg-main mb-3">
                <div class="row">
                    <div class="col-md-3">
                        @component('component.inputSearch', ['title' => 'Tìm kiếm IP'])
                        @endcomponent
                    </div>
                </div>
            </div>

            <div class="card pd-15 bg-main mb-3">
                <div class="row">
                    <div class="form-group col-md-3 last:!mb-0 md:!mb-0">
                        <input type="text" class="form-control" placeholder="DD/MM/YYYY to DD/MM/YYYY" name="date"
                            id="flatpickr-range" />
                    </div>
                    <div class="form-group col-md-3 last:!mb-0 md:!mb-0">
                        <a class="btn bg-gradient-success text-white" onclick="actionLog('{{ url('admin',['com'=>$com,'act'=>'man','type'=>$type]) }}')"
                            title="Tìm kiếm"><i class="fas fa-search mr-1"></i>Tìm kiếm</a>
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-datatable table-responsive">
                    <table class="datatables-category-list table border-top text-sm">
                        <thead>
                            <tr>
                                <th width="10%" class="text-center">Thiết bị</th>
                                <th width="40%">Trình duyệt</th>
                                <th width="">Địa chỉ IP</th>
                                <th width="15%">Ngày</th>
                                <th width="" class="text-center">Thao tác</th>
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
                                @for ($i = 0; $i < count($items); $i++)
                                   <tr>
                                        <td class="align-middle text-center">
                                            <a class="text-dark text-break">
                                                <img src="../assets/admin/img/icons/brands/{{ Func::device($items[$i]['device'], 10)['img'] }}.png"
                                                    alt="{{ Func::device($items[$i]['device'], 10)['name'] }}"
                                                    class="me-3" width="35" />
                                            </a>
                                        </td>
                                        <td class="align-middle">
                                            <a class="text-dark text-break">{{ $items[$i]['user_agent'] }}</a>
                                        </td>
                                        <td class="align-middle">
                                            <a class="text-dark text-break">{{ $items[$i]['ip'] }}</a>
                                        </td>
                                        <td class="align-middle">
                                            <a
                                                class="text-dark text-break">{{ date('d/m/Y H:i', $items[$i]['timelog']) }}</a>
                                        </td>
                                        <td class="text-center">
                                            <a class="text-dark text-break">{{ $items[$i]['operation'] }}</a>
                                        </td>
                                    </tr>
                                @endfor
                            </tbody>
                        @endif
                    </table>
                </div>
            </div>

            {!! $items->appends(request()->query())->links() !!}

        </div>
    </div>
@endsection
@pushonce('styles')
    <link rel="stylesheet" href="@asset('assets/admin/vendor/libs/flatpickr/flatpickr.css')" />
@endpushonce
@pushonce('scripts')
    <script src="@asset('assets/admin/vendor/libs/flatpickr/flatpickr.js')"></script>
    <script>
        const flatpickrRange = document.querySelector('#flatpickr-range');
        if (typeof flatpickrRange != undefined) {
            flatpickrRange.flatpickr({
                dateFormat: 'd/m/Y',
                mode: 'range',
            });
        }
    </script>
@endpushonce
