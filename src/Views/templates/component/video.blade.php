<div class="item-video">
    <div class="img card-image block overflow-hidden" data-fancybox="video"
        data-src="{{ Func::get_youtube_shorts($news['link']) }}" title="{{ $news['name' . $lang] }}">
        <div class="img-video scale-img p-relative">
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
                'image' => $news['photo'] ?? '',
                'alt' => $news['name' . $lang] ?? '',
            ])
            @endcomponent
            <span><i class="fa-solid fa-play"></i></span>
        </div>
        <h3 class="text-split">{{ $news['name' . $lang] }}</h3>
    </div>
</div>
