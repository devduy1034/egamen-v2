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
                            <div class="row">
                                <h5 class="mb-3">Chọn mẫu</h5>
                                @if (file_exists('assets/admin/img/hotline'))
                                @php
                                $file = scandirFile('assets/admin/img/hotline', 0);
                                @endphp
                                @foreach ($file as $k => $file)
                                <div class="form-group col-md-4 col-sm-6">
                                    <div class="view-ext">
                                        <img onerror="this.src='../assets/images/noimage.png';" src="../assets/admin/img/hotline/hotline{{ $k + 1 }}.png" alt="hotline{{ $k + 1 }}" />
                                    </div>
                                    <div class="custom-control custom-radio text-md mt-3">
                                        <input class="custom-control-input" type="radio" id="hotline{{ $k + 1 }}" value="hotline{{ $k + 1 }}" name="data[options][hotline]" {{ !empty($options['hotline']) && $options['hotline'] == 'hotline' . $k + 1 ? 'checked' : '' }} value="1">
                                        <label for="hotline{{ $k + 1 }}" class="custom-control-label font-weight-normal">
                                            Mãu {{ $k + 1 }}
                                        </label>
                                    </div>
                                </div>
                                @endforeach
                                @endif
                            </div>

                            <div class="row">

                                <div class="form-group col-md-4 col-sm-4">
                                    <div class="mb-2">
                                        <h5 class="mb-2 tt-15">Background (icon)</h5>
                                        <input type="text" class="form-control jscolor text-sm" name="data[options][background]" maxlength="7" value="{{ !empty($options['background']) && $options['background'] != '' ? $options['background'] : '#000000' }}">
                                    </div>
                                </div>

                                <div class="form-group col-md-4 col-sm-4">
                                    <div class="mb-2">
                                        <h5 class="mb-2 tt-15">Background (hotline)</h5>
                                        <input type="text" class="form-control jscolor text-sm" name="data[options][background-text]" maxlength="7" value="{{ !empty($options['background-text']) && $options['background-text'] != '' ? $options['background-text'] : '#000000' }}">
                                    </div>
                                </div>

                                <div class="form-group col-md-4 col-sm-4">
                                    <div class="mb-2">
                                        <h5 class="mb-2 tt-15">Mãu ch?</h5>
                                        <input type="text" class="form-control jscolor text-sm" name="data[options][color]" maxlength="7" value="{{ !empty($options['color']) && $options['color'] != '' ? $options['color'] : '#000000' }}">
                                    </div>
                                </div>

                                <div class="form-group col-md-12 col-sm-12">
                                    <h5 class="mb-2 tt-15">Thiết bị</h5>
                                    <div class="row">
                                        <div class="form-group mt-3 col-md-6 col-sm-6">
                                            <label class="switch switch-success d-inline-block">
                                                <input type="checkbox" name="data[options][destop][device]" class="switch-input custom-control-input show-checkbox" {{ !empty($options['destop']['device']) && $options['destop']['device'] != '' ? 'checked' : '' }}>
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
                                                <input type="numb" name="data[options][destop][left]" value="{{ !empty($options['destop']['left']) && $options['destop']['left'] != '' ? $options['destop']['left'] : 0 }}" class="form-control">
                                            </div>

                                            <div class="form-group col-md-4 col-sm-12">
                                                <h5 class="mb-2 tt-15">Khoảng cách phải (px)</h5>
                                                <input type="numb" name="data[options][destop][right]" value="{{ !empty($options['destop']['right']) && $options['destop']['right'] != '' ? $options['destop']['right'] : 0 }}" class="form-control">
                                            </div>

                                            <div class="form-group col-md-4 col-sm-12">
                                                <h5 class="mb-2 tt-15">Khoảng cách bottom (px)</h5>
                                                <input type="numb" name="data[options][destop][bottom]" value="{{ !empty($options['destop']['bottom']) && $options['destop']['bottom'] != '' ? $options['destop']['bottom'] : 10 }}" class="form-control">
                                            </div>
                                        </div>

                                        <div class="form-group mt-3 col-md-6 col-sm-6">
                                            <label class="switch switch-success d-inline-block">
                                                <input type="checkbox" name="data[options][mobile][device]" class="switch-input custom-control-input show-checkbox" {{ !empty($options['mobile']['device']) && $options['mobile']['device'] != '' ? 'checked' : '' }}>
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
                                                <input type="numb" name="data[options][mobile][left]" value="{{ !empty($options['mobile']['left']) && $options['mobile']['left'] != '' ? $options['mobile']['left'] : 0 }}" class="form-control">
                                            </div>

                                            <div class="form-group col-md-4 col-sm-12">
                                                <h5 class="mb-2 tt-15">Khoảng cách phải (px)</h5>
                                                <input type="numb" name="data[options][mobile][right]" value="{{ !empty($options['mobile']['right']) && $options['mobile']['right'] != '' ? $options['mobile']['right'] : 0 }}" class="form-control">
                                            </div>

                                            <div class="form-group col-md-4 col-sm-12">
                                                <h5 class="mb-2 tt-15">Khoảng cách bottom (px)</h5>
                                                <input type="numb" name="data[options][mobile][bottom]" value="{{ !empty($options['mobile']['bottom']) && $options['mobile']['bottom'] != '' ? $options['mobile']['bottom'] : 10 }}" class="form-control">
                                            </div>
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
                                            <input type="text" class="form-control  text-sm" name="data[name{{ $k }}]" id="name{{ $k }}" placeholder="Tiêu đề ({{ $k }})" value="{{ !empty(Flash::has('namevi')) ? Flash::get('namevi') : $item['name' . $k] }}" required>
                                        </div>

                                        <div class="form-group">
                                            <label class="form-label" for="hotline{{ $k }}">Điện thoại
                                                ({{ $k }})
                                                :</label>
                                            <input type="text" class="form-control  text-sm" name="data[hotline{{ $k }}]" id="hotline{{ $k }}" placeholder="Hotline ({{ $k }})" value="{{ !empty(Flash::has('hotline' . $k)) ? Flash::get('hotline' . $k) : $item['hotline' . $k] }}" required>
                                            <p class="mt-2" style="color:#f00;font-style:italic">Nếu nhiều hơn 2
                                                số điện thoại bạn vui lòng nhập ký tự "-" giữa các số, ví dụ 0169...
                                                - 0168...</p>
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