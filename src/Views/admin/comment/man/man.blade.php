@extends('layout')

@section('content')

    <div class="container-xxl flex-grow-1 container-p-y container-fluid">
        <h4>
            <span>Quản lý</span>/<span class="text-muted fw-light"></span
                data-i18n="propertieslist">{{ $configMan->title_main }}
        </h4>

        <div class="card pd-15 bg-main mb-3">
            <div class="col-md-3">
                @component('component.inputSearch', ['title' => 'Tìm kiếm'])
                @endcomponent
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
                            <th>Tên</th>
                            <th>Email</th>
                            <th>Chuyên mục</th>
                            <th class="text-center">Ngày</th>
                            <th class="text-center" width="150px">Số sao</th>
                            <th class="text-center">Trả lời</th>
                            @if (!empty($configMan->status))
                                @foreach ($configMan->status as $key => $value)
                                    <th class="text-lg-center text-center">{{ $value }}</th>
                                @endforeach
                            @endif
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
                            @for ($i = 0; $i < count($items); $i++)
                                @php
                                    $linkID = '?id=' . $items[$i]['id'];

                                @endphp <tr>
                                    <td class="align-middle">
                                        <div class="custom-control custom-checkbox my-checkbox">
                                            <input type="checkbox" class="form-check-input" id="select-checkbox1"
                                                value="{{ $items[$i]['id'] }}">
                                        </div>
                                    </td>

                                    <td class="align-middle">
                                        <a class="text-dark text-break">{{ $items[$i]['fullname'] }}</a>
                                    </td>

                                    <td class="align-middle">
                                        <a class="text-dark text-break">{{ $items[$i]['email'] }}</a>
                                    </td>

                                    <td class="align-middle">
                                        <a class="text-dark text-break" target="_blank"
                                            href="{{ config('app.site_path') }}{{ Func::nameSlug($items[$i]['id_variant'], $items[$i]['type'], 'slugvi') }}">{{ Func::nameSlug($items[$i]['id_variant'], $items[$i]['type'], 'namevi') }}
                                            <span class="text-primary font-italic">(chi tiết >>)</span></a>
                                    </td>

                                    <td class="align-middle text-center">
                                        <a class="text-dark text-break">{{ $items[$i]['created_at'] }}</a>
                                    </td>

                                    <td class="align-middle text-center">
                                        <a class="text-dark text-break">
                                            <div class="comment-item-rating mb-2 w-clear">
                                                <div class="comment-item-star comment-star mb-0">
                                                    <i class="ti ti-star"></i>
                                                    <i class="ti ti-star"></i>
                                                    <i class="ti ti-star"></i>
                                                    <i class="ti ti-star"></i>
                                                    <i class="ti ti-star"></i>
                                                    <span style="width: {{ Comment::scoreStar($items[$i]['star']) }}%;">
                                                        <i class="ti ti-star"></i>
                                                        <i class="ti ti-star"></i>
                                                        <i class="ti ti-star"></i>
                                                        <i class="ti ti-star"></i>
                                                        <i class="ti ti-star"></i>
                                                    </span>
                                                </div>
                                            </div>
                                        </a>
                                    </td>

                                    <td class="align-middle text-center">
                                        <a class="text-dark text-break">
                                            <p class="last:!mb-0 md:!mb-0">Duyệt: <span
                                                    class="text-primary">({{ Comment::countReply($items[$i]['id'], 1) }})
                                            </p>
                                            <p class="last:!mb-0 md:!mb-0">Chưa duyệt: <span
                                                    class="text-primary">({{ Comment::countReply($items[$i]['id']) }})</p>
                                        </a>
                                    </td>

                                    @if (!empty($configMan->status))
                                        @foreach ($configMan->status as $key => $value)
                                            @php $status_array = (!empty($items[$i]['status'])) ? explode(',', $items[$i]['status']) : array(); @endphp
                                            <td class="align-middle text-center">
                                                <label class="switch switch-success">
                                                    @component('component.switchButton', [
                                                        'keyC' => $key,
                                                        'idC' => $items[$i]['id'],
                                                        'tableC' => 'comment',
                                                        'status_arrayC' => $status_array,
                                                    ])
                                                    @endcomponent
                                                </label>
                                            </td>
                                        @endforeach
                                    @endif

                                    <td class="align-middle text-center">
                                        @component('component.buttonList', [
                                            'params' => [
                                                'id' => $items[$i]['id'],
                                            ],
                                        ])
                                        @endcomponent
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
@endsection
