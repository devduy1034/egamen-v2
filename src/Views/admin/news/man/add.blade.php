@extends('layout')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y container-fluid">
    <div class="app-ecommerce">
        <form class="validation-form" novalidate method="post"
            action="{{ url('admin', ['com' => $com, 'act' => 'save', 'type' => $type], ['id' => $item['id'] ?? 0,'page' => $page]) }}"
            enctype="multipart/form-data">
            @component('component.buttonAdd', ['params' => ['id_parent' => $id_parent]])
            @endcomponent

            {!! Flash::getMessages('admin') !!}
            <div class="row">
                <div class="col-12 col-lg-8">
                    @if (!empty($configMan->slug))
                    @php
                    $slugchange = $act == 'edit' ? 1 : 0;
                    $item = !empty($item) ? $item : [];
                    @endphp
                    @component('component.slug', ['slugchange' => $slugchange, 'item' => $item])
                    @endcomponent
                    @endif
                    <div class="card mb-4">
                        <div class="card-header">
                            <h3 class="card-title">Thông tin {{ $configMan->title_main }}
                            </h3>
                        </div>
                        <div class="card-body card-article">
                            @component('component.content', [
                            'name' => $configMan->name ?? false,
                            'desc' => $configMan->desc ?? false,
                            'desc_cke' => $configMan->desc_cke ?? false,
                            'content' => $configMan->content ?? false,
                            'content_cke' => $configMan->content_cke ?? false,
                            'item' => $item,
                            ])
                            @endcomponent

                            @if (!empty($configMan->link))
                            <div class="form-group mt-3">
                                <label class="form-label" for="link">Link:</label>
                                <input type="text" class="form-control for-seo text-sm"
                                    name="data[link]" id="link"
                                    placeholder="Link"
                                    value="{{ !empty(Flash::has('link')) ? Flash::get('link') : $item['link'] }}">
                            </div>
                            @endif

                        </div>
                    </div>
                </div>
                <div class="col-12 col-lg-4">
                    @if (!empty($configMan->categories) || !empty($configMan->tags))
                    <div class="card mb-4 form-group-category">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Danh mục</h5>
                        </div>
                        <div class="card-body ">
                            @if (!empty($configMan->categories->list))
                            <div class="form-group">
                                <label class="form-label" for="id_list">Danh mục cấp 1:</label>
                                {!! Func::getAjaxCategory('news_list', 'news_cat', 'list', $type, 'Danh mục cấp 1') !!}
                            </div>
                            @endif
                            @if (!empty($configMan->categories->cat))
                            <div class="form-group">
                                <label class="form-label" for="id_list">Danh mục cấp 2:</label>
                                {!! Func::getAjaxCategory('news_cat', 'news_item', 'cat', $type, 'Danh mục cấp 2') !!}
                            </div>
                            @endif
                            @if (!empty($configMan->categories->item))
                            <div class="form-group">
                                <label class="form-label" for="id_list">Danh mục cấp 3:</label>
                                {!! Func::getAjaxCategory('news_item', 'news_sub', 'item', $type, 'Danh mục cấp 3') !!}
                            </div>
                            @endif
                            @if (!empty($configMan->categories->sub))
                            <div class="form-group">
                                <label class="form-label" for="id_list">Danh mục cấp 4:</label>
                                {!! Func::getAjaxCategory('news_sub', '', 'sub', $type, 'Danh mục cấp 4') !!}
                            </div>
                            @endif
                            @if (!empty($configMan->tags))
                            <div class="form-group last:!mb-0">
                                <label class="form-label" for="id_tags">Danh mục tags:</label>
                                {!! Func::getTags(@$item['id'], 'dataTags', 'news_tags', $type) !!}
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif

                    @if (!empty($configMan->images))
                    @foreach ($configMan->images as $key => $photo)
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">{{ $photo->title }}</h5>
                        </div>
                        <div class="card-body">

                            @php
                            /* Photo detail */
                            $photoDetail = [];
                            $photoAction = 'photo';
                            $photoDetail['upload'] = 'news';
                            $photoDetail['image'] = !empty($item) ? $item[$key] : '';
                            $photoDetail['id'] = !empty($item) ? $item['id'] : 0;
                            $photoDetail['dimension'] =
                            'Width: ' .
                            $configMan->images->$key->width .
                            ' px - Height: ' .
                            $configMan->images->$key->height .
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

                    @if (!empty($configMan->file))
                        <div class="card mb-4">
                            <div class="card-body">
                                <div class="form-group">
                                    <label class="change-file mb-1 mr-2" for="file-taptin">
                                        <p>Upload tệp tin:</p>
                                        <strong class="ml-2">
                                            <span class="btn btn-sm btn-primary">Chọn tệp tin</span>
                                            <div><b class="text-sm text-split"></b></div>
                                        </strong>
                                    </label>
                                    <strong class="d-block mt-2 mb-2 text-sm">{{config('type.type_file')}}</strong>
                                    <div class="custom-file my-custom-file d-none">
                                        <input type="file" class="custom-file-input" name="file-taptin" id="file-taptin">
                                        <label class="custom-file-label" for="file-taptin">Chọn file</label>
                                    </div>
                                    <?php

 if (isset($item['file']) && $item['file'] != '') { ?>
                                        <a class="btn btn-sm bg-gradient-primary text-white d-inline-block align-middle p-2 rounded mb-1"
                                            href="{{ assets_photo('file', '', $item['file'], '') }}" target="_blank" title="Download tệp tin hiện tại">Download tệp tin hiện tại</a>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="card mb-4">
                        @component('component.tinhtrang', ['item' => $item ?? [], 'status' => $configMan->status ?? [], 'stt' => true])
                        @if (!empty($configMan->datePublish))
                        @component('component.datePublish', ['item' => $item ?? []])
                        @endcomponent
                        @endif
                        @endcomponent
                    </div>
                </div>
            </div>
            @if (!empty($configMan->gallery))
            @component('component.filergallery', [
            'title_main' => $configMan->title_main,
            'gallery' => $gallery ?? [],
            'act' => $act,
            'folder' => 'news',
            ])
            @endcomponent
            @endif
            @if (!empty($configMan->seo))
            @component('component.seo', ['item' => $item ?? [], 'com' => $com, 'schema' => $configMan->schema])
            @endcomponent
            @endif
            @if (!empty($configMan->schema))
            @component('component.schema', ['item' => $item ?? []])
            <input type="hidden" id="schema-type" value="news">
            @endcomponent
            @endif
            <input type="hidden" name="id"
                value="<?= !empty($item['id']) && $item['id'] > 0 ? $item['id'] : '' ?>">
            <input type="hidden" name="id_parent"
                value="<?= !empty($id_parent) && $id_parent > 0 ? $id_parent : '' ?>">
            <input name="csrf_token" type="hidden" value="{{ csrf_token() }}">
            @component('component.buttonAdd', ['params' => ['id_parent' => $id_parent]])
            @endcomponent
        </form>
    </div>
</div>
@endsection