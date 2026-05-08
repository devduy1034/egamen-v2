<div class="item-news">
    <a class="img card-image block overflow-hidden scale-img" href="{{ $news[$sluglang] }}"
        title="{{ $news['name' . $lang] }}">
        @component('component.image', [
            'class' => 'w-100 lazy',
            'w' => config('type.news.' . $news['type'] . '.images.photo.width'),
            'h' => config('type.news.' . $news['type'] . '.images.photo.height'),
            'z' => config('type.news.' . $news['type'] . '.images.photo.opt'),
            'is_watermarks' => false,
            'destination' => 'news',
            'image' => $news['photo'] ?? '',
            'alt' => $news['name' . $lang] ?? '',
        ])
        @endcomponent
    </a>
    <div class="ds-news">
        <h3>
            <a class="text-decoration-none name-news" href="{{ $news[$sluglang] }}" title="{{ $news['name' . $lang] }}">
                {{ $news['name' . $lang] }}
            </a>
        </h3>
        <div class="time-news">
            <i class="bi bi-calendar3"></i>
            {{ $news->updated_at
                ? $news->updated_at->format('d/m/Y')
                : ($news->created_at
                    ? $news->created_at->format('d/m/Y')
                    : '') }}
        </div>
        {!! $slot !!}
    </div>
</div>
