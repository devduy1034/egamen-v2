@extends('layout')
@section('content')
    <div>
        <div class="max-width py-3 mt-4">
            @if ($photo->isNotEmpty())
                <div class="title-main">
                    <h1>{{ $titleMain }}</h1>
                </div>
                <div class="album-product row">
                    @foreach ($photo as $v)
                        <div class="col-lg-3 col-md-4 col-sm-6 col-6 mb-4">
                            <div class="video">
                                <p class="pic-video">
                                    <a class="scale-img text-decoration-none" data-fancybox="video" data-src="{!!Func::get_youtube_shorts($v['link_video'])!!}" title="{{  $v['name' . $lang] }}">
                                        <img onerror="this.src={{ thumbs('thumbs/380x380x1/assets/images/noimage.png') }}';" src="https://img.youtube.com/vi/{!!Func::getYoutube($v['link_video'])!!}/0.jpg" alt="{{  $v['name' . $lang] }}" />
                                    </a>
                                </p>
                                <h3>
                                    <a class="name-video text-split scale-img text-decoration-none" title="{{  $v['name' . $lang] }}">{{  $v['name' . $lang] }}</a>
                                </h3>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
@endsection
