@extends('layout')
@section('content')
    <div class="container-xxl flex-grow-1 container-p-y container-fluid">
        <h4>
            <span>Quản lý</span>/<span class="text-muted fw-light"></span>{{ $configMan->title_main }}
        </h4>
        @component('component.buttonMan')
        @endcomponent
        @if (!empty(config('type.crawler.icondenim.active')))
            <div class="card pd-15 bg-main mb-3 navbar-detached">
                <div class="d-flex gap-2">
                    <a class="btn btn-warning text-dark"
                        href="{{ url('admin', ['com' => 'product-crawler', 'act' => 'man', 'type' => $type]) }}">
                        <i class="ti ti-world-search mr-2"></i>Crawl ICONDENIM
                    </a>
                </div>
            </div>
        @endif
        @if (Func::chekcPermission($tb . '.import.' . $type . '.man', $permissions))
            @if (!empty($configMan->excel->import))
                @component('component.excelImport', [
                    'url' => 'product-import/man/' . $type,
                    'title' => $configMan->excel->import->title_main_excel,
                ])
                @endcomponent
            @endif
        @endif
        <div class="card pd-15 bg-main mb-3">
            <div class="col-md-3">
                @component('component.inputSearch', ['title' => 'Tìm kiếm danh mục'])
                @endcomponent
            </div>
        </div>
        @if (!empty($configMan->categories))
            <div class="card pd-15 bg-main mb-3">
                <div class="row">
                    @if (!empty($configMan->categories->list))
                        <div class="form-group col-md-3 last:!mb-0 md:!mb-0">
                            {!! Func::getLinkCategory('product_list', 'list', $type, 'Danh mục cấp 1') !!}
                        </div>
                    @endif
                    @if (!empty($configMan->categories->cat))
                        <div class="form-group col-md-3 last:!mb-0 md:!mb-0">
                            {!! Func::getLinkCategory('product_cat', 'cat', $type, 'Danh mục cấp 2') !!}
                        </div>
                    @endif
                    @if (!empty($configMan->categories->item))
                        <div class="form-group col-md-3 last:!mb-0 md:!mb-0">
                            {!! Func::getLinkCategory('product_item', 'item', $type, 'Danh mục cấp 3') !!}
                        </div>
                    @endif
                    @if (!empty($configMan->categories->sub))
                        <div class="form-group col-md-3 last:!mb-0 md:!mb-0">
                            {!! Func::getLinkCategory('product_sub', 'sub', $type, 'Danh mục cấp 4') !!}
                        </div>
                    @endif
                </div>
            </div>
        @endif
        <div class="card">

            <div class="card-datatable table-responsive">
                <table class="datatables-category-list table border-top text-sm">
                    <thead>
                        <tr>
                            <th class="align-middle w-[60px]">
                                <div class="custom-control custom-checkbox my-checkbox">
                                    <input
                                        {{ !isPermissions(str_replace('-', '.', $com) . '.' . $type . '.delete') ? 'disabled' : '' }}
                                        type="checkbox" class="form-check-input" id="selectall-checkbox">
                                </div>
                            </th>
                            <th class="text-center w-[70px] !pl-0">STT</th>
                            <th width="20%">Tiêu đề</th>
                            @if (!empty($configMan->show_images))
                                <th>Hình ảnh</th>
                            @endif
                            @if (!empty($configMan->gallery))
                                <th>Gallery</th>
                            @endif
                            @if (!empty($configMan->posts))
                                <th>Bài viết</th>
                            @endif
                            @foreach ($configMan->status ?? [] as $key => $value)
                                <th class="text-lg-center text-center">{{ $value }}</th>
                            @endforeach
                            <th class="text-lg-center text-center">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>

                        @forelse($items as $k => $v)
                            <tr>
                                <td class="align-middle">
                                    <div class="custom-control custom-checkbox my-checkbox">
                                        <input
                                            {{ !isPermissions(str_replace('-', '.', $com) . '.' . $type . '.delete') ? 'disabled' : '' }}
                                            type="checkbox" class="form-check-input" id="select-checkbox1"
                                            value="{{ $v['id'] }}">
                                    </div>
                                </td>
                                <td class="align-middle w-[70px] !pl-0">
                                    @component('component.inputNumb', ['numb' => $v['numb'], 'idtbl' => $v['id'], 'table' => 'product'])
                                    @endcomponent
                                </td>
                                <td class="align-middle">

                                    @component('component.name', [
                                            'slug' => $v['slug'.config('app.lang_default')],
                                            'name' => $v['name'. config('app.lang_default')],
                                            'params' => [
                                                'id' => $v['id'],
                                                'id_list' => $v['id_list'],
                                                'id_cat' => $v['id_cat'],
                                                'id_item' => $v['id_item'],
                                                'id_sub' => $v['id_sub'],
                                                'id_brand' => $v['id_brand'],
                                            ],
                                        ])
                                    @endcomponent
                                    <div class="tool-action mt-2 w-clear">
                                        @component('component.buttonAction', [
                                            'slug' => $v['slug'. config('app.lang_default')],
                                            'params' => [
                                                'id' => $v['id'],
                                                'id_list' => $v['id_list'],
                                                'id_cat' => $v['id_cat'],
                                                'id_item' => $v['id_item'],
                                                'id_sub' => $v['id_sub'],
                                                'id_brand' => $v['id_brand'],
                                            ],
                                        ])
                                            <div class="dropdown">
                                                <a id="dropdownCopy" data-url="{{ url('copy') }}"
                                                    data-id="{{ $v['id'] }}" data-table="product"
                                                    data-com="{{ $com }}" data-type="{{ $type }}"
                                                    class="nav-link text-success mr-2"><i class="ti ti-copy"></i>Copy</a>
                                            </div>
                                            @if (!empty($configMan->copy))
                                                @can(str_replace('-', '.', $com) . '.' . $type . '.edit')
                                                    <div class="dropdown">
                                                        <a id="dropdownCopy" data-url="{{ url('copy') }}"
                                                            data-id="{{ $v['id'] }}" data-table="product"
                                                            data-com="{{ $com }}" data-type="{{ $type }}"
                                                            class="nav-link text-success mr-2"><i class="ti ti-copy"></i>Copy</a>
                                                    </div>
                                                @endcan
                                            @endif
                                            @if (!empty($configMan->properties) && !empty(Func::checkPhotoProperties($v['list_properties'])))
                                                <a
                                                    href="{{ url('admin', ['com' => 'product-photo', 'act' => 'man', 'type' => $type], ['id_product' => $v['id']]) }}">
                                                    <svg xmlns="http://www.w3.org/2000/svg"
                                                        class="icon icon-tabler icon-tabler-photo-circle-plus" width="18"
                                                        height="18" viewBox="0 0 24 24" stroke-width="1.5"
                                                        stroke="currentColor" fill="none" stroke-linecap="round"
                                                        stroke-linejoin="round">
                                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                        <path d="M15 8h.01" />
                                                        <path
                                                            d="M20.964 12.806a9 9 0 0 0 -8.964 -9.806a9 9 0 0 0 -9 9a9 9 0 0 0 9.397 8.991" />
                                                        <path d="M4 15l4 -4c.928 -.893 2.072 -.893 3 0l4 4" />
                                                        <path d="M14 14l1 -1c.928 -.893 2.072 -.893 3 0" />
                                                        <path d="M16 19.33h6" />
                                                        <path d="M19 16.33v6" />
                                                    </svg>
                                                    Thêm ảnh
                                                </a>
                                            @endif
                                        @endcomponent
                                    </div>
                                </td>
                                @if (!empty($configMan->show_images))
                                    <td class="align-middle">
                                        <img class="img-preview" onerror=this.src="@asset('assets/images/noimage.png')";
                                            src="{{ assets_photo('product', '70x70x1', $v['photo'], 'thumbs') }}"
                                            alt="{{ $v['name'. config('app.lang_default')] }}" title="{{ $v['name'. config('app.lang_default')] }}" />
                                    </td>
                                @endif
                                @if (!empty($configMan->gallery))
                                    <td class="align-middle">
                                        <div class="dropdown btn-dropdown">
                                            <button class="btn btn-secondary dropdown-toggle" type="button"
                                                id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                                                Thêm ảnh
                                            </button>
                                            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                                                @foreach ($configMan->gallery as $key => $vgallery)
                                                    <li><a class="dropdown-item"
                                                            href="{{ url('admin', ['com' => 'product-album', 'act' => 'man', 'type' => $type], ['gallery' => $key, 'id_parent' => $v['id']]) }}">{{ $vgallery->title_main_photo }}</a>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    </td>
                                @endif
                                @if (!empty($configMan->posts))
                                    <td class="align-middle">
                                        <div class="dropdown btn-dropdown">
                                            <button class="btn btn-secondary dropdown-toggle" type="button"
                                                id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                                                Thêm bài viết
                                            </button>
                                            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                                                @foreach ($configMan->posts as $key => $vposts)
                                                    <li><a class="dropdown-item"
                                                            href="{{ url('admin', ['com' => 'news', 'act' => 'man', 'type' => $key], ['id_parent' => $v['id']]) }}">{{ $vposts }}</a>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    </td>
                                @endif
                                @foreach ($configMan->status ?? [] as $key => $value)
                                    @php $status_array = (!empty($v['status'])) ? explode(',', $v['status']) : array(); @endphp
                                    <td class="align-middle text-center">
                                        <label class="switch switch-success">
                                            @component('component.switchButton', [
                                                'keyC' => $key,
                                                'idC' => $v['id'],
                                                'tableC' => 'product',
                                                'status_arrayC' => $status_array,
                                            ])
                                            @endcomponent
                                        </label>
                                    </td>
                                @endforeach
                                <td class="align-middle text-center">
                                    @component('component.buttonList', [
                                        'params' => [
                                            'id' => $v['id'],
                                            'id_list' => $v['id_list'],
                                            'id_cat' => $v['id_cat'],
                                            'id_item' => $v['id_item'],
                                            'id_sub' => $v['id_sub'],
                                            'id_brand' => $v['id_brand'],
                                        ],
                                    ])
                                    @endcomponent
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
        @component('component.buttonMan')
        @endcomponent
    </div>
@endsection
