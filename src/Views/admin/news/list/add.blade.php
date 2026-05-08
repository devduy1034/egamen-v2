@extends('layout')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y container-fluid">

    <div class="app-ecommerce">

        <form class="validation-form" novalidate method="post"
            action="{{ url('admin', ['com' => $com, 'act' => 'save', 'type' => $type], ['id' => $item['id'] ?? 0,'page' => $page]) }}"
            enctype="multipart/form-data">

            @component('component.buttonAdd')
            @endcomponent

            {!! Flash::getMessages('admin') !!}

            <div class="row">

                <div class="col-12 col-lg-8">
                    @if (!empty($configMan->categories->list->slug_categories))
                    @php
                    $slugchange = $act == 'edit' ? 1 : 0;
                    $item = !empty($item) ? $item : [];
                    @endphp
                    @component('component.slug', ['slugchange' => $slugchange, 'item' => $item])
                    @endcomponent
                    @endif
                    <div class="card mb-4">
                        <div class="card-header">
                            <h3 class="card-title">Thông tin {{ $configMan->categories->list->title_main_categories }}
                            </h3>
                        </div>
                        <div class="card-body card-article">
                            @component('component.content', [
                            'name' => $configMan->categories->list->name_categories ?? false,
                            'desc' => $configMan->categories->list->desc_categories ?? false,
                            'desc_cke' => $configMan->categories->list->desc_cke_categories ?? false,
                            'content' => $configMan->categories->list->content_categories ?? false,
                            'content_cke' => $configMan->categories->list->content_cke_categories ?? false,
                            'item' => $item,
                            ])
                            @endcomponent
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-4">
                    @if (!empty($configMan->categories->list->images))
                    @foreach ($configMan->categories->list->images as $key => $photo)
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
                            $configMan->categories->list->images->$key->width .
                            ' px - Height: ' .
                            $configMan->categories->list->images->$key->height .
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
                        'status' => $configMan->categories->list->status_categories ?? [],
                        'stt' => true,
                        ])
                        @endcomponent
                    </div>

                </div>

                @if (!empty($configMan->categories->list->gallery_categories))
                @component('component.filergallery', [
                'title_main' => $configMan->categories->list->title_main_categories,
                'gallery' => $gallery ?? [],
                'act' => $act,
                'folder' => 'news',
                ])
                @endcomponent
                @endif

                @if (!empty($configMan->categories->list->seo_categories))
                @component('component.seo', ['item' => $item ?? [], 'com' => $com])
                @endcomponent
                @endif
            </div>


            <input type="hidden" name="id"
                value="<?= !empty($item['id']) && $item['id'] > 0 ? $item['id'] : '' ?>">
            <input name="csrf_token" type="hidden" value="{{ csrf_token() }}">
            @component('component.buttonAdd')
            @endcomponent

        </form>
    </div>
</div>
@endsection