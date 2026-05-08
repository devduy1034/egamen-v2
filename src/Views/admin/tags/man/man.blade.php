@extends('layout')

@section('content')

<div class="container-xxl flex-grow-1 container-p-y container-fluid">
    <h4 class="py-3 mb-2">
        <span>Quản lý</span>/<span class="text-muted fw-light"></span>{{ $configMan->title_main }}
    </h4>
    @component('component.buttonMan')@endcomponent
    <div class="card pd-15 bg-main mb-3">
        <div class="col-md-3">
            @component('component.inputSearch', ['title' => 'Tìm kiếm danh mục'])@endcomponent
        </div>
    </div>
    <div class="card mb-3">
        <div class="card-datatable table-responsive">
            <table class="datatables-category-list table border-top text-sm">
                <thead>
                    <tr>
                        <th class="align-middle w-[60px]">
                            <div class="custom-control custom-checkbox my-checkbox">
                                <input type="checkbox" {{ !isPermissions(str_replace('-','.',$com).'.'.$type.'.delete')?'disabled':'' }} class="form-check-input" id="selectall-checkbox">
                            </div>
                        </th>
                        <th class="text-center w-[70px] !pl-0">STT</th>
                        <th width="30%">Tiêu đề</th>
                        @if (!empty($configMan->show_images))
                        <th>Hình ảnh</th>
                        @endif
                        @foreach ($configMan->status??[] as $key => $value)
                        <th class="text-lg-center text-center">{{ $value }}</th>
                        @endforeach
                        <th class="text-lg-center text-center">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $k => $v)
                    @php
                        $thumbSize = $configMan->thumb ?? '100x100x1';
                        $imageSrc = !empty($v['photo'])
                            ? assets_photo('news', $thumbSize, $v['photo'], 'thumbs')
                            : assets('assets/images/noimage.png');
                    @endphp
                    <tr>
                        <td class="align-middle">
                            <div class="custom-control custom-checkbox my-checkbox">
                                <input type="checkbox" {{ !isPermissions(str_replace('-','.',$com).'.'.$type.'.delete')?'disabled':'' }} class="form-check-input" id="select-checkbox1" value="{{ $v['id'] }}">
                            </div>
                        </td>
                        <td class="align-middle w-[70px] !pl-0">
                            @component('component.inputNumb',['numb'=>$v['numb'],'idtbl'=>$v['id'],'table'=>'tags'])@endcomponent
                        </td>
                        <td class="align-middle">
                            @component('component.name',['name'=>$v['namevi'],'slug'=>$v['slugvi'],'params'=>['id'=>$v['id']]])
                            @endcomponent
                            <div class="tool-action mt-2 w-clear">
                                @component('component.buttonAction',['slug'=>$v['slugvi'],'params'=>['id'=>$v['id']]])
                                @if (!empty($configMan->copy))
                                <div class="dropdown">
                                    <a id="dropdownCopy" data-url="{{ url('copy') }}"
                                        data-id="{{ $v['id'] }}" data-table="tags"
                                        data-com="{{ $com }}" data-type="{{ $type }}"
                                        class="nav-link text-success mr-3"><i class="ti ti-copy"></i>Copy</a>
                                </div>
                                @endif
                                @endcomponent
                            </div>
                        </td>
                        @if (!empty($configMan->show_images))
                        <td class="align-middle">
                            <img class="img-preview" src="{{ $imageSrc }}" loading="lazy" decoding="async"
                                onerror="this.src='{{ assets('assets/images/noimage.png') }}';" alt="{{ $v['namevi'] }}" title="{{ $v['namevi'] }}" />
                        </td>
                        @endif
                        @foreach ($configMan->status??[] as $key => $value)
                        @php $status_array = (!empty($v['status'])) ? explode(',', $v['status']) : array(); @endphp
                        <td class="align-middle text-center">
                            <label class="switch switch-success">
                                @component('component.switchButton',['keyC'=>$key,'idC'=>$v['id'],'tableC'=>'tags','status_arrayC'=>$status_array]) @endcomponent
                            </label>
                        </td>
                        @endforeach
                        <td class="align-middle text-center">
                            @component('component.buttonList',['params'=>['id'=>$v['id']]])@endcomponent
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
    @component('component.buttonMan') @endcomponent
</div>
@endsection
