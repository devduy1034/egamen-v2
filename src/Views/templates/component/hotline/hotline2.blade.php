@if (device() == 'mobile')
    @php
        $bottom = $opt['mobile']['bottom'] ?? '';
        $left = !empty($opt['mobile']['left']) ? $opt['mobile']['left'] . 'px' : '';
        $right = !empty($opt['mobile']['right']) ? $opt['mobile']['right'] . 'px' : '';
    @endphp
@else
    @php
        $bottom = $opt['destop']['bottom'] ?? '';
        $left = !empty($opt['destop']['left']) ? $opt['destop']['left'] . 'px' : '';
        $right = !empty($opt['destop']['right']) ? $opt['destop']['right'] . 'px' : '';
    @endphp
@endif

@php
    $background = $opt['background'] ?? '';
    $backgroundText = $opt['background-text'] ?? '';
    $color = $opt['color'] ?? '';
    $location = !empty($left) ? 'left' : 'right';
    $hotline = preg_replace('/[^0-9]/', '', $val['hotlinevi']);
    $photo = !empty($val['photo'])
        ? assets_photo('photo', config('type.extensions.hotline.thumb') ?? '35x35x1', $val['photo'], 'thumbs')
        : assets('assets/images/noimage.png');
    $destop = !empty($opt['destop']['device']) && device() == 'destop' ? true : false;
    $mobile = !empty($opt['mobile']['device']) && device() == 'mobile' ? true : false;
@endphp

@if (!empty($destop) || !empty($mobile))
    <a id="hotline"
        style="
        --background: #{{ $background }};
        --bottom: {{ $bottom }}px;
        --left: {{ $left }};
        --right: {{ $right }};
    "
        class="btn-phone btn-frame text-decoration-none" href="tel:{{ $hotline }}">
        <div class="animated infinite zoomIn kenit-alo-circle"></div>
        <div class="animated infinite pulse kenit-alo-circle-fill"></div>
        <i><img onerror="this.src='assets/images/noimage.png';" src="{{ $photo }}" alt="hotline" title="hotline"
                width="35" /></i>
        <span
            style="{{ $location }} : 25px;--color:#{{ $color }};--backgroundText: #{{ $backgroundText }}">{{ $hotline }}</span>
    </a>

    @push('styles')
        <link href="assets/css/hotline.css" rel="stylesheet">
    @endpush
    @push('scripts')
        <script type="text/javascript">
            $('#hotline').show(500);
        </script>
    @endpush
@endif
