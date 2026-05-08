<div class="d-flex justify-content-between flex-wrap">
    @php
        $cat = $productAjax->first()?->getCategoryCat;
    @endphp
    <div class="left-product-list-home">
        <a class="pic-product-list-home d-block" href="{{ $cat?->{'slug' . $lang} }}"
            title="{{ $cat?->{'name' . $lang} }}">
            @component('component.image', [
                'rel' => 'preload',
                'class' => 'w-100',
                'w' => config('type.product.' . ($cat?->type ?? '') . '.categories.cat.images.photo1.width'),
                'h' => config('type.product.' . ($cat?->type ?? '') . '.categories.cat.images.photo1.height'),
                'z' => config('type.product.' . ($cat?->type ?? '') . '.categories.cat.images.photo1.opt'),
                'is_watermarks' => false,
                'destination' => 'product',
                'image' => $cat?->photo1 ?? '',
                'alt' => $cat?->{'name' . $lang} ?? '',
            ])
            @endcomponent
        </a>
    </div>
    <div class="right-product-list-home">
        @if ($productAjax->isNotEmpty())
            <div class="p-relative">
                <div class="swiper swiper-auto"
                    data-swiper="slidesPerView:1|spaceBetween:10|breakpoints:{370: {slidesPerView:2,spaceBetween:10},575: {slidesPerView:2,spaceBetween:20},768: {slidesPerView:3,spaceBetween:20},992: {slidesPerView:{{ $paginate }},spaceBetween:12}}|autoplay:{delay: 5000,pauseOnMouseEnter:true,disableOnInteraction:false}|speed:1000|navigation:{nextEl:'.proajaxcat-next',prevEl:'.proajaxcat-prev'}">
                    <div
                        class="swiper-wrapper row row-product row-cols-2 row-cols-sm-2 row-cols-md-3 row-cols-lg-{{ $paginate }} flex-nowrap gutter-x-12">
                        @foreach ($productAjax as $k => $v)
                            <div class="swiper-slide col">
                                @component('component.itemProduct', ['product' => $v])
                                @endcomponent
                            </div>
                        @endforeach
                    </div>
                    <div class="swiper-button-prev proajaxcat-prev"></div>
                    <div class="swiper-button-next proajaxcat-next"></div>
                </div>
            </div>
        @endif
    </div>
</div>
