@extends('layout')
@section('content')
    <section>
        <div class="wrap-content my-4">
            <div class="title-detail">
                <h1>{{ $titleMain }}</h1>
            </div>
            @if (!empty($brand))
                <div class="grid-product">
                    @foreach ($brand as $v)
                        <div class="product">
                            <div class="pic-product">
                                <a class="scale-img img block aspect-[300/200]" href="{{ $v[$sluglang] }}"
                                    title="{{ $v['name' . $lang] }}">
                                    @component('component.image', [
                                        'class' => 'w-100',
                                        'w' => 300,
                                        'h' => 200,
                                        'z' => 1,
                                        'breakpoints' => [
                                            412 => 300,
                                        ],
                                        'is_watermarks' => false,
                                        'destination' => 'product',
                                        'image' => $v['photo'] ?? '',
                                        'alt' => $v['name' . $lang] ?? '',
                                    ])
                                    @endcomponent
                                </a>
                            </div>
                            <h3 class="name-product">
                                <a class="text-split text-decoration-none" href="{{ $v[$sluglang] }}"
                                    title="{{ $v['name' . $lang] }}">{{ $v['name' . $lang] }}</a>
                            </h3>

                        </div>
                    @endforeach
                </div>
            @endif
            {!! $brand->appends(request()->query())->links() !!}
        </div>
    </section>
@endsection