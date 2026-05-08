@extends('layout')
@section('content')
    <section>
        <div class="wrap-content my-4">
            <div class="flex-news-detail">
                <div class="news-left">
                    @if (!empty($rowDetail))
                        <div class="title-detail">
                            <h1><?= $rowDetail['name' . $lang] ?></h1>
                        </div>
                        <div class="meta-toc">
                            <div class="box-readmore">
                                <div class="tt-toc">{{ __('web.mucluc') }} <i class="fa-solid fa-list"></i></div>
                                <ul class="toc-list" data-toc="article" data-toc-headings="h1, h2, h3"></ul>
                            </div>
                        </div>
                        @if (!empty($rowDetail['desc' . $lang]))
                            <div class="content-main baonoidung w-clear"> {!! Func::decodeHtmlChars($rowDetail['desc' . $lang]) !!}</div>
                        @endif
                        <div class="content-main baonoidung w-clear" id="toc-content"> {!! Func::decodeHtmlChars($rowDetail['content' . $lang]) !!}</div>
                        @if (!empty($rowDetailPhoto))
                            <div class="grid-news">
                                @foreach ($rowDetailPhoto as $v)
                                    <div class="box-album" data-fancybox="gallery"
                                        data-src="{{ assets_photo('news', '710x440x1', $v['photo'], '') }}">
                                        <div class="scale-img">
                                            @component('component.image', [
                                                'class' => 'w-100',
                                                'w' => 390,
                                                'h' => 300,
                                                'z' => 1,
                                                'breakpoints' => [
                                                    412 => 390,
                                                ],
                                                'is_watermarks' => false,
                                                'destination' => 'news',
                                                'image' => $v['photo'] ?? '',
                                                'alt' => $rowDetail['name' . $lang] ?? '',
                                            ])
                                            @endcomponent
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
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
                <div class="news-right">
                    <div class="title-main mb-3">
                        <span>{{ __('web.baivietkhac') }}</span>
                    </div>
                    @if (!empty($news))
                        <div class="wr-news-detail">
                            <div class="item-news-one mb-4">
                                <a class="img block overflow-hidden" href="{{ $news[0][$sluglang] }}"
                                    title="{{ $news[0]['name' . $lang] }}">
                                    <div class="scale-img">
                                        @component('component.image', [
                                            'class' => 'w-100',
                                            'w' => 1200,
                                            'h' => 800,
                                            'z' => 1,
                                            'is_watermarks' => false,
                                            'destination' => 'news',
                                            'image' => $news[0]['photo'] ?? '',
                                            'alt' => $news[0]['name' . $lang] ?? '',
                                        ])
                                        @endcomponent
                                    </div>
                                    <div class="ds-news">
                                        <h3 class="text-split my-2">{{ $news[0]['name' . $lang] }}</h3>
                                        <p class="desc text-split">{{ $news[0]['desc' . $lang] }}</p>
                                    </div>
                                </a>
                            </div>
                        </div>
                        @foreach ($news->skip(1) as $v)
                            <div class="item-news-all mb-3">
                                <a class="img block overflow-hidden" href="{{ $v[$sluglang] }}"
                                    title="{{ $v['name' . $lang] }}">
                                    <div class="scale-img">
                                        @component('component.image', [
                                            'class' => 'w-100',
                                            'w' => 300,
                                            'h' => 225,
                                            'z' => 1,
                                            'is_watermarks' => false,
                                            'destination' => 'news',
                                            'image' => $v['photo'] ?? '',
                                            'alt' => $v['name' . $lang] ?? '',
                                        ])
                                        @endcomponent
                                    </div>
                                    <div class="ds-news">
                                        <h3 class="text-split">{{ $v['name' . $lang] }}</h3>
                                    </div>
                                </a>
                            </div>
                        @endforeach
                    @endif
                </div>

            </div>
        </div>
    </section>
@endsection

@pushOnce('scripts')
    <script src="@asset('assets/toc/toc.js')"></script>
    <script>
        if (isExist($('.toc-list'))) {
            $('.toc-list').toc({
                content: 'div#toc-content',
                headings: 'h2,h3,h4'
            });

            if (!$('.toc-list li').length) $('.meta-toc').hide();
            if (!$('.toc-list li').length) $('.meta-toc .mucluc-dropdown-list_button').hide();

            $('.toc-list')
                .find('a')
                .click(function() {
                    var x = $(this).attr('data-rel');
                    goToByScroll(x);
                });

            $('body').on('click', '.mucluc-dropdown-list_button', function() {
                $('.box-readmore').slideToggle(200);
            });

            $(document).scroll(function() {
                var y = $(this).scrollTop();
                if (y > 300) {
                    $('.meta-toc').addClass('fiedx');
                } else {
                    $('.meta-toc').removeClass('fiedx');
                }
            });
        }
    </script>
@endPushOnce
