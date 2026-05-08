@extends('layout')

@section('content')

<div class="container-xxl flex-grow-1 container-p-y container-fluid">
    <h4>
        <span>Quản lý</span>/<span class="text-muted fw-light"></span data-i18n="propertieslist">{{ $configMan->title_main }}
    </h4>

    @component('component.buttonMan') @endcomponent

    <div class="card pd-15 bg-main mb-3">
        <div class="col-md-3">
            @component('component.inputSearch', ['title' => 'Tìm kiếm danh mục'])
            @endcomponent
        </div>
    </div>

    <div class="card pd-15 bg-main mb-3">
        <div class="row">
            @if (!empty($configMan->categories->list))
            <div class="form-group col-md-3 md:!mb-0 last:!mb-0">
                {!! Func::getLinkCategory('properties_list', 'list', $type, 'Danh mục cấp 1') !!}
            </div>
            @endif
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
                        <th width="30%">Tiêu đề</th>
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
                    @for ($i = 0; $i < count($items); $i++) @php $linkID='?id=' . $items[$i]['id']; if ($items[$i]['id_list']) { $linkID .='&id_list=' . $items[$i]['id_list']; } @endphp <tr>
                        <td class="align-middle">
                            <div class="custom-control custom-checkbox my-checkbox">
                                <input type="checkbox" class="form-check-input" id="select-checkbox1" value="{{ $items[$i]['id'] }}">
                            </div>
                        </td>
                        <td class="align-middle w-[70px] !pl-0">
                            @component('component.inputNumb',['numb'=>$items[$i]['numb'],'idtbl'=>$items[$i]['id'],'table'=>'properties'])@endcomponent
                        </td>
                        <td class="align-middle">
                            @component('component.name', [
                                'slug' => $items[$i]['slugvi'],
                                'name' => $items[$i]['namevi'],
                                'params' => [
                                'id' => $items[$i]['id'],
                                'id_list' => $items[$i]['id_list']
                                ],
                            ])
                            @endcomponent
                        </td>

                        @if (!empty($configMan->status))
                        @foreach ($configMan->status as $key => $value)
                        @php $status_array = (!empty($items[$i]['status'])) ? explode(',', $items[$i]['status']) : array(); @endphp
                        <td class="align-middle text-center">
                            <label class="switch switch-success">
                                @component('component.switchButton',['keyC'=>$key,'idC'=>$items[$i]['id'],'tableC'=>'properties','status_arrayC'=>$status_array]) @endcomponent
                            </label>
                        </td>
                        @endforeach
                        @endif

                        <td class="align-middle text-center">
                            @component('component.buttonList',['params'=>['id'=>$items[$i]['id'],'id_list'=>$items[$i]['id_list']]])@endcomponent
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
@endsection