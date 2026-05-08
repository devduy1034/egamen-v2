
@if (!empty($video))
    <div class="video-main">
        <div class="img-video scale-img text-decoration-none p-relative" data-fancybox="video"
            data-src="{{Func::get_youtube_shorts($video[0]['link'])}}" title="{{ $video[0]['name' . $lang] }}">
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
                'image' => $video[0]['photo'] ?? '',
                'alt' => $video[0]['name' . $lang] ?? '',
            ])
            @endcomponent
            <span><i class="fa-solid fa-play"></i></span>
        </div>
    </div>
    <select class="list-video" aria-label="List video">
        @foreach ($video as $k => $v)
            <option value="{{ $v['id'] }}">{{ $v['name' . $lang] }}</option>
        @endforeach
    </select>
@endif