@extends('layout')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y container-fluid">
    <div class="app-ecommerce">
        <form class="validation-form" novalidate method="post"
            action="{{ url('admin', ['com' => $com, 'act' => 'save', 'type' => $type], ['id' => $item['id'] ?? 0,'page'=>$page]) }}"
            enctype="multipart/form-data">
            @component('component.buttonAdd', ['params' => ['id_parent' => $id_parent]])
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
            
                            <div class="form-group mt-3">
                                <label class="form-label" for="content"><strong>Nội dung:</strong></label>
                                <p class="content-link-in">{!!$item['content']??''!!}</p>
                            </div>

                            <div class="form-group mt-3">
                                <label class="form-label" for="link"><strong>Link:</strong></label>
                                <input type="text" class="form-control for-seo text-sm" name="data[link]"
                                    id="link" placeholder="Link"
                                    value="{{ !empty(Flash::has('link')) ? Flash::get('link') : $item['link'] }}">
                            </div>


                        </div>
                    </div>
                </div>
                <div class="col-12 col-lg-4">

                    <div class="card mb-4 form-group-category">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Trạng thái</h5>
                        </div>
                        <div class="card-body ">
                            <div class="form-group">
                                <label class="form-label" for="id_list">{!! Func::decodeHtmlChars(Func::checkWebsiteStatus($item['link']??'')) !!}</label>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <input type="hidden" name="data[type_link]"
                value="<?= !empty($item['type_link']) && $item['type_link'] > 0 ? $item['type_link'] : '' ?>">
            <input type="hidden" name="id"
                value="<?= !empty($item['id']) && $item['id'] > 0 ? $item['id'] : '' ?>">
            <input type="hidden" name="id_parent"
                value="<?= !empty($item['id_parent']) && $item['id_parent'] > 0 ? $item['id_parent'] : '' ?>">
            <input name="csrf_token" type="hidden" value="{{ csrf_token() }}">
            @component('component.buttonAdd', ['params' => ['id_parent' => $id_parent]])
            @endcomponent
        </form>
    </div>
</div>
@endsection
