@extends('layout')
@section('content')
    <section x-data>
        <div class="container my-4">
            <div class="grid-pro-detail product-detail-v2" data-variant-stock='@json($variantStockMap ?? [])'>
                <div class="left-pro-detail">
                    <div class="product-detail-gallery">
                        <div class="product-detail-thumbs swiper swiper-auto" data-swiper-name="productThumbsSwiper"
                            data-swiper="direction:'vertical'|slidesPerView:'auto'|spaceBetween:10|speed:500|allowTouchMove:true|navigation:{nextEl:'.thumbs-next',prevEl:'.thumbs-prev'}|breakpoints:{0:{direction:'horizontal',slidesPerView:4,spaceBetween:8},576:{direction:'horizontal',slidesPerView:5,spaceBetween:8},992:{direction:'vertical',slidesPerView:'auto',spaceBetween:15}}|">
                            <div class="swiper-wrapper">
                                @foreach ($albumPhotos as $photoItem)
                                    <a class="swiper-slide item thumb-pro-detail {{ $loop->first ? 'mz-thumb-selected' : '' }}"
                                        data-zoom-id="Zoom-1" href="{{ assets_photo('product', '', $photoItem) }}"
                                        data-product-photo="{{ $photoItem }}" title="{{ $rowDetail['name' . $lang] }}"
                                        data-image="{{ assets_photo('product', $thumbPath, $photoItem, 'thumbs') }}">
                                        <img onerror="this.src='{{ thumbs('thumbs/' . $thumbPath . '/assets/images/noimage.png') }}';"
                                            src="{{ assets_photo('product', $thumbPath, $photoItem, 'thumbs') }}"
                                            alt="{{ $rowDetail['name' . $lang] }}">
                                    </a>
                                @endforeach
                            </div>
                        </div>
                        <div class="product-detail-main overflow-hidden">
                            <a id="Zoom-1" class="MagicZoom"
                                data-options="zoomMode: true; hint: off; rightClick: true; selectorTrigger: click; expandCaption: false; history: false;"
                                href="{{ assets_photo('product', '', $mainPhoto) }}"
                                title="{{ $rowDetail['name' . $lang] }}">
                                <img class="product-main-image"
                                    onerror="this.src='{{ thumbs('thumbs/' . $thumbPath . '/assets/images/noimage.png') }}';"
                                    src="{{ assets_photo('product', $thumbPath, $mainPhoto, 'thumbs') }}"
                                    alt="{{ $rowDetail['name' . $lang] }}">
                            </a>
                            <div class="swiper-button-prev thumbs-prev"></div>
                            <div class="swiper-button-next thumbs-next"></div>
                        </div>
                    </div>
                </div>

                <div class="right-pro-detail">
                    <div class="title-detail">
                        <h1>{{ $rowDetail['name' . $lang] }}</h1>
                    </div>
                    @if (!empty($initialCode))
                        <li class="flex mb-2 items-baseline">
                            <label
                                class="attr-label-pro-detail font-medium mr-[5px] text-[15px]">{{ __('web.code') }}:</label>
                            <div class="attr-content-pro-detail"><span
                                    class="js-product-code text-[15px]">{{ $initialCode }}</span>
                            </div>
                        </li>
                    @endif
                    <li class="flex mb-2 items-baseline">
                        <p class="attr-label-pro-detail font-medium mr-[5px]">{{ __('web.gia') }}:</p>
                        <div class="attr-content-pro-detail">
                            @php
                                $rawRegular = (float) ($rowDetail['regular_price'] ?? 0);
                                $rawSale = (float) ($rowDetail['sale_price'] ?? 0);
                                $hasDiscount = $rawSale > 0 && $rawRegular > $rawSale;
                                $displayPrice = $rawSale > 0 ? $rawSale : $rawRegular;
                                $saveAmount = $hasDiscount ? $rawRegular - $rawSale : 0;
                                $savePercent = $hasDiscount ? round((($rawRegular - $rawSale) * 100) / $rawRegular) : 0;
                            @endphp
                            @if ($hasDiscount)
                                <div class="price-line-pro-detail">
                                    <span
                                        class="price-new-pro-detail">{{ Func::formatMoney($rowDetail['sale_price']) }}</span>
                                    <span
                                        class="price-old-pro-detail">{{ Func::formatMoney($rowDetail['regular_price']) }}</span>
                                    <span
                                        class="price-percent-pro-detail js-price-percent {{ $hasDiscount ? '' : 'hidden' }}">
                                        -{{ $savePercent }}%
                                    </span>
                                </div>
                                <div class="price-saving-pro-detail js-price-saving {{ $hasDiscount ? '' : 'hidden' }}">
                                    (Tiết kiệm: <span>{{ Func::formatMoney($saveAmount) }}</span>)
                                </div>
                            @else
                                <div class="price-line-pro-detail">
                                    <span
                                        class="price-new-pro-detail">{{ $displayPrice ? Func::formatMoney($displayPrice) : __('web.lienhe') }}</span>
                                    <span class="price-old-pro-detail"></span>
                                    <span class="price-percent-pro-detail js-price-percent hidden"></span>
                                </div>
                                <div class="price-saving-pro-detail js-price-saving hidden">
                                    (Tiết kiệm: <span></span>)
                                </div>
                            @endif
                        </div>
                    </li>
                    @if (config('type.comment'))
                        <div class="product-review-summary">
                            <div class="comment-star">
                                <i class="far fa-star"></i>
                                <i class="far fa-star"></i>
                                <i class="far fa-star"></i>
                                <i class="far fa-star"></i>
                                <i class="far fa-star"></i>
                                <span style="width: {{ Comment::avgStar($rowDetail['id'], $rowDetail['type']) }}%">
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                </span>
                            </div>
                            <div class="comment-count"><a>({{ $countComment }} đánh giá)</a></div>
                        </div>
                    @endif
                    @if (!empty($rowDetail['desc' . $lang]))
                        <div class="desc-pro-detail mb-4">
                            {!! Func::decodeHtmlChars($rowDetail['desc' . $lang]) !!}
                        </div>
                    @endif
                    @if (!empty($listProperties))
                        @foreach ($listProperties as $groupKey => $list)
                            @if (count($list[1]) > 0)
                                @php
                                    $isColorList = ($list[0]['id'] ?? 0) == $colorListId;
                                    $propertyItems = collect($list[1])
                                        ->sortBy(function ($item) use ($initialVariantIds) {
                                            $number = isset($item['number']) ? (int) $item['number'] : null;
                                            $numb = isset($item['numb']) ? (int) $item['numb'] : null;
                                            $id = isset($item['id']) ? (int) $item['id'] : 0;
                                            $isActive =
                                                !empty($initialVariantIds) && in_array((int) $id, $initialVariantIds);
                                            $order = 100000 + $id;

                                            if (!is_null($number) && $number > 0) {
                                                $order = $number;
                                            } elseif (!is_null($numb) && $numb > 0) {
                                                $order = $numb;
                                            }

                                            return ($isActive ? 0 : 1000000) + $order;
                                        })
                                        ->values();
                                    $activePropertyName = '';
                                    foreach ($propertyItems as $tmpProp) {
                                        if (
                                            !empty($initialVariantIds) &&
                                            in_array((int) $tmpProp['id'], $initialVariantIds)
                                        ) {
                                            $activePropertyName = $tmpProp['name' . $lang];
                                            break;
                                        }
                                    }
                                    if (empty($activePropertyName) && $propertyItems->isNotEmpty()) {
                                        $activePropertyName = $propertyItems->first()['name' . $lang];
                                    }
                                    $groupLabel = $isColorList
                                        ? __('web.mausac') ?? 'Mau sac'
                                        : $list[0]['name' . $lang] ?? 'Kich thuoc';
                                @endphp
                                <div class="mb-2 product-color-title">
                                    <span>{{ $groupLabel }}: </span>
                                    <strong class="js-selected-prop {{ $isColorList ? 'js-selected-color' : '' }}"
                                        data-list="{{ $list[0]['id'] }}">{{ $activePropertyName }}</strong>
                                </div>
                                <div class="grid-properties mb-2">
                                    @foreach ($propertyItems as $propertyKey => $value)
                                        @php
                                            $isInStock = in_array((int) ($value['id'] ?? 0), $inStockPropertyIds ?? []);
                                        @endphp
                                        <span
                                            class="properties {{ (!empty($initialVariantIds) ? in_array((int) $value['id'], $initialVariantIds) : $propertyKey == 0) ? 'active' : '' }} {{ $isColorList ? 'is-color' : '' }} {{ $isInStock ? '' : 'outstock disabled' }}"
                                            data-product="{{ $rowDetail['id'] }}" data-id="{{ $value['id'] }}"
                                            data-list="{{ $list[0]['id'] }}" data-is-color="{{ $isColorList ? 1 : 0 }}"
                                            data-name="{{ $value['name' . $lang] }}"
                                            data-in-stock="{{ $isInStock ? 1 : 0 }}"
                                            data-photo="{{ $propertyPhotoMap[$value['id']] ?? '' }}"
                                            data-code="{{ $propertyCodeMap[$value['id']] ?? $rowDetail['code'] }}"
                                            style="{{ !empty($propertyPhotoMap[$value['id']]) ? "--color-thumb:url('" . assets_photo('product', $thumbPath, $propertyPhotoMap[$value['id']], 'thumbs') . "')" : '' }}">{{ $value['name' . $lang] }}</span>
                                    @endforeach
                                </div>
                            @endif
                        @endforeach
                    @endif


                    <ul class="attr-pro-detail">
                        @if (!empty($brandDetail))
                            <li class="flex mb-2 items-baseline">
                                <label class="attr-label-pro-detail font-medium mr-[5px]">{{ __('web.brand') }}:</label>
                                <div class="attr-content-pro-detail"><a
                                        href="{{ $brandDetail[$sluglang] }}">{{ $brandDetail['name' . $lang] }}</a>
                                </div>
                            </li>
                        @endif

                    </ul>
                    @if (config('type.order'))
                        <div class="mb-2">
                            <button type="button" class="btn-wishlist-detail js-wishlist-toggle"
                                data-product-id="{{ (int) $rowDetail['id'] }}" data-variant-source="detail"
                                data-variant-id="">
                                <i class="bi bi-heart"></i>
                                <span>Yêu Thích</span>
                            </button>
                        </div>
                        <div class="cart-pro-detail cart-actions-top mt-md-4 mt-2">
                            <div class="quantity-wrap">
                                <label class="sr-only" for="qty-pro">{{ __('web.soluong') }}</label>
                                <div class="quantity-pro-detail">
                                    <span class="quantity-minus-pro-detail">-</span>
                                    <input type="text" id="qty-pro" class="qty-pro !outline-none !shadow-none !ring-0"
                                        min="1" value="1" readonly="">
                                    <span class="quantity-plus-pro-detail">+</span>
                                </div>
                            </div>
                            <a class="transition addcart text-decoration-none addnow" data-id="{{ $rowDetail['id'] }}"
                                data-action="addnow">{{ __('web.themvaogiohang') }}</a>
                        </div>

                        <div class="cart-pro-detail cart-actions-bottom">
                            <a class="transition addcart text-decoration-none buynow" data-id="{{ $rowDetail['id'] }}"
                                data-action="buynow">{{ __('web.muangay') }}</a>
                        </div>
                    @endif

                    {{-- <div class="social-plugin w-clear">
                        @component('component.share')
                        @endcomponent
                    </div> --}}
                </div>
            </div>

            <div class="tabs-pro-detail mt-4">
                <ul class="nav nav-tabs" id="tabsProDetail" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="info-pro-detail-tab" data-bs-toggle="tab" href="#info-pro-detail"
                            role="tab">{{ __('web.thongtinsanpham') }}</a>
                    </li>
                </ul>
                <div class="tab-content" id="tabsProDetailContent">
                    <div class="tab-pane fade show active" id="info-pro-detail" role="tabpanel">
                        <div class="baonoidung chitietsanpham mt-4" x-data="{ expanded: false }">
                            <div class="info_nd content_down he-first" x-bind:class="expanded ? 'heigt-auto' : ''"
                                x-collapse.min.100px>
                                {!! Func::decodeHtmlChars($rowDetail['content' . $lang]) !!}
                            </div>
                            <button type="button" @click="expanded = ! expanded"
                                class="mx-auto block active:!bg-[#5172fd] active:!border-[#5172fd] active:!text-white mt-4 mb-4 !border-[1px] border-solid border-gray-400 bg-white text-black !shadow-none !ring-0 !outline-none rounded-[50px] px-[15px] py-[7px]">
                                <span x-text="(!expanded)?'{{ __('web.xemthem') }}':'{{ __('web.thugon') }}'"
                                    class="font-medium"></span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            @if (config('type.comment'))
                <div class="py-3">
                    @component('component.comment.comment', ['rowDetail' => $rowDetail])
                    @endcomponent
                </div>
            @endif

            @if (!empty($product))
                <div class="title-main mb-3 mt-3"><span>{{ __('web.sanphamcungloai') }}</span></div>
                <div class="swiper swiper-auto"
                    data-swiper="slidesPerView:1|spaceBetween:10|breakpoints:{370: {slidesPerView:2,spaceBetween:10},575: {slidesPerView:2,spaceBetween:20},768: {slidesPerView:3,spaceBetween:20},992: {slidesPerView:4,spaceBetween:20}}|autoplay:{delay: 5000,pauseOnMouseEnter:true,disableOnInteraction:false}|speed:1000">
                    <div
                        class="swiper-wrapper row row-product row-cols-2 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 flex-nowrap g-2">
                        @foreach ($product as $v)
                            <div class="swiper-slide col">
                                @include('component.itemProduct', ['product' => $v])
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

        </div>
    </section>
@endsection

@push('styles')
    <link rel="stylesheet" href="@asset('assets/magiczoomplus/magiczoomplus.css')">
@endpush
@push('scripts')
    <script src="@asset('assets/magiczoomplus/magiczoomplus.js')"></script>
@endpush
