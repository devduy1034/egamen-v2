@extends('layout')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y container-fluid">
        <div class="app-ecommerce">
            <form class="validation-form" novalidate method="post"
                action="{{ url('admin', ['com' => $com, 'act' => 'save', 'type' => $type], ['id' => $item['id'] ?? 0, 'page' => $page]) }}"
                enctype="multipart/form-data">
                @component('component.buttonAdd')
                @endcomponent
                {!! Flash::getMessages('admin') !!}
                <div class="row">
                    <div class="col-12 col-lg-8">
                        @if (!empty($configMan->categories->item->slug_categories))
                            @component('component.slug', ['slugchange' => $act == 'edit' ? 1 : 0, 'item' => $item ?? []])
                            @endcomponent
                        @endif

                        <div class="card mb-4">
                            <div class="card-header">
                                <h3 class="card-title">Thông tin {{ $configMan->categories->item->title_main_categories }}
                                </h3>
                            </div>
                            <div class="card-body card-article">
                                @component('component.content', [
                                    'name' => $configMan->categories->item->name_categories ?? false,
                                    'desc' => $configMan->categories->item->desc_categories ?? false,
                                    'desc_cke' => $configMan->categories->item->desc_categories_cke ?? false,
                                    'content' => $configMan->categories->item->content_categories ?? false,
                                    'content_cke' => $configMan->categories->item->content_categories_cke ?? false,
                                    'item' => $item,
                                ])
                                @endcomponent
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-lg-4">
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Danh mục sản phẩm</h5>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label class="d-block" for="id_list">Danh mục cấp 1:</label>
                                    {!! Func::getAjaxCategory('product_list', 'product_cat', 'list', $type, 'Danh mục cấp 1') !!}
                                </div>
                                <div class="form-group">
                                    <label class="d-block" for="id_list">Danh mục cấp 2:</label>
                                    {!! Func::getAjaxCategory('product_cat', 'product_item', 'cat', $type, 'Danh mục cấp 2') !!}
                                </div>
                            </div>
                        </div>

                        @if (!empty($configMan->categories->item->images))
                            @foreach ($configMan->categories->item->images as $key => $photo)
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">{{ $photo->title }}</h5>
                                    </div>
                                    <div class="card-body">

                                        @php
                                            /* Photo detail */
                                            $photoDetail = [];
                                            $photoAction = 'photo';
                                            $photoDetail['upload'] = 'product';
                                            $photoDetail['image'] = !empty($item) ? $item[$key] : '';
                                            $photoDetail['id'] = !empty($item) ? $item['id'] : 0;
                                            $photoDetail['dimension'] =
                                                'Width: ' .
                                                $configMan->categories->item->images->$key->width .
                                                ' px - Height: ' .
                                                $configMan->categories->item->images->$key->height .
                                                ' px (' .
                                                config('type.type_img') .
                                                ')';
                                        @endphp
                                        @component('component.image', ['photoDetail' => $photoDetail, 'photoAction' => 'photo', 'key' => $key])
                                        @endcomponent
                                    </div>
                                </div>
                            @endforeach
                        @endif

                        <div class="card mb-4">
                            @component('component.tinhtrang', [
                                'item' => $item ?? [],
                                'status' => $configMan->categories->item->status_categories ?? [],
                                'stt' => true,
                            ])
                            @endcomponent
                        </div>
                    </div>
                </div>

                @if (!empty($configMan->categories->item->gallery_categories))
                    @component('component.filergallery', [
                        'title_main' => $configMan->categories->item->title_main_categories,
                        'gallery' => $gallery ?? [],
                        'act' => $act,
                        'folder' => 'product',
                    ])
                    @endcomponent
                @endif

                @if (!empty($configMan->categories->item->seo_categories))
                    @component('component.seo', ['item' => $item ?? [], 'com' => $com])
                    @endcomponent
                @endif
                <input type="hidden" name="id"
                    value="<?= !empty($item['id']) && $item['id'] > 0 ? $item['id'] : '' ?>">
                <input name="csrf_token" type="hidden" value="{{ csrf_token() }}">
                @component('component.buttonAdd')
                @endcomponent
            </form>
        </div>
    </div>
@endsection
