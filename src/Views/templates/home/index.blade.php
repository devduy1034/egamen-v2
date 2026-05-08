@extends('layout')
@section('content')
    @if ($criteria->isNotEmpty())
        <section class="wrap-criteria">
            <div class="wrap-content">
                <div class="policies-body">
                    @foreach ($criteria as $k => $v)
                        <div class="policies-item">
                            <div class="policies-image">
                                @component('component.image', [
                                    'class' => 'w-100',
                                    'w' => config('type.photo.' . $v['type'] . '.images.photo.width'),
                                    'h' => config('type.photo.' . $v['type'] . '.images.photo.height'),
                                    'z' => 0,
                                    'loading' => 'eager',
                                    'fetchpriority' => 'high',
                                    'decoding' => 'async',
                                    'type' => '',
                                    'destination' => 'photo',
                                    'image' => $v['photo'] ?? '',
                                    'alt' => $v['name' . $lang] ?? '',
                                ])
                                @endcomponent
                            </div>
                            <div class="policies-info">
                                <h3 class="policies-title">{{ $v['name' . $lang] }}</h3>
                                <div class="policies-desc">{{ $v['desc' . $lang] }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @if (!empty($vouchers) && $vouchers->isNotEmpty())
        <section class="wrap-voucher-home">
            <div class="wrap-content">
                <div class="row g-3">
                    @foreach ($vouchers as $voucher)
                        @php
                            $discountType = strtoupper((string) ($voucher['discount_type'] ?? ''));
                            $discountValue = (float) ($voucher['discount_value'] ?? 0);
                            $maxDiscount = (float) ($voucher['max_discount'] ?? 0);
                            $minOrderValue = (float) ($voucher['min_order_value'] ?? 0);
                            $conditionText = trim((string) ($voucher['condition_text'] ?? ''));
                            $conditionLines = array_values(
                                array_filter(array_map('trim', preg_split('/\r\n|\n|\r/', $conditionText))),
                            );

                            if ($discountType === 'PERCENT') {
                                $discountText =
                                    'Giảm ' . rtrim(rtrim(number_format($discountValue, 2, '.', ''), '0'), '.') . '%';
                                if ($maxDiscount > 0) {
                                    $discountText .= ' tối đa ' . number_format($maxDiscount, 0, ',', '.') . 'đ';
                                }
                            } elseif ($discountType === 'FIXED_AMOUNT') {
                                $discountText = 'Giảm ' . number_format($discountValue, 0, ',', '.') . 'đ';
                            } else {
                                $discountText = 'Miễn phí vận chuyển';
                                if ($discountValue > 0) {
                                    $discountText .= ' tối đa ' . number_format($discountValue, 0, ',', '.') . 'đ';
                                }
                            }

                            $summaryText = trim((string) ($voucher['description'] ?? ''));
                            if ($summaryText === '') {
                                $summaryText = $discountText;
                                if ($minOrderValue > 0) {
                                    $summaryText .=
                                        ' cho đơn hàng tối thiểu ' . number_format($minOrderValue, 0, ',', '.') . 'đ';
                                }
                            }

                            $scopeType = strtoupper((string) ($voucher['scope_type'] ?? 'ALL'));
                            if ($scopeType === 'CATEGORY') {
                                $scopeText = 'Áp dụng cho danh mục được chọn';
                            } elseif ($scopeType === 'PRODUCT') {
                                $scopeText = 'Áp dụng cho sản phẩm được chọn';
                            } else {
                                $scopeText = 'Áp dụng cho tất cả sản phẩm';
                            }

                            $minOrderText =
                                $minOrderValue > 0
                                    ? 'Đơn tối thiểu ' . number_format($minOrderValue, 0, ',', '.') . 'đ'
                                    : 'Không yêu cầu đơn tối thiểu';

                            $expiryTimestamp = !empty($voucher['end_at'])
                                ? strtotime((string) $voucher['end_at'])
                                : false;
                            $expiryText = !empty($expiryTimestamp) ? date('d/m/Y H:i', $expiryTimestamp) : '';

                            $voucherImage = !empty($voucher['photo'])
                                ? assets_photo('voucher', '70x115x1', $voucher['photo'], 'thumbs')
                                : assets('assets/images/noimage.png');
                        @endphp
                        <div class="col-12 col-sm-6 col-xl-3">
                            <article class="voucher-home-card h-100">
                                <div class="voucher-home-thumb">
                                    <img src="{{ $voucherImage }}" alt="{{ $voucher['code'] ?? '' }}" loading="eager"
                                        fetchpriority="high" decoding="async" width="70" height="115">
                                </div>
                                <div class="voucher-home-info">
                                    <h3 class="voucher-home-code">NHẬP MÃ: {{ $voucher['code'] ?? '' }}</h3>
                                    <p class="voucher-home-desc">{{ $summaryText }}</p>
                                    <div class="voucher-home-action">
                                        <button type="button" class="voucher-home-btn js-copy-voucher-home"
                                            data-code="{{ $voucher['code'] ?? '' }}">
                                            Sao chép
                                        </button>
                                        <button type="button" class="voucher-home-link" data-bs-toggle="modal"
                                            data-bs-target="#voucher-home-modal-{{ $voucher['id'] }}">
                                            Điều kiện
                                        </button>
                                    </div>
                                </div>
                            </article>
                        </div>

                        <div class="modal fade voucher-home-modal" id="voucher-home-modal-{{ $voucher['id'] }}"
                            tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">NHẬP MÃ: {{ $voucher['code'] ?? '' }}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="voucher-home-modal-code">
                                            <span>Mã khuyến mãi:</span>
                                            <strong>{{ $voucher['code'] ?? '' }}</strong>
                                        </div>
                                        <div class="voucher-home-modal-condition">
                                            <p>Điều kiện:</p>
                                            <ul>
                                                @if (!empty($conditionLines))
                                                    @foreach ($conditionLines as $conditionLine)
                                                        <li>{{ $conditionLine }}</li>
                                                    @endforeach
                                                @else
                                                    <li>Vui lòng nhập điều kiện chi tiết trong admin.</li>
                                                @endif
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Đóng</button>
                                        <button type="button" class="btn btn-primary js-copy-voucher-home"
                                            data-code="{{ $voucher['code'] ?? '' }}">
                                            Sao chép
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @if ($productcat->isNotEmpty())
        <section class="wrap-procat-home">
            <div class="wrap-content">
                <div class="title-main-home">
                    <h2>Thời trang EGA</h2>
                </div>
                <div class="swiper swiper-auto"
                    data-swiper="slidesPerView:1|spaceBetween:10|breakpoints:{370: {slidesPerView:2,spaceBetween:10},575: {slidesPerView:2,spaceBetween:20},768: {slidesPerView:3,spaceBetween:20},992: {slidesPerView:6,spaceBetween:10}}|autoplay:{delay: 5000,pauseOnMouseEnter:true,disableOnInteraction:false}|speed:1000|navigation:{nextEl:'.procat-next',prevEl:'.procat-prev'}|loop:true">
                    <div
                        class="swiper-wrapper row row-product row-cols-2 row-cols-sm-3 row-cols-md-5 row-cols-lg-6 flex-nowrap gutter-x-20">
                        @foreach ($productcat as $v)
                            <div class="swiper-slide">
                                <div class="item-procat">
                                    <div class="pic-procat">
                                        <a class="" href="{{ $v[$sluglang] }}" title="{{ $v['name' . $lang] }}">
                                            @component('component.image', [
                                                'class' => 'w-100',
                                                'w' => 300,
                                                'h' => 300,
                                                'z' => config('type.product.' . $v['type'] . '.categories.cat.images.photo.opt'),
                                                'loading' => 'eager',
                                                'fetchpriority' => 'high',
                                                'decoding' => 'async',
                                                'is_watermarks' => false,
                                                'destination' => 'product',
                                                'image' => $v['photo'] ?? '',
                                                'alt' => $v['name' . $lang] ?? '',
                                            ])
                                            @endcomponent
                                        </a>
                                    </div>
                                    <div class="info-procat">
                                        <h3 class="">
                                            <a class="text-split text-decoration-none name-procat"
                                                href="{{ $v[$sluglang] }}"
                                                title="{{ $v['name' . $lang] }}">{{ $v['name' . $lang] }}</a>
                                        </h3>
                                        <div class="count-procat">
                                            {{ $v->getItems->count() }} sản phẩm
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="swiper-button-prev procat-prev"></div>
                    <div class="swiper-button-next procat-next"></div>
                </div>
            </div>
        </section>
    @endif
    @if ($productNew->isNotEmpty())
        <section class="wrap-product-new">
            <div id="" class="load-home other-product" data-url="{{ url('load-product') }}" data-section="home"
                data-type="san-pham" data-status="moi" data-paginate="5" data-other="1" data-slug="san-pham"
                data-eshow=".paging-product-home" data-template="List">
                <div class="wrap-content">
                    <div class="title-main-home">
                        <h2>Sản phẩm mới</h2>
                    </div>
                    <div class="paging-product-home"></div>
                </div>
            </div>
        </section>
    @endif

    <section class="wrap-recommend-home my-4" id="home-recommend-section" data-api-url="{{ url('api.recommendations') }}"
        data-limit="10" data-fetch-limit="{{ max((int) config('recommendation.max_limit', 60), 10) }}">
        <div class="wrap-content">
            <div class="title-main-home">
                <h2>Dành cho bạn</h2>
            </div>
            <div class="home-recommend-loading">Đang tải gợi ý...</div>
            <div class="home-recommend-list"></div>
            <div class="home-recommend-pagination d-none"></div>
            <div class="home-recommend-empty d-none">Chưa có gợi ý phù hợp lúc này.</div>
        </div>
    </section>

    @if ($banner_quangcao->isNotEmpty())
        <section class="wrap-banner-adv">
            <div class="wrap-content">
                <div class="banner-adv">
                    <div class="swiper swiper-auto"
                        data-swiper="autoplay:{delay: 5000,pauseOnMouseEnter:true,disableOnInteraction:false}|speed:1000|loop:true|navigation:{nextEl:'.-next',prevEl:'.-prev'}|effect:'fade'|fadeEffect: {crossFade: true}">
                        <div class="swiper-wrapper">
                            @foreach ($banner_quangcao as $k => $v)
                                <div class="swiper-slide">
                                    <a class="d-block" href="{{ $v['link'] ?? '' }}" title="{{ $v['namevi'] }}">
                                        @component('component.image', [
                                            'class' => 'w-100',
                                            'w' => config('type.photo.' . $v['type'] . '.width'),
                                            'h' => config('type.photo.' . $v['type'] . '.height'),
                                            'z' => config('type.photo.' . $v['type'] . '.opt'),
                                            'loading' => 'lazy',
                                            'fetchpriority' => 'low',
                                            'decoding' => 'async',
                                            'is_watermarks' => false,
                                            'destination' => 'photo',
                                            'image' => $v['photo'] ?? '',
                                            'alt' => $v['namevi'] ?? '',
                                        ])
                                        @endcomponent
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @endif
    @if ($productList->isNotEmpty())
        <section class="wrap-product-list-home">
            @foreach ($productList as $vlist)
                <div class="list-product other-product">
                    <div class="wrap-content">
                        <div class="box-title flex justify-between items-center">
                            <div class="title-pro">
                                <h2>{{ $vlist['name' . $lang] }}</h2>
                            </div>
                            <div class="list-c2">
                                @if (!empty($vlist->getCategoryCats))
                                    <div class="title-cat-main click-product">
                                        @foreach ($vlist->getCategoryCats ?? [] as $k => $cat)
                                            <span class="" data-url="{{ url('load-product') }}"
                                                data-cat="{{ $cat['id'] }}" data-list="{{ $vlist['id'] }}"
                                                data-paginate="4" data-eshow=".list-{{ $vlist['id'] }}"
                                                data-slug="{{ $cat['slug' . $lang] }}" data-type="san-pham"
                                                data-section="cat" data-template="Cat">
                                                {{ $cat['name' . $lang] }}
                                            </span>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="paging-product list-{{ $vlist['id'] }}"></div>


                    </div>
                </div>
            @endforeach
        </section>
    @endif
    @if (!empty($video))
        <section class="wrap-video-home my-4">
            <div class="wrap-content">
                <div class="video-home" data-aos="zoom-in" data-aos-duration="1000">
                    <div class="video-wrapper">
                        <iframe width="100%" height="100%" src="{{ Func::get_youtube($video['link'] ?? '') }}"
                            loading="lazy" referrerpolicy="strict-origin-when-cross-origin" title="Video 1"
                            frameborder="0"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                            allowfullscreen>
                        </iframe>
                    </div>
                </div>
            </div>
        </section>
    @endif

    @if ($blog->isNotEmpty())
        <section class="wrap-blog">
            <div class="wrap-content">
                <div class="title-main-home">
                    <h2>Tin Thời Trang</h2>
                </div>
                <div class="swiper swiper-auto"
                    data-swiper="slidesPerView:1|spaceBetween:10|breakpoints:{370: {slidesPerView:2,spaceBetween:10},575: {slidesPerView:2,spaceBetween:20},768: {slidesPerView:3,spaceBetween:20},992: {slidesPerView:3,spaceBetween:20}}|autoplay:{delay: 5000,pauseOnMouseEnter:true,disableOnInteraction:false}|speed:1000">
                    <div
                        class="swiper-wrapper row row-product row-cols-2 row-cols-sm-2 row-cols-md-3 row-cols-lg-3 flex-nowrap gutter-x-20">
                        @foreach ($blog as $v)
                            <div class="swiper-slide">
                                @component('component.itemNews', ['news' => $v])
                                    <p class="desc text-split">{{ $v['desc' . $lang] }}</p>
                                @endcomponent
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>
    @endif


    {{-- <article>
        <div class="wrap-content mb-4 mt-4">
            <div class="flex-about">
                <div class="content-about ">
                    <div class="title-about">
                        <h2>{{ $about['name' . $lang] }}</h2>
                    </div>
                    {!! Func::decodeHtmlChars($about['desc' . $lang]) !!}
                    <a href="gioi-thieu" class="view-about"><span>{{ __('web.xemthem') }} &#8594;</span></a>
                </div>
                <div class="photo-about ">
                    <a class="scale-img img block" href="gioi-thieu" title="{{ $about['name' . $lang] }}">
                        @component('component.image', [
    'rel' => 'preload',
    'class' => 'w-100',
    'w' => config('type.static.' . $about['type'] . '.images.photo.width'),
    'h' => config('type.static.' . $about['type'] . '.images.photo.height'),
    'z' => config('type.static.' . $about['type'] . '.images.photo.opt'),
    'breakpoints' => [
        400 => 400,
    ],
    'is_watermarks' => false,
    'destination' => 'news',
    'image' => $about['photo'] ?? '',
    'alt' => $about['name' . $lang] ?? '',
])
                        @endcomponent
                    </a>
                </div>
            </div>
        </div>
    </article>

    @if ($productNew->isNotEmpty())
        <div id="" class="wrap-product-home padding-top-bottom load-home other-product"
            data-url="{{ url('load-product') }}" data-section="home" data-type="san-pham" data-status="noibat"
            data-paginate="4" data-other="1" data-slug="san-pham" data-eshow=".paging-product-home">
            <div class="wrap-content">
                <div class="title-main-home">
                    <h2>{{ __('web.sanphamnoibat') }}</h2>
                </div>
                <div class="paging-product-home"></div>
            </div>
        </div>
    @endif
    @if ($productList->isNotEmpty())
        <div id="" class="product-list load-prolist padding-top-bottom other-product">
            <div class="wrap-content">
                <div class="title-main-home">
                    <h2>{{ __('web.sanphamnoibat') }}</h2>
                </div>
                <div class="list-c2">
                    <div class="title-list-main click-product">
                        @foreach ($productList as $k => $vlist)
                            <span class="" data-url="{{ url('load-product') }}" data-paginate="4" data-other="2"
                                data-slug="{{ $vlist['slug'.$lang] }}" data-section="list" data-list="{{ $vlist['id'] }}"
                                data-eshow=".paging-prolist">{{ $vlist['name' . $lang] }}</span>
                        @endforeach
                    </div>
                    <div class="sort-select" x-data="{ open: false }">
                        <p class="click-sort" @click="open = ! open">{{ __('web.sapxep') }}: <span
                                class="sort-show">{{ __('web.moinhat') }}</span></p>
                        <div class="sort-select-main sort" x-show="open">
                            <p><span data-sort="1" class="active"><i></i>{{ __('web.moinhat') }}</span></p>
                            <p><span data-sort="2"><i></i>{{ __('web.cunhat') }}</span></p>
                            <p><span data-sort="3"><i></i>{{ __('web.giacaodenthap') }}</span></p>
                            <p><span data-sort="4"><i></i>{{ __('web.giathapdencao') }}</span></p>
                        </div>
                    </div>
                </div>
                <div class="paging-prolist"></div>
            </div>
        </div>
    @endif
    @if ($productList->isNotEmpty())
        @foreach ($productList as $vlist)
            <div class="list-product other-product">
                <div class="wrap-content">
                    <div class="box-title flex justify-between items-center">
                        <div class="title-pro"><span>{{ $vlist['name' . $lang] }}</span></div>
                        <div class="list-c2">
                            @if (!empty($vlist->getCategoryCatsHome))
                                <div class="title-cat-main click-product">
                                    @foreach ($vlist->getCategoryCatsHome ?? [] as $k => $cat)
                                        <span class="" data-url="{{ url('load-product') }}"
                                            data-cat="{{ $cat['id'] }}" data-list="{{ $vlist['id'] }}"
                                            data-paginate="4" data-other="3" data-eshow=".list-{{ $vlist['id'] }}"
                                            data-slug="{{ $cat['slug' . $lang] }}" data-type="san-pham" data-section="cat"
                                            data-template="List">
                                            {{ $cat['name' . $lang] }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="paging-product list-{{ $vlist['id'] }}"></div>
                </div>
            </div>
        @endforeach
    @endif

    @if ($blog->isNotEmpty())
        <section>
            <div class="info-main info-news">
                <div class="wrap-content">
                    <div class="title-main">
                        <span>Tin tức</span>
                    </div>
                    <div class="swiper swiper-auto p-2"
                        data-swiper="slidesPerView:1|spaceBetween:10|breakpoints:{370: {slidesPerView:2,spaceBetween:10},575: {slidesPerView:2,spaceBetween:20},768: {slidesPerView:3,spaceBetween:20},992: {slidesPerView:3,spaceBetween:20}}|autoplay:{delay: 5000,pauseOnMouseEnter:true,disableOnInteraction:false}|speed:1000">
                        <div
                            class="swiper-wrapper row row-product row-cols-2 row-cols-sm-2 row-cols-md-3 row-cols-lg-3 flex-nowrap gutter-x-20">
                            @foreach ($blog as $v)
                                <div class="swiper-slide">
                                    @component('component.itemNews', ['news' => $v])
                                        <p class="desc text-split">{{ $v['desc' . $lang] }}</p>
                                    @endcomponent
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @endif

    @if ($video->isNotEmpty())
        <section>
            <div class="wrap-content my-3">
                <div class="flex-video">
                    <div class="wr-video">
                        <div class="title-main mb-3"><span>Video</span></div>
                        @component('component.videoSelect', ['video' => $video])
                        @endcomponent
                    </div>
                    <div class="wr-form">
                        <div class="title-main mb-3"><span>{{ __('web.dangkynhantin') }}</span></div>
                        <div class="frm_contact">
                            <form class="contact-form validation-letter" id="form-newsletter" novalidate method="post"
                                action="{{ url('lien-he-post') }}" enctype="multipart/form-data">
                                <div class="row-20 row">
                                    <div class="contact-input mb-3">
                                        <label for="fullname-contact" class="mb-2">{{ __('web.hoten') }}
                                            <span>(*)</span></label>
                                        <div class="form-floating-cus">
                                            <input type="text" name="dataContact[fullname]" class="form-control text-sm"
                                                id="fullname-contact" placeholder="{{ __('web.hoten') }}" value=""
                                                required>
                                        </div>
                                    </div>
                                    <div class="contact-input mb-3">
                                        <label for="phone-contact" class="mb-2">{{ __('web.dienthoai') }}
                                            <span>(*)</span></label>
                                        <div class="form-floating-cus">
                                            <input type="number" name="dataContact[phone]" id="phone-contact"
                                                class="form-control text-sm" placeholder="{{ __('web.dienthoai') }}"
                                                value="" required>
                                        </div>
                                    </div>
                                    <div class="contact-input mb-3">
                                        <label class="mb-2" for="email-contact">Email</label>
                                        <div class="form-floating-cus">
                                            <input type="email" class="form-control text-sm" id="email-contact"
                                                name="dataContact[email]" placeholder="Email" value="">
                                        </div>
                                    </div>
                                </div>
                                <div class="contact-input mb-3">
                                    <label class="mb-2" for="content-contact">{{ __('web.bancanhotrogi') }}</label>
                                    <div class="form-floating-cus">
                                        <textarea id="content-contact" class="form-control text-sm" name="dataContact[content]"
                                            placeholder="{{ __('web.bancanhotrogi') }}"></textarea>
                                    </div>
                                </div>
                                <input type="hidden" name="dataContact[type]" value="lien-he">
                                <input type="hidden" name="csrf_token" value="{{ csrf_token() }}">
                                <input type="submit" class="btn btn-primary btn-submit mr-2" name="submit-contact"
                                    value="{{ __('web.guiyeucau') }}">
                                <input type="hidden" name="recaptcha_response_contact" id="recaptchaResponseNewsletter">
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @endif

    @if ($partner->isNotEmpty())
        <section>
            <div class="wrap-content mb-4 mt-4">
                <div class="swiper swiper-auto"
                    data-swiper="slidesPerView:3|spaceBetween:10|breakpoints:{370: {slidesPerView:3,spaceBetween:10},575: {slidesPerView:4,spaceBetween:20},768: {slidesPerView:5,spaceBetween:20},992: {slidesPerView:6,spaceBetween:20}}|autoplay:{delay: 5000,pauseOnMouseEnter:true,disableOnInteraction:false}|speed:1000">
                    <div
                        class="swiper-wrapper row row-product row-cols-3 row-cols-sm-4 row-cols-md-5 row-cols-lg-6 flex-nowrap gutter-x-20">
                        @foreach ($partner as $k => $v)
                            <div class="swiper-slide col">
                                <a class="item-partner  scale-img" href="{{ $v['link'] }}"
                                    title="{{ $v['name' . $lang] }}">
                                    @component('component.image', [
    'class' => 'w-100',
    'w' => config('type.photo.' . $v['type'] . '.width'),
    'h' => config('type.photo.' . $v['type'] . '.height'),
    'z' => config('type.photo.' . $v['type'] . '.opt'),
    'breakpoints' => [
        400 => 200,
    ],
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
                    <div class="swiper-pagination -pagination"></div>
                    <div class="swiper-button-prev -prev"></div>
                    <div class="swiper-button-next -next"></div>
                </div>
            </div>
        </section>
    @endif

    @if ($tags->isNotEmpty())
        <section>
            <div class="footer-tags">
                <div class="wrap-content py-[15px]">
                    <p class="title-tags">{{ __('web.tagsanpham') }}</p>
                    <div class="flex-tags">
                        @foreach ($tags as $v)
                            <a href="{{ $v[$sluglang] }}" title="{{ $v['name' . $lang] }}">{{ $v['name' . $lang] }}</a>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>
    @endif --}}
@endsection

