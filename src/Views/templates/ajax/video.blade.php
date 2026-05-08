@if ($video)
    <div class="img-video scale-img text-decoration-none p-relative" data-fancybox="video"
        data-src="{{ Func::get_youtube_shorts($video['link']) }}" title="{{ $video['name' . $lang] }}">
        @component('component.image', [
            'class' => 'w-100',
            'w' => 300,
            'h' => 210,
            'z' => 1,
            'breakpoints' => [
                412 => 300,
            ],
            'is_watermarks' => false,
            'destination' => 'news',
            'image' => $video['photo'] ?? '',
            'alt' => $video['namevi'] ?? '',
        ])
        @endcomponent
        <span><i class="fa-solid fa-play"></i></span>
    </div>
@endif