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
    $destop = !empty($val['destop']['device']) && device() == 'destop' ? true : false;
    $mobile = !empty($val['mobile']['device']) && device() == 'mobile' ? true : false;
    $socialThumb = config('type.extensions.social.thumb') ?? '35x35x1';
@endphp

@if (!empty($destop) || !empty($mobile))
    <div class="toolbar"
        style="
    --background: #{{ $background }};
    --bottom: {{ $bottom }}px;
    --left: {{ $left }};
    --right: {{ $right }};
    --color: #{{ $color }};
    ">
        <ul>
            @foreach ($mxh as $mxh)
                @php
                    $socialSrc = !empty($mxh['photo'])
                        ? assets_photo('photo', $socialThumb, $mxh['photo'], 'thumbs')
                        : assets('assets/images/noimage.png');
                @endphp
                <li>
                    <a class=" text-decoration-none " href="{{ $mxh['link'] }}" title="{{ $mxh['namevi'] }}">
                        <img data-src="{{ $socialSrc }}" alt="{{ $mxh['namevi'] }}" class="lazy loaded"
                            src="{{ $socialSrc }}"
                            data-was-processed="true" loading="lazy" decoding="async"><br>
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
            $('.toolbar').show(500);
        </script>
    @endpush
@endif
