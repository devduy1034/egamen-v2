@php
    $image = $image ?? '';
    $class = $class ?? '';
    $destination = $destination ?? 'product';
    $alt = $alt ?? '';
    $w = $w ?? 10;
    $h = $h ?? 10;
    $z = $z ?? 0;
    $loading = $loading ?? null;
    $fetchpriority = $fetchpriority ?? '';
    $decoding = $decoding ?? '';
    $breakpoints ??= [];
    $is_lazy = strpos($class, 'lazy') === false ? false : true;
    $is_watermarks ??= false;

    $type ??= $is_watermarks ? 'watermarks' : 'thumbs';

    $thumb = $z != 0 ? $type : '';

    $srcset_attr = 'srcset';
    $src_attr = 'src';
    $loading = $loading ?? ($is_lazy ? 'lazy' : '');
    $loading_attr = !empty($loading) ? 'loading="' . $loading . '"' : '';
    $fetchpriority_attr = !empty($fetchpriority) ? 'fetchpriority="' . $fetchpriority . '"' : '';
    $decoding_attr = !empty($decoding) ? 'decoding="' . $decoding . '"' : '';

    if (!empty($breakpoints)) {
        $breakpoints[$w] = $w;
        krsort($breakpoints);
    }

    if (!empty($breakpoints) && !empty($image))
    $error_src = 'onerror="if(this.parentNode.tagName==\'PICTURE\') this.parentNode.replaceWith(this);this.src=\'' . thumbs('thumbs/' . $w . 'x' . $h . 'x' . $z . '/assets/images/noimage.png.webp') . '\';"';
    else
    $error_src = 'onerror="this.src=`' . thumbs('thumbs/' . $w . 'x' . $h . 'x' . $z . '/assets/images/noimage.png.webp') . '`"';

@endphp

@if (!empty($breakpoints) && !empty($image))
    <picture class="w-100">
        @foreach ($breakpoints as $bp => $brk_w)
            @php
                $brk_h = ceil(($brk_w * $h) / $w);
            @endphp
            <source media="(min-width: {{ $bp }}px)" {{ $srcset_attr }}="{{ assets_photo($destination, $brk_w . 'x' . $brk_h . 'x' . $z, $image, $thumb) }}" width="{{ $brk_w }}" height="{{ $brk_h }}"  type="image/webp">
        @endforeach
        <img {!! $loading_attr !!} {!! $fetchpriority_attr !!} {!! $decoding_attr !!} class="{{ $class }}" {!! $error_src !!} {{ $src_attr }}="{{ assets_photo($destination, $w . 'x' . $h . 'x' . $z, $image, $thumb) }}" alt="{{ $alt }}" width="{{ $w }}" height="{{ $h }}">
    </picture>
@else
    <img {!! $loading_attr !!} {!! $fetchpriority_attr !!} {!! $decoding_attr !!} class="{{ $class }}" {!! $error_src !!} {{ $src_attr }}="{{ assets_photo($destination, $w . 'x' . $h . 'x' . $z, $image, $thumb) }}" alt="{{ $alt }}" width="{{ $w }}" height="{{ $h }}">
@endif
