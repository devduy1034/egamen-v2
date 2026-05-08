@extends('layout')
@section('content')
    <div>
        <div class="max-width py-3">
            <div class="title-detail">
                <h1>{{$titleMain}}</h1>
            </div>
            @if (!empty($news))
                <div class="row">
                    @foreach ($news as $v)
                        <div class="col-lg-3 col-md-4 col-sm-6 col-6 mb-3">
                            <div class="album">
                                @component('component.itemAlbum', ['news' => $v])
                                @endcomponent
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
            {!! $news->links() !!}
        </div>
    </div>
@endsection
