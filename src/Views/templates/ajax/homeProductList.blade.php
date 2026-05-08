@if ($productAjax->isNotEmpty())
    <div class="p-relative">
        <div class="swiper swiper-auto"
            data-swiper="slidesPerView:1|spaceBetween:10|breakpoints:{370: {slidesPerView:2,spaceBetween:10},575: {slidesPerView:2,spaceBetween:20},768: {slidesPerView:3,spaceBetween:20},992: {slidesPerView:{{ $paginate }},spaceBetween:12}}|autoplay:{delay: 5000,pauseOnMouseEnter:true,disableOnInteraction:false}|speed:1000|navigation:{nextEl:'.proajax-next',prevEl:'.proajax-prev'}">
            <div
                class="swiper-wrapper row row-product row-cols-2 row-cols-sm-2 row-cols-md-3 row-cols-lg-{{ $paginate }} flex-nowrap gutter-x-12">
                @foreach ($productAjax as $k => $v)
                    <div class="swiper-slide col">
                        @component('component.itemProduct', ['product' => $v])
                        @endcomponent
                    </div>
                @endforeach
            </div>
            <div class="swiper-button-prev proajax-prev"></div>
            <div class="swiper-button-next proajax-next"></div>
        </div>
    </div>
@endif
