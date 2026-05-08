<div class="modal-detail" x-data="{ open: false }" @showts.window="open=true;console.log($event.detail)" x-cloak
    x-show="open" x-transition>
    <div class="content-modal-detail">
        <div x-data="{ tab: 'tab1' }" @showts.window="tab = $event.detail.type">
            <ul class="tabs">
                @if ($rowDetailPhoto->isNotEmpty())
                    <li :class="{ 'active': tab === 'tab1' }" @click="tab = 'tab1'">Ảnh</li>
                @endif
                <li :class="{ 'active': tab === 'tab2' }" @click="tab = 'tab2'">Thông tin sản phẩm</li>
            </ul>
            @if ($rowDetailPhoto->isNotEmpty())
                <div x-cloak x-show="tab === 'tab1'" class="tab-content active">
                    <div class="slick_photo1 d-flex overflow-hidden">
                        <a id="Zoom-1" class="MagicZoom flex justify-content-center"
                            data-options="zoomMode: false; hint: off; rightClick: true; selectorTrigger: click; expandCaption: false; history: false;"
                            href="{{ assets_photo('product', '', $rowDetail['photo']) }}"
                            title="{{ $rowDetail['name'.$lang] }}">
                            <img class="w-100"
                                onerror="this.src='{{ thumbs('thumbs/950x600x1/assets/images/noimage.png') }}';"
                                src="{{ assets_photo('product', '950x600x1', $rowDetail['photo'], 'thumbs') }}"
                                alt="{{ $rowDetail['name'.$lang] }}">
                        </a>
                    </div>
                    <div class="album-product my-2 mt-3">
                        <div class="swiper swiper-auto"
                            data-swiper="slidesPerView:3|spaceBetween:10|breakpoints:{370: {slidesPerView:3,spaceBetween:10},575: {slidesPerView:2,spaceBetween:10},768: {slidesPerView:4,spaceBetween:0},992: {slidesPerView:5,spaceBetween:10}}|autoplay:{delay: 5000,pauseOnMouseEnter:true,disableOnInteraction:false}|speed:|allowTouchMove: false">
                            <div
                                class="swiper-wrapper row row-product row-cols-2 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 flex-nowrap g-1">
                                <a class="swiper-slide col thumb-pro-detail border-[1px]"
                                    data-zoom-id="Zoom-1" href="{{ assets_photo('product', '', $rowDetail['photo']) }}"
                                    title="{{ $rowDetail['name'.$lang] }}"
                                    data-image="{{ assets_photo('product', '710x440x1', $rowDetail['photo'], 'thumbs') }}"><img
                                        class=" !mb-0 !pb-0 !border-0"
                                        onerror="this.src='{{ thumbs('thumbs/710x440x1/assets/images/noimage.png') }}';"
                                        src="{{ assets_photo('product', '710x440x1', $rowDetail['photo'], 'thumbs') }}"
                                        alt="{{ $rowDetail['name'.$lang] }}"></a>
                                @foreach ($rowDetailPhoto as $v)
                                    <a class="swiper-slide col thumb-pro-detail border-[1px]"
                                        data-zoom-id="Zoom-1" href="{{ assets_photo('product', '', $v['photo']) }}"
                                        title="{{ $rowDetail['name'.$lang] }}"
                                        data-image="{{ assets_photo('product', '710x440x1', $v['photo'], 'thumbs') }}"><img
                                            class=" !mb-0 !pb-0 !border-0"
                                            onerror="this.src='{{ thumbs('thumbs/710x440x1/assets/images/noimage.png') }}';"
                                            src="{{ assets_photo('product', '710x440x1', $v['photo'], 'thumbs') }}"
                                            alt="{{ $rowDetail['name'.$lang] }}"></a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <div x-cloak x-show="tab === 'tab2'" class="tab-content">
                {!! Func::decodeHtmlChars($rowDetail['contentvi']) !!}
            </div>
        </div>
    </div>
    <div class="btn-closemenu close-tab" x-on:click="open = ! open">Đóng</div>
</div>
