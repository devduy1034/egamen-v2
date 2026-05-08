@php
    $firstSlide = $slider->first();
    $firstSlideWidth = !empty($firstSlide) ? config('type.photo.' . $firstSlide['type'] . '.width') : null;
    $firstSlideHeight = !empty($firstSlide) ? config('type.photo.' . $firstSlide['type'] . '.height') : null;
    $firstSlideOpt = !empty($firstSlide) ? config('type.photo.' . $firstSlide['type'] . '.opt') : null;
@endphp

@if (!empty($firstSlide['photo']) && !empty($firstSlideWidth) && !empty($firstSlideHeight) && $firstSlideOpt !== null)
    @push('styles')
        <link rel="preload" as="image"
            href="{{ assets_photo('photo', $firstSlideWidth . 'x' . $firstSlideHeight . 'x' . $firstSlideOpt, $firstSlide['photo'], $firstSlideOpt != 0 ? 'thumbs' : '') }}">
    @endpush
@endif

<div class="slideshow below-nav">
    <div class="wrap-content">
        <div class="swiper swiper-auto"
            data-swiper="autoplay:{delay: 5000,pauseOnMouseEnter:true,disableOnInteraction:false}|speed:1000|loop:true|navigation:{nextEl:'.slide-next',prevEl:'.slide-prev'}|effect:'fade'|fadeEffect: {crossFade: true}">
            <div class="swiper-wrapper">
                @foreach ($slider as $k => $v)
                    <div class="swiper-slide">
                        <a class="item d-block pic position-relative" href="{{ $v['link'] }}"
                            title="{{ $v['name' . $lang] }}">
                            @component('component.image', [
                                'class' => 'w-100',
                                'w' => config('type.photo.' . $v['type'] . '.width'),
                                'h' => config('type.photo.' . $v['type'] . '.height'),
                                'z' => config('type.photo.' . $v['type'] . '.opt'),
                                'loading' => $k === 0 ? 'eager' : 'lazy',
                                'fetchpriority' => $k === 0 ? 'high' : 'auto',
                                'decoding' => $k === 0 ? 'sync' : 'async',
                                'is_watermarks' => false,
                                'destination' => 'photo',
                                'image' => $v['photo'] ?? '',
                                'alt' => $v['name' . $lang] ?? '',
                            ])
                            @endcomponent
                        </a>
                    </div>
                @endforeach
            </div>
            <div class="swiper-pagination slide-pagination"></div>
            <div class="swiper-button-prev slide-prev"></div>
            <div class="swiper-button-next slide-next"></div>
        </div>
    </div>
</div>
