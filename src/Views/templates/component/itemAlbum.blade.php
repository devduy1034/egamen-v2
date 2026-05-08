<div class="box-cont" >
    <div class="picture">
        <a class="img block " href="{{ url('slugweb',['slug'=>$news['slug']]) }}" title="{{ $news['name'.$lang] }}">
             @component('component.image', [
                'class' => 'w-100',
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
    </div>
    <h3 class="name mb-0 text-center">
        <a class="text-split text-decoration-none" href="{{ url('slugweb',['slug'=>$news['slug']]) }}" title="{{ $news['name'.$lang] }}">{{ $news['name'.$lang] }}</a>
    </h3>
</div>