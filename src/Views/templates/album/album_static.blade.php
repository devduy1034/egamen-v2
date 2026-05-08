@extends('layout')
@section('content')
    <div>
        <div class="max-width py-3 mt-4">
            @if ($photo->isNotEmpty())
                <div class="title-main">
                    <h1>{{ $titleMain }}</h1>
                </div>
                <div class="album-product row">
                    @foreach ($photo as $v)
                        <div class="col-lg-3 col-md-4 col-sm-6 col-6 mb-4">
                            <a class="img" data-fancybox="gallery" data-src="{{ assets_photo('photo', '', $v['photo']) }}"
                                title="{{ $titleMain }}"
                                data-image="{{ assets_photo('photo', config('type.photo.' . $v['type'] . '.thumb'), $v['photo'], 'thumbs') }}">
                                @component('component.image', [
                                    'rel' => 'preload',
                                    'class' => 'w-100',
                                    'w' => config('type.photo.' . $v['type'] . '.width'),
                                    'h' => config('type.photo.' . $v['type'] . '.height'),
                                    'z' => config('type.photo.' . $v['type'] . '.opt'),
                                    'breakpoints' => [
                                        400 => 400,
                                    ],
                                    'is_watermarks' => false,
                                    'destination' => 'photo',
                                    'image' => $v['photo'] ?? '',
                                    'alt' => $v['name' . $lang] ?? '',
                                ])
                                @endcomponent
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
@endsection
