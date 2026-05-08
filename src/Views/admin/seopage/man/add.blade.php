@extends('layout')

@section('content')
@php
    $languages = config('app.langconfig') == 'link' ? config('app.langs') : config('app.slugs');
@endphp
<div class="container-xxl flex-grow-1 container-p-y container-fluid">
    <div class="app-ecommerce">
        <form class="validation-form" novalidate method="post" action="{{ url('admin',['com'=>$com,'act'=>'save','type'=>$type],['id'=>$item['id']??0,'page'=>$page]) }}" enctype="multipart/form-data">
            @component('component.buttonAdd')
            @endcomponent

            {!! Flash::getMessages('admin') !!}
            <div class="row">
                <div class="col-12 col-lg-8">

                    <div class="card mb-4">
                        <div class="card-header">
                            <h3 class="card-title">Thông tin {{ $configMan->title_main }}
                            </h3>
                        </div>
                        <div class="card-body card-article">
                            <div class="card">
                                <ul class="nav nav-tabs" id="custom-tabs-three-tab-lang" role="tablist">
                                    @foreach ($languages as $k => $v)
                                    <li class="nav-item">
                                        <a class="nav-link {{ $k ==  config('app.lang_default') ? 'active' : '' }}" id="tabs-lang" data-bs-toggle="tab" data-bs-target="#tabs-lang-{{ $k }}" role="tab" aria-controls="tabs-lang-{{ $k }}" aria-selected="true">{{ $v }}</a>
                                    </li>
                                    @endforeach
                                </ul>
                                <div class="tab-content" id="custom-tabs-three-tabContent-lang">
                                    @foreach ($languages as $k => $v)
                                    <div class="tab-pane fade show {{ $k ==  config('app.lang_default') ? 'active' : '' }}" id="tabs-lang-{{ $k }}" role="tabpanel" aria-labelledby="tabs-lang">
                                        <div class="form-group last:!mb-0">
                                            <label class="form-label" for="title{{ $k }}">Title
                                                ({{ $k }})
                                                :</label>
                                            <input type="text" class="form-control text-sm" name="data[title{{ $k }}]" id="title{{ $k }}" placeholder="Title ({{ $k }})" value="{{ !empty(Flash::has('title' . $k)) ? Flash::get('title' . $k) : $item['title' . $k] }}" required>
                                        </div>

                                        <div class="form-group last:!mb-0">
                                            <label class="form-label" for="keywords{{ $k }}">Keywords
                                                ({{ $k }})
                                                :</label>
                                            <input type="text" class="form-control text-sm" name="data[keywords{{ $k }}]" id="keywords{{ $k }}" placeholder="Keywords ({{ $k }})" value="{{ !empty(Flash::has('keywords' . $k)) ? Flash::get('keywords' . $k) : $item['keywords' . $k] }}" required>
                                        </div>

                                        <div class="form-group last:!mb-0">
                                            <label class="form-label" for="description{{ $k }}">Description
                                                ({{ $k }})
                                                :</label>
                                            <textarea class="form-control for-seo text-sm" name="data[description{{ $k }}]" id="description{{ $k }}" rows="5" placeholder="Description ({{ $k }})">{{ !empty(Flash::has('description' . $k)) ? Flash::get('description' . $k) : @$item['description' . $k] }}</textarea>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-4">

                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Hình ảnh
                                {{ $configMan->title_main }}
                            </h5>
                        </div>
                        <div class="card-body">
                            @php
                            /* Photo detail */
                            $photoDetail = [];
                            $photoAction = 'photo';
                            $photoDetail['upload'] = 'seopage';
                            $photoDetail['image'] = !empty($item) ? $item['photo'] : '';
                            $photoDetail['id'] = !empty($item) ? $item['id'] : '';
                            $photoDetail['dimension'] =
                            'Width: 300px - Height: 200px (' . config('type.type_img') . ')';
                            @endphp
                            @component('component.image', ['photoDetail' => $photoDetail, 'photoAction' => 'photo'])
                            @endcomponent
                        </div>
                    </div>

                </div>
            </div>
            <input type="hidden" name="id" value="<?= !empty($item['id']) && $item['id'] > 0 ? $item['id'] : '' ?>">
            <input name="csrf_token" type="hidden" value="{{ csrf_token() }}">

            @component('component.buttonAdd')
            @endcomponent

        </form>
    </div>
</div>
@endsection