@extends('layout')
@section('content')
    <section>
        <div class="wrap-content my-4">
            @if (!empty($static))
                <div class="title-detail">
                    <h1 class="text-center">{{ $static['name' . $lang] }}</h1>
                    <h2 class="hidden">{{ $static['name' . $lang] }}</h2>
                </div>
                <div class="meta-toc mw-700">
                    <div class="box-readmore">
                        <div class="tt-toc">{{ __('web.mucluc') }} <i class="fa-solid fa-list"></i></div>
                        <ul class="toc-list" data-toc="article" data-toc-headings="h1, h2, h3"></ul>
                    </div>
                </div>
                <div class="content-main w-clear" id="toc-content"> {!! Func::decodeHtmlChars($static['content' . $lang]) !!}</div>
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