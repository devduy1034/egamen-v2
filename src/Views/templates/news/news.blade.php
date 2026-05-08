@extends('layout')
@section('content')
    <section>
        @if ($news->isNotEmpty())
            <div class="wrap-content py-3">
                <div class="title-detail">
                    <h1 class="text-center">{{ $titleMain }}</h1>
                    <h2 class="hidden">{{ $titleMain }}</h2>
                </div>
                <div class="grid-news">
                    @foreach ($news as $k => $v)
                        @component('component.itemNews', ['news' => $v])
                            <p class="desc-news line-clamp-3 mt-1">{{ $v['desc'.$lang] }}</p>
                        @endcomponent
                    @endforeach
                </div>
                {!! $news->links() !!}
            </div>
        @else
            <div class="wrap-content py-3">
                <div class="alert alert-warning w-100" role="alert">
                    <strong>{{ __('web.dangcapnhatdulieu') }}</strong>
                </div>
            </div>
        @endif
    </section>
@endsection