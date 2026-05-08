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
                        @if (!empty($configMan->brand->slug_brand))
                            @component('component.slug', ['slugchange' => $act == 'edit' ? 1 : 0, 'item' => $item ?? []])
                            @endcomponent
                        @endif
                        <div class="card mb-4">
                            <div class="card-header">
                                <h3 class="card-title">Thông tin {{ $configMan->brand->title_main_brand }}
                                </h3>
                            </div>
                            <div class="card-body card-article">
                                @component('component.content', [
                                    'name' => $configMan->brand->name_brand ?? false,
                                    'desc' => $configMan->brand->desc_brand ?? false,
                                    'desc_cke' => $configMan->brand->desc_brand_cke ?? false,
                                    'content' => $configMan->brand->content_brand ?? false,
                                    'content_cke' => $configMan->brand->content_brand_cke ?? false,
                                    'item' => $item,
                                ])
                                @endcomponent
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-lg-4">
                        @if (!empty($configMan->brand->images))
                            @foreach ($configMan->brand->images as $key => $photo)
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
                                                $configMan->brand->images->$key->width .
                                                ' px - Height: ' .
                                                $configMan->brand->images->$key->height .
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
                                'status' => $configMan->brand->status_brand ?? [],
                                'stt' => true,
                            ])
                            @endcomponent
                        </div>
                    </div>

                    @if (!empty($configMan->brand->seo_brand))
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
