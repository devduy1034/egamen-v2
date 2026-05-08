@if (device() == 'mobile')
    @php
        $bottom = $val['mobile']['bottom'] ?? '';
        $left = !empty($val['mobile']['left']) ? $val['mobile']['left'] . 'px' : '';
        $right = !empty($val['mobile']['right']) ? $val['mobile']['right'] . 'px' : '';
    @endphp
@else
    @php
        $bottom = $val['destop']['bottom'] ?? '';
        $left = !empty($val['destop']['left']) ? $val['destop']['left'] . 'px' : '';
        $right = !empty($val['destop']['right']) ? $val['destop']['right'] . 'px' : '';
    @endphp
@endif
@php
    $background = $val['background'] ?? '';
    $backgroundText = $val['background-text'] ?? '';
    $color = $val['color'] ?? '';
    $location = !empty($left) ? 'left' : 'right';
    $hotline = !empty($extHotline['hotlinevi']) ? explode('-', $extHotline['hotlinevi']) : [];
    $socialThumb = config('type.extensions.social.thumb') ?? '35x35x1';
    $destop = !empty($val['destop']['device']) && device() == 'destop' ? true : false;
    $mobile = !empty($val['mobile']['device']) && device() == 'mobile' ? true : false;
@endphp

@if (!empty($destop) || !empty($mobile))
    <div class="toolbar-custom"  style="
    --background: #{{ $background }};
    --bottom: {{ $bottom }}px;
    --left: {{ $left }};
    --right: {{ $right }};
    --color: #{{ $color }};
    ">
        <div class="list-phone">
            @if (!empty($hotline))
                @foreach ($hotline as $hotline)
                <a href="tel:{{   preg_replace('/[^0-9]/', '', $hotline)}}" rel="nofollow">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32" width="256" height="256">
                        <path
                            d="M23.45 20.93a3 3 0 0 0-4.25 0l-1.45 1.45a26.14 26.14 0 0 1-4.51-3.62 26.62 26.62 0 0 1-3.62-4.51l1.45-1.45a3 3 0 0 0 0-4.25L8.24 5.73a2.93 2.93 0 0 0-2.11-.88h0A3 3 0 0 0 4 5.73L2.64 7.08A6.37 6.37 0 0 0 1 12.33c.32 3.75 2.78 8.26 6.57 12.06S15.92 30.64 19.67 31a7.87 7.87 0 0 0 .84 0 6.07 6.07 0 0 0 4.41-1.64L26.27 28a3 3 0 0 0 .88-2.13 2.93 2.93 0 0 0-.88-2.11zm1.41 5.66L23.5 27.94a4.57 4.57 0 0 1-3.66 1c-3.25-.28-7.39-2.58-10.81-6S3.31 15.41 3 12.16a4.53 4.53 0 0 1 1-3.66L5.41 7.14a1 1 0 0 1 .71-.29h0a1 1 0 0 1 .71.29L9.66 10a1 1 0 0 1 0 1.41l-2 2a1 1 0 0 0-.16 1.21 27.49 27.49 0 0 0 4.33 5.58 27.49 27.49 0 0 0 5.58 4.33 1 1 0 0 0 1.21-.16l2-2a1 1 0 0 1 1.41 0l2.83 2.83h0a1 1 0 0 1 .29.71A1 1 0 0 1 24.86 26.59zM26.6 5.39A14.92 14.92 0 0 0 16 1a1 1 0 1 0 0 2A13 13 0 0 1 29 16.11a1 1 0 0 0 1 1h0a1 1 0 0 0 1-1A14.91 14.91 0 0 0 26.6 5.39z"
                            class="color000 svgShape"></path>
                        <path
                            d="M20.91,11.11a6,6,0,0,1,1.77,4.31,1,1,0,0,0,1,1h0a1,1,0,0,0,1-1,8,8,0,0,0-8-8.1,1,1,0,1,0,0,2A6,6,0,0,1,20.91,11.11Z"
                            class="color000 svgShape"></path>
                    </svg>
                    <span>{{ $hotline }}</span>
                </a>
                @endforeach
            @endif
        </div>
        <div class="phone">
            <span id="goidien">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32" width="256" height="256">
                    <path
                        d="M23.45 20.93a3 3 0 0 0-4.25 0l-1.45 1.45a26.14 26.14 0 0 1-4.51-3.62 26.62 26.62 0 0 1-3.62-4.51l1.45-1.45a3 3 0 0 0 0-4.25L8.24 5.73a2.93 2.93 0 0 0-2.11-.88h0A3 3 0 0 0 4 5.73L2.64 7.08A6.37 6.37 0 0 0 1 12.33c.32 3.75 2.78 8.26 6.57 12.06S15.92 30.64 19.67 31a7.87 7.87 0 0 0 .84 0 6.07 6.07 0 0 0 4.41-1.64L26.27 28a3 3 0 0 0 .88-2.13 2.93 2.93 0 0 0-.88-2.11zm1.41 5.66L23.5 27.94a4.57 4.57 0 0 1-3.66 1c-3.25-.28-7.39-2.58-10.81-6S3.31 15.41 3 12.16a4.53 4.53 0 0 1 1-3.66L5.41 7.14a1 1 0 0 1 .71-.29h0a1 1 0 0 1 .71.29L9.66 10a1 1 0 0 1 0 1.41l-2 2a1 1 0 0 0-.16 1.21 27.49 27.49 0 0 0 4.33 5.58 27.49 27.49 0 0 0 5.58 4.33 1 1 0 0 0 1.21-.16l2-2a1 1 0 0 1 1.41 0l2.83 2.83h0a1 1 0 0 1 .29.71A1 1 0 0 1 24.86 26.59zM26.6 5.39A14.92 14.92 0 0 0 16 1a1 1 0 1 0 0 2A13 13 0 0 1 29 16.11a1 1 0 0 0 1 1h0a1 1 0 0 0 1-1A14.91 14.91 0 0 0 26.6 5.39z"
                        class="color000 svgShape"></path>
                    <path
                        d="M20.91,11.11a6,6,0,0,1,1.77,4.31,1,1,0,0,0,1,1h0a1,1,0,0,0,1-1,8,8,0,0,0-8-8.1,1,1,0,1,0,0,2A6,6,0,0,1,20.91,11.11Z"
                        class="color000 svgShape"></path>
                </svg>
            </span>
        </div>
        <ul>
            @foreach ($mxh as $mxh)
                @php
                    $socialSrc = !empty($mxh['photo'])
                        ? assets_photo('photo', $socialThumb, $mxh['photo'], 'thumbs')
                        : assets('assets/images/noimage.png');
                @endphp
                <li>
                    <a class=" text-decoration-none " rel="nofollow" href="{{ $mxh['link'] }}" title="{{ $mxh['namevi'] }}">
                        <img width="30" height="30" data-src="{{ $socialSrc }}" alt="{{ $mxh['namevi'] }}" class="lazy loaded"
                            src="{{ $socialSrc }}" data-was-processed="true" loading="lazy" decoding="async"><br>
                        <span>{{ $mxh['namevi'] }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
    </div>

    @push('styles')
        <link href="assets/css/social.css" rel="stylesheet">
    @endpush

    @push('scripts')
        <script type="text/javascript">
        $('.toolbar-custom').show(500);
            $(".toolbar-custom .phone").click(function (e) {
                e.stopPropagation();
                $(".toolbar-custom").toggleClass('is-active');
            });
            $(document).click(function () {
                $(".toolbar-custom").removeClass('is-active');
            });
        </script>
    @endpush
@endif
