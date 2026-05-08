@extends('layout')

@section('content')

<div class="container-xxl flex-grow-1 container-p-y container-fluid">
    <h4>
        <span>Quản lý</span>/<span class="text-muted fw-light"></span>{{ $configMan->title_main }}
    </h4>

    @component('component.buttonMan',['params'=>['id_parent'=>$id_parent]])@endcomponent
    <div class="card pd-15 bg-main mb-3">
        <div class="col-md-3">
            @component('component.inputSearch', ['title' => 'Tìm kiếm danh mục']) @endcomponent
        </div>
    </div>
    @if(!empty($configMan->categories))
    <div class="card pd-15 bg-main mb-3">
        <div class="row">
            @if (!empty($configMan->categories->list))
            <div class="form-group col-md-3 md:!mb-0">
                {!! Func::getLinkCategory('news_list', 'list', $type, 'Danh mục cấp 1') !!}
            </div>
            @endif
            @if (!empty($configMan->categories->cat))
            <div class="form-group col-md-3 md:!mb-0">
                {!! Func::getLinkCategory('news_cat', 'cat', $type, 'Danh mục cấp 2') !!}
            </div>
            @endif
            @if (!empty($configMan->categories->item))
            <div class="form-group col-md-3 md:!mb-0">
                {!! Func::getLinkCategory('news_item', 'item', $type, 'Danh mục cấp 3') !!}
            </div>
            @endif
            @if (!empty($configMan->categories->sub))
            <div class="form-group col-md-3 md:!mb-0">
                {!! Func::getLinkCategory('news_sub', 'sub', $type, 'Danh mục cấp 4') !!}
            </div>
            @endif
        </div>
    </div>
    @endif
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
                        @if (!empty($configMan->status))
                        @foreach ($configMan->status as $key => $value)
                        <th class="text-lg-center text-center">{{ $value }}</th>
                        @endforeach
                        @endif
                        @if (!empty($configMan->oneSignal))
                            <th class="text-lg-center text-center">??y tin</th>
                        @endif
                        <th class="text-lg-center text-center">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $k => $v)
                    <tr>
                        <td class="align-middle">
                            <div class="custom-control custom-checkbox my-checkbox">
                                <input {{ !isPermissions(str_replace('-','.',$com).'.'.$type.'.delete')?'disabled':'' }} type="checkbox" class="form-check-input" id="select-checkbox1" value="{{ $v['id'] }}">
                            </div>
                        </td>
                        <td class="align-middle w-[70px] !pl-0">
                            @component('component.inputNumb',['numb'=>$v['numb'],'idtbl'=>$v['id'],'table'=>'news'])@endcomponent
                        </td>
                        <td class="align-middle">
                            @component('component.name',['config'=>$configMan,'name' => $v['name'. config('app.lang_default')],'slug'=>$v['slug'. config('app.lang_default')],'params'=>['id'=>$v['id'],'id_list'=>$v['id_list'],'id_cat'=>$v['id_cat'],'id_item'=>$v['id_item'],'id_sub'=>$v['id_sub']]])
                            @endcomponent
                            <div class="tool-action mt-2 w-clear">
                                @component('component.buttonAction',['config'=>$configMan,'slug'=>$v['slug'. config('app.lang_default')],'params'=>['id'=>$v['id'],'id_list'=>$v['id_list'],'id_cat'=>$v['id_cat'],'id_item'=>$v['id_item'],'id_sub'=>$v['id_sub']]])
                                <div class="dropdown">
                                    <a id="dropdownCopy" data-url="{{ url('copy') }}" data-id="{{ $v['id'] }}" data-table="news" data-com="{{ $com }}" data-type="{{ $type }}" class="nav-link text-success mr-3"><i class="ti ti-copy"></i>Copy</a>
                                </div>
                                @endcomponent
                            </div>
                        </td>
                        @if (!empty($configMan->show_images))
                        <td class="align-middle">
                            <img class="img-preview" onerror=this.src='@asset("assets/images/noimage.png")' ; src="{{assets_photo('news','70x70x1',$v['photo'],'thumbs')}}"  alt="{{ $v['name'. config('app.lang_default')] }}" title="{{ $v['name'. config('app.lang_default')] }}" />
                        </td>
                        @endif
                        @if (!empty($configMan->status))
                        @foreach ($configMan->status as $key => $value)
                        @php $status_array = (!empty($v['status'])) ? explode(',', $v['status']) : array(); @endphp
                        <td class="align-middle text-center">
                            <label class="switch switch-success">
                                @component('component.switchButton',['keyC'=>$key,'idC'=>$v['id'],'tableC'=>'news','status_arrayC'=>$status_array]) @endcomponent
                            </label>
                        </td>
                        @endforeach

                        @if (!empty($configMan->oneSignal))
                            <td class="text-lg-center text-center "><a href="{{ url('admin', ['com' => $com, 'act' => 'send', 'type' => $type], ['id' => $v['id']]) }}" class="btn btn-primary  text-white"><i class="ti ti-bell-ringing"></i></a></td>
                        @endif

                        @endif
                        <td class="align-middle text-center">
                            @component('component.buttonList',['params'=>['id'=>$v['id'],'id_list'=>$v['id_list'],'id_cat'=>$v['id_cat'],'id_item'=>$v['id_item'],'id_sub'=>$v['id_sub'],'id_parent'=>$id_parent]])@endcomponent
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

    @component('component.buttonMan',['params'=>['id_parent'=>$id_parent]])
    @endcomponent

</div>
@endsection