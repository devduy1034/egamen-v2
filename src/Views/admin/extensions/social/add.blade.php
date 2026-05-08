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
                    <div class="card mb-4">
                        <div class="card-header">
                            <h3 class="card-title">Thông tin {{ $configMan->title_main }}
                            </h3>
                        </div>
                        <div class="card-body card-article">
                            @if (file_exists('assets/admin/img/social'))
                            @php
                            $file = scandirFile('assets/admin/img/social', 0);
                            @endphp
                            @endif

                            <div class="card">
                                <ul class="nav nav-tabs" id="custom-tabs-three-tab-type" role="tablist">
                                    @foreach ($file as $k => $v)
                                    <li class="nav-item">
                                        <a class="nav-link {{ $k == 0 ? 'active' : '' }}" id="tabs-type" data-bs-toggle="tab" data-bs-target="#tabs-type-{{ $k + 1 }}" role="tab" aria-controls="tabs-type-{{ $k + 1 }}" aria-selected="true">Mãu {{ $k + 1 }}</a>
                                    </li>
                                    @endforeach
                                </ul>
                                <div class="tab-content" id="custom-tabs-three-tabContent-type">
                                    @foreach ($file as $k => $v)
                                    <div class="tab-pane fade show {{ $k == 0 ? 'active' : '' }}" id="tabs-type-{{ $k + 1 }}" role="tabpanel" aria-labelledby="tabs-type">
                                        <div class="form-group col-md-12 col-sm-12">
                                            <div class="view-ext">
                                                <img onerror="this.src='../assets/images/noimage.png';" src="../assets/admin/img/social/social{{ $k + 1 }}.png" alt="social{{ $k + 1 }}" />
                                            </div>
                                        </div>

                                        <div class="form-group col-md-12 col-sm-12">
                                            <div class="row">
                                                <div class="form-group col-md-4 col-sm-4">
                                                    <h5 class="mb-2 tt-15">Hiển thị</h5>
                                                    <label class="switch switch-success d-inline-block">
                                                        <input type="checkbox" name="data[options][social{{ $k + 1 }}][status]" class="switch-input custom-control-input show-checkbox" {{ !empty($options['social' . $k + 1]['status']) && $options['social' . $k + 1]['status'] != '' ? 'checked' : '' }}>
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

                                                <div class="form-group col-md-4 col-sm-4">
                                                    <div class="mb-2">
                                                        <h5 class="mb-2 tt-15">Background</h5>
                                                        <input type="text" class="form-control jscolor text-sm" name="data[options][social{{ $k + 1 }}][background]" maxlength="7" value="{{ !empty($options['social' . $k + 1]['background']) && $options['social' . $k + 1]['background'] != '' ? $options['social' . $k + 1]['background'] : '#000000' }}">
                                                    </div>
                                                </div>

                                                <div class="form-group col-md-4 col-sm-4">
                                                    <div class="mb-2">
                                                        <h5 class="mb-2 tt-15">Mãu ch?</h5>
                                                        <input type="text" class="form-control jscolor text-sm" name="data[options][social{{ $k + 1 }}][color]" maxlength="7" value="{{ !empty($options['social' . $k + 1]['color']) && $options['social' . $k + 1]['color'] != '' ? $options['social' . $k + 1]['color'] : '#000000' }}">
                                                    </div>
                                                </div>

                                                <div class="form-group mt-3 col-md-6 col-sm-6">
                                                    <label class="switch switch-success d-inline-block">
                                                        <input type="checkbox" name="data[options][social{{ $k + 1 }}][destop][device]" class="switch-input custom-control-input show-checkbox" {{ !empty($options['social' . $k + 1]['destop']['device']) && $options['social' . $k + 1]['destop']['device'] != '' ? 'checked' : '' }}>
                                                        <span class="switch-toggle-slider">
                                                            <span class="switch-on">
                                                                <i class="ti ti-check"></i>
                                                            </span>
                                                            <span class="switch-off">
                                                                <i class="ti ti-x"></i>
                                                            </span>
                                                        </span>
                                                    </label>
                                                    <span class="ms-5">Destop</span>
                                                </div>

                                                <div class="form-group col-md-12 col-sm-12 row">
                                                    <div class="form-group col-md-4 col-sm-12">
                                                        <h5 class="mb-2 tt-15">Khoảng cách trái (px)</h5>
                                                        <input type="numb" name="data[options][social{{ $k + 1 }}][destop][left]" value="{{ !empty($options['social' . $k + 1]['destop']['left']) && $options['social' . $k + 1]['destop']['left'] != '' ? $options['social' . $k + 1]['destop']['left'] : 0 }}" class="form-control">
                                                    </div>

                                                    <div class="form-group col-md-4 col-sm-12">
                                                        <h5 class="mb-2 tt-15">Khoảng cách phải (px)</h5>
                                                        <input type="numb" name="data[options][social{{ $k + 1 }}][destop][right]" value="{{ !empty($options['social' . $k + 1]['destop']['right']) && $options['social' . $k + 1]['destop']['right'] != '' ? $options['social' . $k + 1]['destop']['right'] : 0 }}" class="form-control">
                                                    </div>

                                                    <div class="form-group col-md-4 col-sm-12">
                                                        <h5 class="mb-2 tt-15">Khoảng cách bottom (px)</h5>
                                                        <input type="numb" name="data[options][social{{ $k + 1 }}][destop][bottom]" value="{{ !empty($options['social' . $k + 1]['destop']['bottom']) && $options['social' . $k + 1]['destop']['bottom'] != '' ? $options['social' . $k + 1]['destop']['bottom'] : 10 }}" class="form-control">
                                                    </div>
                                                </div>


                                                <div class="form-group mt-3 col-md-6 col-sm-6">
                                                    <label class="switch switch-success d-inline-block">
                                                        <input type="checkbox" name="data[options][social{{ $k + 1 }}][mobile][device]" class="switch-input custom-control-input show-checkbox" {{ !empty($options['social' . $k + 1]['mobile']['device']) && $options['social' . $k + 1]['mobile']['device'] != '' ? 'checked' : '' }}>
                                                        <span class="switch-toggle-slider">
                                                            <span class="switch-on">
                                                                <i class="ti ti-check"></i>
                                                            </span>
                                                            <span class="switch-off">
                                                                <i class="ti ti-x"></i>
                                                            </span>
                                                        </span>
                                                    </label>
                                                    <span class="ms-5">Mobile</span>
                                                </div>

                                                <div class="form-group col-md-12 col-sm-12 row">
                                                    <div class="form-group col-md-4 col-sm-12">
                                                        <h5 class="mb-2 tt-15">Khoảng cách trái (px)</h5>
                                                        <input type="numb" name="data[options][social{{ $k + 1 }}][mobile][left]" value="{{ !empty($options['social' . $k + 1]['mobile']['left']) && $options['social' . $k + 1]['mobile']['left'] != '' ? $options['social' . $k + 1]['mobile']['left'] : 0 }}" class="form-control">
                                                    </div>

                                                    <div class="form-group col-md-4 col-sm-12">
                                                        <h5 class="mb-2 tt-15">Khoảng cách phải (px)</h5>
                                                        <input type="numb" name="data[options][social{{ $k + 1 }}][mobile][right]" value="{{ !empty($options['social' . $k + 1]['mobile']['right']) && $options['social' . $k + 1]['mobile']['right'] != '' ? $options['social' . $k + 1]['mobile']['right'] : 0 }}" class="form-control">
                                                    </div>

                                                    <div class="form-group col-md-4 col-sm-12">
                                                        <h5 class="mb-2 tt-15">Khoảng cách bottom (px)</h5>
                                                        <input type="numb" name="data[options][social{{ $k + 1 }}][mobile][bottom]" value="{{ !empty($options['social' . $k + 1]['mobile']['bottom']) && $options['social' . $k + 1]['mobile']['bottom'] != '' ? $options['social' . $k + 1]['mobile']['bottom'] : 10 }}" class="form-control">
                                                    </div>
                                                </div>
                                            </div>
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
                            $photoDetail['upload'] = 'news';
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
                                    <label for="{{ $key }}-checkbox" class="d-inline-block align-middle mb-0 mr-2"><?= $value ?>:</label>
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