@extends('layout')
@section('content')
<div class="container-xxl flex-grow-1 container-p-y container-fluid">
    <h4>
        <span>Quản lý</span>/<span class="text-muted fw-light">{{ $configMan->title_main }}
    </h4>
    @component('component.buttonMan',['params'=>['gallery'=>$gallery,'id_parent'=>$id_parent]])
    @endcomponent

    <div class="card">

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
                        @if (!empty($configMan->gallery->$gallery->name_photo))
                        <th width="30%">Tiêu đề</th>
                        @endif
                        @if (!empty($configMan->gallery->$gallery->images_photo))
                        <th>Hình ảnh</th>
                        @endif
                        @if (!empty($configMan->gallery->$gallery->status_photo))
                        @foreach ($configMan->gallery->$gallery->status_photo as $key => $value)
                        <th class="text-lg-center text-center">{{ $value }}</th>
                        @endforeach
                        @endif
                        <th class="text-lg-center text-center">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $k => $v)
                    @php
                        $thumbSize = $configMan->gallery->$gallery->photo_thumb ?? '100x100x1';
                        $imageSrc = !empty($v['photo'])
                            ? assets_photo('product', $thumbSize, $v['photo'], 'thumbs')
                            : assets('assets/images/noimage.png');
                    @endphp
                    <tr>
                        <td class="align-middle">
                            <div class="custom-control custom-checkbox my-checkbox">
                                <input type="checkbox" class="form-check-input" id="select-checkbox1" value="{{ $v['id'] }}">
                            </div>
                        </td>
                        <td class="align-middle w-[70px] !pl-0">
                            <input type="number" class="form-control form-control-mini m-auto update-numb" min="0" value="{{ $v['numb'] }}" data-id="{{ $v['id'] }}" data-table="gallery">
                        </td>
                        @if (!empty($configMan->gallery->$gallery->name_photo))
                        <td class="align-middle">
                            <a class="text-dark text-break">{{ $v['namevi'] }}</a>
                        </td>
                        @endif
                        @if (!empty($configMan->gallery->$gallery->images_photo))
                        <td class="align-middle">
                            <img class="img-preview" src="{{ $imageSrc }}" loading="lazy" decoding="async"
                                onerror="this.src='{{ assets('assets/images/noimage.png') }}';" alt="{{ $v['namevi'] }}" title="{{ $v['namevi'] }}" />
                        </td>
                        @endif

                        @if (!empty($configMan->gallery->$gallery->status_photo))
                        @foreach ($configMan->gallery->$gallery->status_photo as $key => $value)
                        @php $status_array = (!empty($v['status'])) ? explode(',', $v['status']) : array(); @endphp
                        <td class="align-middle text-center">
                            <label class="switch switch-success">
                                @component('component.switchButton', [
                                'keyC' => $key,
                                'idC' => $v['id'],
                                'tableC' => 'gallery',
                                'status_arrayC' => $status_array,
                                ])
                                @endcomponent
                            </label>
                        </td>
                        @endforeach
                        @endif

                        <td class="align-middle text-center">
                            @component('component.buttonList', ['params'=>['id'=>$v['id'],'gallery'=>$gallery,'id_parent'=>$id_parent]]) @endcomponent
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
    @component('component.buttonMan',['params'=>['gallery'=>$gallery,'id_parent'=>$id_parent]])
    @endcomponent
</div>
@endsection
