@extends('layout')
@section('content')
    <div>
        <div class="max-width py-3 mt-4">
            @if (!empty($rowDetail))
                <div class="title-detail">
                    <h1><?= $rowDetail['name' . $lang] ?></h1>
                </div>
                <div class="album-product row">
                    <div class="col-lg-3 col-md-4 col-sm-6 col-6 mb-4">
                        <a class="img" data-fancybox="gallery"
                            data-src="{{ assets_photo('news', '', $rowDetail['photo']) }}"
                            title="{{ $rowDetail['name' . $lang] }}"
                            data-image="{{ assets_photo('news', config('type.news.' . $rowDetail['type'] . '.images.photo.thumb'), $rowDetail['photo'], 'thumbs') }}">
                            @component('component.image', [
                                'rel' => 'preload',
                                'class' => 'w-100',
                                'w' => config('type.news.' . $v['type'] . '.images.photo.width'),
                                'h' => config('type.news.' . $v['type'] . '.images.photo.height'),
                                'z' => config('type.news.' . $v['type'] . '.images.photo.opt'),
                                'breakpoints' => [
                                    400 => 400,
                                ],
                                'is_watermarks' => false,
                                'destination' => 'news',
                                'image' => $v['photo'] ?? '',
                                'alt' => $v['name' . $lang] ?? '',
                            ])
                            @endcomponent
                        </a>
                    </div>
                    @foreach ($rowDetailPhoto as $v)
                        <div class="col-lg-3 col-md-4 col-sm-6 col-6 mb-4">
                            <a class="img" data-fancybox="gallery"
                                data-src="{{ assets_photo('news', '', $v['photo']) }}"
                                title="{{ $rowDetail['name' . $lang] }}"
                                data-image="{{ assets_photo('news', config('type.news.' . $v['type'] . '.gallery.' . $v['type'] . '.photo_thumb'), $v['photo'], 'thumbs') }}">
                                @component('component.image', [
                                    'rel' => 'preload',
                                    'class' => 'w-100',
                                    'w' => config('type.news.' . $v['type'] . '.images.photo.width'),
                                    'h' => config('type.news.' . $v['type'] . '.images.photo.height'),
                                    'z' => config('type.news.' . $v['type'] . '.images.photo.opt'),
                                    'breakpoints' => [
                                        400 => 400,
                                    ],
                                    'is_watermarks' => false,
                                    'destination' => 'news',
                                    'image' => $v['photo'] ?? '',
                                    'alt' => $rowDetail['name' . $lang] ?? '',
                                ])
                                @endcomponent
                            </a>
                        </div>
                    @endforeach
                </div>
                <div class="content-main baonoidung w-clear" id="toc-content"> {!! Func::decodeHtmlChars($rowDetail['content'.$lang]) !!}</div>
                <div class="share">
                    <b>{{ __('web.chiase') }}:</b>
                    <div class="social-plugin w-clear">
                        @component('component.share')
                        @endcomponent
                    </div>
                </div>
            @else
                <div class="alert alert-warning w-100" role="alert">
                    <strong>{{ __('web.dangcapnhatdulieu') }}</strong>
                </div>
            @endif
        </div>
    </div>
    @if ($news->isNotEmpty())
        <div>
            <div class="max-width py-3">
                <div class="title-detail">
                    <h1>{{ __('web.albumkhac') }}</h1>
                </div>
                <div class="row">
                    @foreach ($news as $v)
                        <div class="col-lg-3 col-md-4 col-sm-6 col-6 mb-3">
                            <div class="album">
                                @component('component.itemAlbum', ['news' => $v])
                                @endcomponent
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
@endsection
