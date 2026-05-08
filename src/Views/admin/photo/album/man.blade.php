@extends('layout')
@section('content')
    <div class="container-xxl flex-grow-1 container-p-y container-fluid">
        <h4 class="py-3 mb-2"> <span>Quản lý</span>/<span class="text-muted fw-light"></span>{{ $configMan->title_main }} </h4>
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
                            @if (!empty($configMan->show_images))
                                <th>Hình ảnh</th>
                            @endif
                            @if (!empty($configMan->link))
                            <th>Link</th>
                            @endif
                            @if (!empty($configMan->link_video))
                            <th>Link Video</th>
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
                                ? assets_photo('photo', $thumbSize, $v['photo'], 'thumbs')
                                : assets('assets/images/noimage.png');
                        @endphp
                        <tr>
                            <td class="align-middle">
                                <div class="custom-control custom-checkbox my-checkbox">
                                    <input {{ !isPermissions(str_replace('-','.',$com).'.'.$type.'.delete')?'disabled':'' }} type="checkbox" class="form-check-input" id="select-checkbox1" value="{{ $v['id'] }}">
                                </div>
                            </td>
                            <td class="align-middle w-[70px] !pl-0">
                                @component('component.inputNumb',['numb'=>$v['numb'],'idtbl'=>$v['id'],'table'=>'photo'])@endcomponent
                            </td>
                            <td class="align-middle">
                                @component('component.name',['name'=>$v['name'. config('app.lang_default')],'slug'=>$v['slug'. config('app.lang_default')],'params'=>['id'=>$v['id']]])@endcomponent
                            </td>
                            @if (!empty($configMan->show_images))
                            <td class="align-middle">
                                <img class="img-preview" src="{{ $imageSrc }}" loading="lazy" decoding="async"
                                    onerror="this.src='{{ assets('assets/images/noimage.png') }}';"
                                    alt="{{ $v['name'. config('app.lang_default')] }}" title="{{ $v['name'. config('app.lang_default')] }}" />
                            </td>
                            @endif
                            @if (!empty($configMan->link))
                            <td><a class="text-dark text-break">{{ $v['link'] }}</a></td>
                            @endif
                            @if (!empty($configMan->link_video))
                            <td><a class="text-dark text-break">{{ $v['link_video'] }}</a></td>
                            @endif
                            @foreach ($configMan->status??[] as $key => $value)
                                @php $status_array = (!empty($v['status'])) ? explode(',', $v['status']) : array(); @endphp
                                <td class="align-middle text-center">
                                    <label class="switch switch-success">
                                        @component('component.switchButton',['keyC'=>$key,'idC'=>$v['id'],'tableC'=>'photo','status_arrayC'=>$status_array]) @endcomponent
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
        @component('component.buttonMan')@endcomponent
    </div>
@endsection
