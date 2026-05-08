@extends('layout')
@section('content')

<div class="container-xxl flex-grow-1 container-p-y container-fluid">
    <h4>
        <span>Quản lý</span>/<span class="text-muted fw-light"></span>{{ $configMan->brand->title_main_brand }}
    </h4>

    @component('component.buttonMan')@endcomponent

    <div class="card pd-15 bg-main mb-3">
        <div class="col-md-3">
            @component('component.inputSearch', ['title' => 'Tìm kiếm danh mục']) @endcomponent
        </div>
    </div>
    <div class="card mb-3">
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
                        <th width="30%">Tiêu đề</th>
                        @if (!empty($configMan->brand->show_images_brand))
                        <th>Hình ảnh</th>
                        @endif
                        @if (!empty($configMan->brand->status_brand))
                        @foreach ($configMan->brand->status_brand as $key => $value)
                        <th class="text-lg-center text-center ">{{ $value }}</th>
                        @endforeach
                        @endif

                        <th class="text-lg-center text-center">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($items as $k => $v)
                    <tr>
                        <td class="align-middle">
                            <div class="custom-control custom-checkbox my-checkbox">
                                <input type="checkbox" {{ !isPermissions(str_replace('-','.',$com).'.'.$type.'.delete')?'disabled':'' }} class="form-check-input" id="select-checkbox1" value="{{ $v['id'] }}">
                            </div>
                        </td>
                        <td class="align-middle !pl-0">
                            @component('component.inputNumb',['numb'=>$v['numb'],'idtbl'=>$v['id'],'table'=>'product_brand'])@endcomponent
                        </td>
                        <td class="align-middle">
                            @component('component.name',['name'=>$v['namevi'],'slug'=>$v['slugvi'],'params'=>['id'=>$v['id']]])
                            @endcomponent
                            <div class="tool-action mt-2 w-clear">
                                @component('component.buttonAction',['slug'=>$v['slugvi'],'params'=>['id'=>$v['id']]])
                                    @if (!empty($configMan->brand->copy_brand))
                                        @can(str_replace('-','.',$com).'.'.$type.'.edit')
                                            <div class="dropdown">
                                                <a id="dropdownCopy" data-url="{{ url('copy') }}" data-id="{{ $v['id'] }}" data-table="product_brand" data-com="{{ $com }}" data-type="{{ $type }}" class="nav-link text-success mr-3"><i class="ti ti-copy"></i>Copy</a>
                                            </div>
                                        @endcan
                                    @endif
                                @endcomponent
                            </div>
                        </td>
                        @if (!empty($configMan->brand->show_images_brand))
                            <td class="align-middle">
                                <img class="img-preview" onerror=this.src='@asset("assets/images/noimage.png")'; src="{{assets_photo('product','70x70x1',$v['photo'],'thumbs')}}" alt="{{ $v['namevi'] }}" title="{{ $v['namevi'] }}" />
                            </td>
                        @endif
                        @if (!empty($configMan->brand->status_brand))
                            @foreach ($configMan->brand->status_brand as $key => $value)
                                @php $status_array = ($v['status']) ? explode(',', $v['status']) : []; @endphp
                                <td class="align-middle text-center ">
                                    <label class="switch switch-success">
                                        @component('component.switchButton',['keyC'=>$key,'idC'=>$v['id'],'tableC'=>'product_brand','status_arrayC'=>$status_array]) @endcomponent
                                    </label>
                                </td>
                            @endforeach
                        @endif

                        <td class="align-middle text-center">
                            @component('component.buttonList',['params'=>['id'=>$v['id']]])@endcomponent
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="100" class="text-center">Không có dữ liệu</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    {!! $items->appends(request()->query())->links() !!}
    @component('component.buttonMan') @endcomponent
</div>
@endsection