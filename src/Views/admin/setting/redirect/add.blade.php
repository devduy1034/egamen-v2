@extends('layout')
@section('content')
<div class="container-xxl flex-grow-1 container-p-y container-fluid">
    <div class="app-ecommerce">
        <form class="validation-form" novalidate method="post" action="{{ url('admin',['com'=>$com,'act'=>'save','type'=>$type],['id'=>$item['id']??0,'page'=>$page]) }}" enctype="multipart/form-data">
            @component('component.buttonAdd') @endcomponent
            {!! Flash::getMessages('admin') !!}
            <div class="row">
                <div class="col-12 col-lg-12">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h3 class="card-title">Thông tin link điều hướng</h3>
                        </div>
                        <div class="card-body card-article">
                            <div class="form-group">
                                <label class="form-label" for="link">Url cần chuyển hướng:</label>
                                <input type="text" required class="form-control text-sm" name="data[link]" id="name" placeholder="Url cần chuyển hướng" value="{{ !empty(Flash::has('link')) ? Flash::get('link') : $item['link'] }}">
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="link">Url ??ch:</label>
                                <input type="text" required class="form-control  text-sm" name="data[link_redirect]" id="link_redirect" placeholder="Url ??ch" value="{{ !empty(Flash::has('link_redirect')) ? Flash::get('link_redirect') : $item['link_redirect'] }}">
                            </div>
                            <div class="form-group">
                                <label class="form-label mr-2">Loại chuyển hướng:</label>
                                <label class="switch switch-success">
                                    <input type="checkbox" class="switch-input" name="data[redirect]" {{ (!empty($item) && @$item['redirect']=='302')?'checked':'' }} />
                                    <span class="switch-toggle-slider">
                                        <span class="switch-on"><i class="ti ti-check"></i></span>
                                        <span class="switch-off"><i class="ti ti-x"></i></span>
                                    </span>
                                    <span class="switch-label">Chuyển hướng 302</span>
                                </label>
                            </div>

                            <div class="form-group mb-0">
                                <label for="numb" class="d-inline-block form-label align-middle mb-0 mr-2">Số thứ tự:</label>
                                <input type="number" class="form-control form-control-mini w-25 text-left d-inline-block align-middle text-sm" min="0" name="data[numb]" id="numb" placeholder="Số thứ tự" value="{{ !empty($item['numb']) ? $item['numb'] : 1 }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <input type="hidden" name="id" value="<?= !empty($item['id']) && $item['id'] > 0 ? $item['id'] : '' ?>">
            <input name="csrf_token" type="hidden" value="{{ csrf_token() }}">
            @component('component.buttonAdd') @endcomponent
        </form>
    </div>
</div>
@endsection