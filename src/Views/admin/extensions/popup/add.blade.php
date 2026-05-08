@extends('layout')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y container-fluid">
    <div class="app-ecommerce">
        <form class="validation-form" novalidate method="post" action="{{ url('admin', ['com' => $com, 'act' => 'save', 'type' => $type]) }}" enctype="multipart/form-data">
            @component('component.buttonAdd')
            @endcomponent
            {!! Flash::getMessages('admin') !!}
            <div class="row">
                <div class="col-12 col-lg-8">
                    <div class="card card-primary card-outline text-sm mb-4">
                        <div class="card-header">
                            <h3 class="card-title">Loại {{ $configMan->title_main }} </h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <div class="custom-control custom-radio d-inline-block mr-3 text-md">
                                    <input class="custom-control-input status-ex" type="radio" id="status-cb" name="data[options][type]" {{ !empty($options['type']) && $options['type'] == 1 ? 'checked' : '' }} value="1">
                                    <label for="status-cb" class="custom-control-label font-weight-normal">Co
                                        bạn</label>
                                </div>
                                {{-- <div class="custom-control custom-radio d-inline-block mr-3 text-md">
                                    <input class="custom-control-input status-ex" type="radio" id="status-nc" name="data[options][type]" {{ !empty($options['type']) && $options['type'] == 2 ? 'checked' : '' }} value="2">
                                    <label for="status-nc" class="custom-control-label font-weight-normal">Nững
                                        cao (Form liên hệ)</label>
                                </div> --}}
                            </div>
                            <div class="cb-status {{ !empty($options['type']) && $options['type'] == 1 ? 'd-block' : 'd-none' }}">
                                <div class="row">
                                </div>
                            </div>
                            <div class="nc-status {{ !empty($options['type']) && $options['type'] == 2 ? 'd-block' : 'd-none' }}">
                                <div class="row">
                                    <h5 class="mb-3">Chọn mẫu</h5>

                                    @if (file_exists('assets/admin/img/popup'))
                                    @php
                                    $file = scandirFile('assets/admin/img/popup', 0);
                                    @endphp
                                    @foreach ($file as $k => $file)
                                    <div class="form-group col-md-4 col-sm-6">
                                        <div class="view-ext">
                                            <img onerror="this.src='../assets/images/noimage.png';" src="../assets/admin/img/popup/popup{{ $k + 1 }}.png" alt="popup{{ $k + 1 }}" />
                                        </div>
                                        <div class="custom-control custom-radio text-md mt-3">
                                            <input class="custom-control-input" type="radio" id="popup{{ $k + 1 }}" value="popup{{ $k + 1 }}" name="data[options][popup]" {{ !empty($options['popup']) && $options['popup'] == 'popup' . $k + 1 ? 'checked' : '' }} value="1">
                                            <label for="popup{{ $k + 1 }}" class="custom-control-label font-weight-normal">
                                                Mãu {{ $k + 1 }}
                                            </label>
                                        </div>
                                    </div>
                                    @endforeach
                                    @endif
                                </div>

                                <div class="row">

                                    <div class="form-group col-md-4 col-sm-6">
                                        <h5 class="mb-3 tt-15">Chọn trường</h5>
                                        <div class="flex-ip">
                                            <div class="custom-control custom-checkbox my-checkbox d-inline-block">
                                                <input type="checkbox" name="data[options][fullname]" class="custom-control-input" value="fullname" {{ !empty($options['fullname']) && $options['fullname'] != '' ? 'checked' : '' }}>
                                                <label for="selectall-checkbox" class="custom-control-label"></label>
                                            </div>
                                            <p class="d-inline-block">&nbsp; Họ tên</p>
                                        </div>
                                        <div class="flex-ip">
                                            <div class="custom-control custom-checkbox my-checkbox d-inline-block">
                                                <input type="checkbox" name="data[options][hotline]" class="custom-control-input" value="hotline" {{ !empty($options['hotline']) && $options['hotline'] != '' ? 'checked' : '' }}>
                                                <label for="selectall-checkbox" class="custom-control-label"></label>
                                            </div>
                                            <p class="d-inline-block">&nbsp; điện thoại</p>
                                        </div>
                                        <div class="flex-ip">
                                            <div class="custom-control custom-checkbox my-checkbox d-inline-block">
                                                <input type="checkbox" name="data[options][address]" class="custom-control-input" value="address" {{ !empty($options['address']) && $options['address'] != '' ? 'checked' : '' }}>
                                                <label for="selectall-checkbox" class="custom-control-label"></label>
                                            </div>
                                            <p class="d-inline-block">&nbsp; địa chỉ</p>
                                        </div>
                                        <div class="flex-ip">
                                            <div class="custom-control custom-checkbox my-checkbox d-inline-block">
                                                <input type="checkbox" name="data[options][email]" class="custom-control-input" value="email" {{ !empty($options['email']) && $options['email'] != '' ? 'checked' : '' }}>
                                                <label for="selectall-checkbox" class="custom-control-label"></label>
                                            </div>
                                            <p class="d-inline-block">&nbsp; Email</p>
                                        </div>
                                        <div class="flex-ip">
                                            <div class="custom-control custom-checkbox my-checkbox d-inline-block">
                                                <input type="checkbox" name="data[options][content]" class="custom-control-input" value="content" {{ !empty($options['content']) && $options['content'] != '' ? 'checked' : '' }}>
                                                <label for="selectall-checkbox" class="custom-control-label"></label>
                                            </div>
                                            <p class="d-inline-block">&nbsp; Nội dung</p>
                                        </div>
                                    </div>

                                    <div class="form-group col-md-4 col-sm-6">
                                        <h5 class="mb-3 tt-15">Trang hiển thị</h5>
                                        @if (!empty($configType->seopage))
                                        @foreach ($configType->seopage as $key => $value)
                                        <div class="flex-ip">
                                            <div class="custom-control custom-checkbox my-checkbox d-inline-block">
                                                <input type="checkbox" name="data[options][{{ $key }}]" class="custom-control-input" value="{{ $key }}" {{ !empty($options[$key]) && $options[$key] != '' ? 'checked' : '' }}>
                                                <label for="selectall-checkbox" class="custom-control-label"></label>
                                            </div>
                                            <p class="d-inline-block">&nbsp; {{ $value }}</p>
                                        </div>
                                        @endforeach
                                        @endif
                                    </div>

                                    <div class="form-group col-md-4 col-sm-6">
                                        <div class="mb-2">
                                            <h5 class="mb-2 tt-15">Màu nền khung</h5>
                                            <input type="text" class="form-control jscolor text-sm" name="data[options][background]" maxlength="7" value="{{ isset($options['background']) && $options['background'] != '' ? $options['background'] : '#000000' }}">
                                        </div>

                                        <div class="mb-2">
                                            <h5 class="mb-2 tt-15">Màu chữ tiêu đề</h5>
                                            <input type="text" class="form-control jscolor text-sm" name="data[options][color-title]" maxlength="7" value="{{ isset($options['color-title']) && $options['color-title'] != '' ? $options['color-title'] : '#000000' }}">
                                        </div>


                                        <div class="mb-2">
                                            <h5 class="mb-2 tt-15">Màu chữ nút gửi</h5>
                                            <input type="text" class="form-control jscolor text-sm" name="data[options][color-send]" maxlength="7" value="{{ isset($options['color-send']) && $options['color-send'] != '' ? $options['color-send'] : '#000000' }}">
                                        </div>

                                        <div class="mb-2">
                                            <h5 class="mb-2 tt-15">Màu nền nút gửi</h5>
                                            <input type="text" class="form-control jscolor text-sm" name="data[options][background-send]" maxlength="7" value="{{ isset($options['background-send']) && $options['background-send'] != '' ? $options['background-send'] : '#000000' }}">
                                        </div>
                                    </div>
                                </div>

                            </div>

                        </div>
                    </div>

                    <div class="card mb-4">
                        <div class="card-header">
                            <h3 class="card-title">Thông tin {{ $configMan->title_main }}
                            </h3>
                        </div>
                        <div class="card-body card-article">

                            <div class="card">
                                <ul class="nav nav-tabs" id="custom-tabs-three-tab-lang" role="tablist">
                                    @foreach (config('app.langs') as $k => $v)
                                    <li class="nav-item">
                                        <a class="nav-link {{ $k == 'vi' ? 'active' : '' }}" id="tabs-lang" data-bs-toggle="tab" data-bs-target="#tabs-lang-{{ $k }}" role="tab" aria-controls="tabs-lang-{{ $k }}" aria-selected="true">{{ $v }}</a>
                                    </li>
                                    @endforeach
                                </ul>
                                <div class="tab-content" id="custom-tabs-three-tabContent-lang">
                                    @foreach (config('app.langs') as $k => $v)
                                    <div class="tab-pane fade show {{ $k == 'vi' ? 'active' : '' }}" id="tabs-lang-{{ $k }}" role="tabpanel" aria-labelledby="tabs-lang">
                                        <div class="form-group">
                                            <label class="form-label" for="name{{ $k }}">Tiêu đề
                                                ({{ $k }})
                                                :</label>
                                            <input type="text" class="form-control  text-sm" name="data[name{{ $k }}]" id="name{{ $k }}" placeholder="Tiêu đề ({{ $k }})" value="{{ !empty(Flash::has('name' . $k)) ? Flash::get('name' . $k) : $item['name' . $k] }}">
                                        </div>

                                        <div class="form-group">
                                            <label class="form-label" for="content{{ $k }}">Nội dung
                                                ({{ $k }})
                                                :</label>
                                            <textarea class="form-control  text-sm" name="data[content{{ $k }}]" id="content{{ $k }}" rows="5" placeholder="Nội dung ({{ $k }})">{{ !empty(Flash::has('content' . $k)) ? Flash::get('content' . $k) : @$item['content' . $k] }}</textarea>
                                        </div>

                                        <div class="form-group">
                                            <label class="form-label" for="link{{ $k }}">Link
                                                ({{ $k }})
                                                :</label>
                                            <input type="text" class="form-control  text-sm" name="data[link{{ $k }}]" id="link{{ $k }}" placeholder="Link ({{ $k }})" value="{{ !empty(Flash::has('link' . $k)) ? Flash::get('link' . $k) : $item['link' . $k] }}">
                                        </div>


                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-lg-4">
                    @if (!empty($configMan->images))
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
                            $photoDetail['upload'] = 'photo';
                            $photoDetail['image'] = !empty($item) ? $item['photo'] : '';
                            $photoDetail['dimension'] =
                            'Width: ' .
                            $configMan->width .
                            ' px - Height: ' .
                            $configMan->height .
                            ' px (' .
                            config('type.type_img') .
                            ')';
                            @endphp
                            @component('component.image', ['photoDetail' => $photoDetail, 'photoAction' => 'photo'])
                            @endcomponent
                        </div>
                    </div>
                    @endif

                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Hiển thị
                                {{ $configMan->title_main }}
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                @php $status_array = !empty($item['status']) ? explode(',', $item['status']) : []; @endphp
                                @if (!empty($configMan->status))
                                @foreach ($configMan->status as $key => $value)
                                <div class="form-group d-inline-block mb-2 me-5">
                                    <label for="{{ $key }}-checkbox" class="d-inline-block align-middle mb-0 mr-2">{{ $value }}:</label>
                                    <label class="switch switch-success">

                                        <input type="checkbox" name="status[{{ $key }}]" class="switch-input custom-control-input show-checkbox" id="{{ $key }}-checkbox" {{ (empty($status_array) && empty($item['id']) ? 'checked' : in_array($key, $status_array)) ? 'checked' : '' }}>

                                        <span class="switch-toggle-slider">
                                            <span class="switch-on">
                                                <i class="ti ti-check"></i>
                                            </span>
                                            <span class="switch-off">
                                                <i class="ti ti-x"></i>
                                            </span>
                                        </span>

                                    </label>
                                </div>
                                @endforeach
                                @endif
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <input type="hidden" name="id" value="{{ !empty($item['id']) && $item['id'] > 0 ? $item['id'] : '' }}">
            <input name="csrf_token" type="hidden" value="{{ csrf_token() }}">

            @component('component.buttonAdd')
            @endcomponent
        </form>
    </div>
</div>
@endsection