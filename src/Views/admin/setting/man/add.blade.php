@extends('layout')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y container-fluid">
        <div class="app-ecommerce" x-data="$store.UrlAdmin">
            <form class="validation-form" novalidate method="post" action="{{ url('admin',['com'=>$com,'act'=>'save','type'=>$type],['id'=>$item['id']??0]) }}" enctype="multipart/form-data">
                <div class="btn-header d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-3 bg-navbar-theme navbar-detached">
                    <div class="d-flex align-content-center flex-wrap gap-2" >
                        <div class="d-flex gap-2">
                            <button type="submit" :class="(!$store.UrlAdmin.checkForLetter || !$store.UrlAdmin.isValidLength)?'disabled':''" class="btn btn-primary submit-check waves-effect"><i class="ti ti-device-floppy mr-2"></i> Luu</button>
                            <button type="reset" class="btn btn-secondary waves-effect"><i class="ti ti-repeat mr-2"></i> Làm lại</button>
                            <a class="btn btn-danger color-white waves-effect" href="{{ url('admin',['com'=>$com,'act'=>'man','type'=>$type],[]) }}" title="Thoát"><i class="ti ti-logout mr-2"></i>Thoát</a>
                        </div>
                    </div>
                </div>
                {!! Flash::getMessages('admin') !!}
                @if (config('app.environment') == 'dev')
                    <div class="card card-primary card-outline text-sm mb-4">
                        <div class="card-header">
                            <h3 class="card-title">Cấu hình email server</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <div class="custom-control custom-radio d-inline-block mr-3 text-md">
                                    <input class="custom-control-input mailertype" type="radio" id="mailertype-host"
                                        name="data[options][mailertype]"
                                        {{ $options['mailertype'] == 1 || $options['mailertype'] == 0 ? 'checked' : '' }}
                                        value="1">
                                    <label for="mailertype-host" class="custom-control-label font-weight-normal">Host
                                        email</label>
                                </div>
                                <div class="custom-control custom-radio d-inline-block mr-3 text-md">
                                    <input class="custom-control-input mailertype" type="radio" id="mailertype-gmail"
                                        name="data[options][mailertype]" {{ $options['mailertype'] == 2 ? 'checked' : '' }}
                                        value="2">
                                    <label for="mailertype-gmail" class="custom-control-label font-weight-normal">Gmail
                                        email</label>
                                </div>
                            </div>
                            <div
                                class="host-email {{ $options['mailertype'] == 1 || $options['mailertype'] == 0 ? 'd-block' : 'd-none' }}">
                                <div class="row">
                                    <div class="form-group col-md-4 col-sm-6">
                                        <label for="ip_host">Host:</label>
                                        <input type="text" class="form-control text-sm" name="data[options][ip_host]"
                                            id="ip_host" placeholder="Host" value="{{ $options['ip_host'] }}">
                                    </div>
                                    <div class="form-group col-md-4 col-sm-6">
                                        <label for="port_host">Port:</label>
                                        <input type="text" class="form-control text-sm" name="data[options][port_host]"
                                            id="port_host" placeholder="Port" value="{{ $options['port_host'] }}">
                                    </div>
                                    <div class="form-group col-md-4 col-sm-6">
                                        <label for="secure_host">Secure:</label>
                                        <select class="form-control custom-select text-sm" name="data[options][secure_host]"
                                            id="secure_host">
                                            <option {{ $options['secure_host'] == 'tls' ? 'selected' : '' }} value="tls">
                                                TLS
                                            </option>
                                            <option {{ $options['secure_host'] == 'ssl' ? 'selected' : '' }} value="ssl">
                                                SSL
                                            </option>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-4 col-sm-6">
                                        <label for="email_host">Email host:</label>
                                        <input type="text" class="form-control text-sm" name="data[options][email_host]"
                                            id="email_host" placeholder="Email host" value="{{ $options['email_host'] }}">
                                    </div>
                                    <div class="form-group col-md-4 col-sm-6">
                                        <label for="password_host">Password host:</label>
                                        <input type="text" class="form-control text-sm"
                                            name="data[options][password_host]" id="password_host"
                                            placeholder="Password host" value="{{ $options['password_host'] }}">
                                    </div>
                                </div>
                            </div>
                            <div class="gmail-email {{ $options['mailertype'] == 2 ? 'd-block' : 'd-none' }}">
                                <div class="row">
                                    <div class="form-group col-md-4 col-sm-6">
                                        <label for="host_gmail">Host:</label>
                                        <input type="text" class="form-control text-sm" name="data[options][host_gmail]"
                                            id="host_gmail" placeholder="Host" value="{{ $options['host_gmail'] }}">
                                    </div>
                                    <div class="form-group col-md-4 col-sm-6">
                                        <label for="port_gmail">Port:</label>
                                        <input type="text" class="form-control text-sm" name="data[options][port_gmail]"
                                            id="port_gmail" placeholder="Port" value="{{ $options['port_gmail'] }}">
                                    </div>
                                    <div class="form-group col-md-4 col-sm-6">
                                        <label for="secure_gmail">Secure:</label>
                                        <select class="form-control custom-select text-sm"
                                            name="data[options][secure_gmail]" id="secure_gmail">
                                            <option {{ $options['secure_gmail'] == 'tls' ? 'selected' : '' }}
                                                value="tls">
                                                TLS</option>
                                            <option {{ $options['secure_gmail'] == 'ssl' ? 'selected' : '' }}
                                                value="ssl">
                                                SSL</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-4 col-sm-6">
                                        <label for="email_gmail">Email:</label>
                                        <input type="text" class="form-control text-sm" name="data[options][email_gmail]"
                                            id="email_gmail" placeholder="Email" value="{{ $options['email_gmail'] }}">
                                    </div>
                                    <div class="form-group col-md-4 col-sm-6">
                                        <label for="password_gmail">Password:</label>
                                        <input type="text" class="form-control text-sm"
                                            name="data[options][password_gmail]" id="password_gmail"
                                            placeholder="Password" value="{{ $options['password_gmail'] }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
                <div class="card mb-4">
                    <div class="card-header">
                        <h3 class="card-title">Cập nhật {{ $configMan->title_main }}
                        </h3>
                    </div>
                    <div class="card-body card-article">
                        <div class="card">
                            <ul class="nav nav-tabs" id="custom-tabs-three-tab-lang" role="tablist">
                                @foreach (config('app.langs') as $k => $v)
                                    <li class="nav-item">
                                        <a class="nav-link {{ $k == 'vi' ? 'active' : '' }}" id="tabs-lang"
                                           data-bs-toggle="tab" data-bs-target="#tabs-lang-{{ $k }}"
                                           role="tab" aria-controls="tabs-lang-{{ $k }}"
                                           aria-selected="true">{{ $v }}</a>
                                    </li>
                                @endforeach
                            </ul>
                            <div class="tab-content" id="custom-tabs-three-tabContent-lang">
                                @foreach (config('app.langs') as $k => $v)
                                    <div class="tab-pane fade show {{ $k == 'vi' ? 'active' : '' }}"
                                         id="tabs-lang-{{ $k }}" role="tabpanel" aria-labelledby="tabs-lang">
                                        <div class="form-group last:!mb-0">
                                            <label class="form-label" for="name{{ $k }}">Tiêu đề
                                                ({{ $k }})
                                                :</label>
                                            <input type="text" class="form-control for-seo text-sm"
                                                   name="data[name{{ $k }}]" id="name{{ $k }}"
                                                   placeholder="Tiêu đề ({{ $k }})"
                                                   value="{{ !empty(Flash::has('name' . $k)) ? Flash::get('name' . $k) : $item['name' . $k] }}"
                                                   required>
                                        </div>

                                        <div class="form-group last:!mb-0">
                                            <label class="form-label" for="address{{ $k }}">địa chỉ
                                                ({{ $k }}) :</label>
                                            <input type="text" class="form-control text-sm"
                                                   name="data[address{{ $k }}]" id="address{{ $k }}"
                                                   placeholder="địa chỉ ({{ $k }})"
                                                   value="{{ !empty(Flash::has('address' . $k)) ? Flash::get('address' . $k) : $item['address' . $k] }}"
                                                   required>
                                        </div>
                                        @if (config('app.environment') == 'dev' && count(config('app.langs')) > 1)
                                            @if (file_exists('src/Lang/' . $k . '/web.json'))
                                                @php
                                                    $filename = 'src/Lang/' . $k . '/web.json';
                                                    $fp = fopen($filename, 'r');
                                                    $contents = fread($fp, filesize($filename));
                                                @endphp
                                            @endif
                                            <div class="form-group">
                                                <label for="lang.{{ $k }}">Ngôn ngữ
                                                    ({{ $k }}):</label>
                                                <textarea class="form-control text-sm code-javascript" name="datafile[{{ $k }}]"
                                                          id="lang{{ $k }}" rows="5" placeholder="Head JS">{{ $contents }}</textarea>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        @if (count(config('app.langs')) > 1)
                            <div class="form-group">
                                <label>Ngôn ngữ mặc định:</label>
                                <div class="form-group">
                                    @foreach (config('app.langs') as $k => $v)
                                        <div class="custom-control custom-radio d-inline-block mr-3 text-md">
                                            <input class="custom-control-input" type="radio"
                                                id="lang_default-{{ $k }}" name="data[options][lang_default]"
                                                {{ ($k == 'vi' ? 'checked' : $k == $options['lang_default']) ? 'checked' : '' }}
                                                value="{{ $k }}">
                                            <label for="lang_default-{{ $k }}"
                                                class="custom-control-label font-weight-normal">{{ $v }}</label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                        <div class="row mt-3">
                            @php /*<div class="form-group col-md-12 col-sm-12" >
                                <label for="admin_url"><b>Cấu hình link truy cập quản trị <span class="text-danger">*</span>:</b></label>
                                <div class="input-group" >
                                    <span class="input-group-text" id="basic-addon14">{{ request()->root() }}/</span>
                                    <input type="text" class="form-control text-sm" x-model="$store.UrlAdmin.urladmin" @input="$store.UrlAdmin.sanitizeInput" placeholder="URL" name="data[options][admin_url]" id="admin_url" value="{{ !empty(Flash::has('admin_url')) ? Flash::get('admin_url') : (@$options['admin_url']??'admin') }}"
                                    required autocomplete="off">
                                </div>
                                <p class="mb-1 text-sm mt-1 text-danger"><b>Lưu ý:</b> Không nhập dấu Tiếng Việt, ký tự đặc biệt và khoảng trắng !</p>
                                <ul class="checker-url p-0 m-0">
                                    <li class="mb-1" :class="{'text-success': $store.UrlAdmin.isValidLength}">* Link truy cập phải nhiều hơn 4 ký tự</li>
                                    <li class="mb-0" :class="{'text-success': $store.UrlAdmin.checkForLetter}">* Link truy cập phải bao gồm 1 chữ cái</li>
                                </ul>
                            </div> */ @endphp
                            @if (!empty($configMan->email))
                                <div class="form-group col-md-4 col-sm-6">
                                    <label for="email">Email:</label>
                                    <input type="email" class="form-control text-sm" name="data[options][email]"
                                        id="email" placeholder="Email"
                                        value="{{ !empty(Flash::has('email')) ? Flash::get('email') : @$options['email'] }}"
                                        required>
                                </div>
                            @endif
                            @if (!empty($configMan->hotline))
                                <div class="form-group col-md-4 col-sm-6">
                                    <label for="hotline">Hotline:</label>
                                    <input type="text" class="form-control text-sm" name="data[options][hotline]"
                                        id="hotline" placeholder="Hỗ trợ 1"
                                        value="{{ !empty(Flash::has('hotline')) ? Flash::get('hotline') : @$options['hotline'] }}"
                                        required>
                                </div>
                            @endif

                            @if (!empty($configMan->phone))
                                <div class="form-group col-md-4 col-sm-6">
                                    <label for="phone">điện thoại:</label>
                                    <input type="text" class="form-control text-sm" name="data[options][phone]"
                                        id="phone" placeholder="Hotline"
                                        value="{{ !empty(Flash::has('phone')) ? Flash::get('phone') : @$options['phone'] }}"
                                        required>
                                </div>
                            @endif

                            @if (!empty($configMan->zalo))
                                <div class="form-group col-md-4 col-sm-6">
                                    <label for="zalo">Zalo:</label>
                                    <input type="text" class="form-control text-sm" name="data[options][zalo]"
                                        id="zalo" placeholder="Zalo h? tr? 1"
                                        value="{{ !empty(Flash::has('zalo')) ? Flash::get('zalo') : @$options['zalo'] }}">
                                </div>
                            @endif


                            @if (!empty($configMan->oaidzalo))
                                <div class="form-group col-md-4 col-sm-6">
                                    <label for="oaidzalo">OAID Zalo:</label>
                                    <input type="text" class="form-control text-sm" name="data[options][oaidzalo]"
                                        id="oaidzalo" placeholder="OAID Zalo"
                                        value="{{ !empty(Flash::has('oaidzalo')) ? Flash::get('oaidzalo') : @$options['oaidzalo'] }}">
                                </div>
                            @endif

                            @if (!empty($configMan->website))
                                <div class="form-group col-md-4 col-sm-6">
                                    <label for="website">Website:</label>
                                    <input type="text" class="form-control text-sm" name="data[options][website]"
                                        id="website" placeholder="Website"
                                        value="{{ !empty(Flash::has('website')) ? Flash::get('website') : @$options['website'] }}"
                                        required>
                                </div>
                            @endif

                            @if (!empty($configMan->fanpage))
                                <div class="form-group col-md-4 col-sm-6">
                                    <label for="fanpage">Fanpage:</label>
                                    <input type="text" class="form-control text-sm" name="data[options][fanpage]"
                                        id="fanpage" placeholder="Fanpage"
                                        value="{{ !empty(Flash::has('fanpage')) ? Flash::get('fanpage') : @$options['fanpage'] }}">
                                </div>
                            @endif

                            @if (!empty($configMan->coords))
                                <div class="form-group col-md-4 col-sm-6">
                                    <label for="coords">Tọa độ google map:</label>
                                    <input type="text" class="form-control text-sm" name="data[options][coords]"
                                        id="coords" placeholder="Tọa độ google map"
                                        value="{{ !empty(Flash::has('coords')) ? Flash::get('coords') : @$options['coords'] }}">
                                </div>
                            @endif

                            @if (!empty($configMan->link_googlemaps))
                                <div class="form-group col-md-4 col-sm-6">
                                    <label for="link_googlemaps">Link google maps:</label>
                                    <input type="text" class="form-control text-sm"
                                        name="data[options][link_googlemaps]" id="link_googlemaps"
                                        placeholder="link google maps"
                                        value="{{ !empty(Flash::has('link_googlemaps')) ? Flash::get('link_googlemaps') : @$options['link_googlemaps'] }}">
                                </div>
                            @endif

                            @if (!empty($configMan->worktime))
                                <div class="form-group col-md-4 col-sm-6">
                                    <label for="worktime">Giờ làm việc:</label>
                                    <input type="text" class="form-control text-sm" name="data[options][worktime]"
                                        id="worktime" placeholder="Giờ làm việc"
                                        value="{{ !empty(Flash::has('worktime')) ? Flash::get('worktime') : @$options['worktime'] }}">
                                </div>
                            @endif

                            @if (!empty($configMan->ship_area))
                                <div class="col-12 mt-2 mb-2">
                                    <h5 class="mb-2 text-primary">Cấu hình phí vận chuyển theo khu vực</h5>
                                    <p class="mb-0 text-muted text-sm">Thiết lập phí ship cho nội thành, ngoại thành và tỉnh xa.</p>
                                </div>
                                @php $shipDefaultArea = (string) (@$options['ship_default_area'] ?? 'tinh-xa'); @endphp
                                <div class="form-group col-md-4 col-sm-6">
                                    <label for="ship_default_area">Khu vực mặc định:</label>
                                    <select class="form-control text-sm" id="ship_default_area" name="data[options][ship_default_area]">
                                        <option value="noi-thanh" {{ $shipDefaultArea === 'noi-thanh' ? 'selected' : '' }}>Nội thành</option>
                                        <option value="ngoai-thanh" {{ $shipDefaultArea === 'ngoai-thanh' ? 'selected' : '' }}>Ngoại thành</option>
                                        <option value="tinh-xa" {{ $shipDefaultArea === 'tinh-xa' ? 'selected' : '' }}>Tỉnh xa</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-4 col-sm-6">
                                    <label for="ship_fee_noi_thanh">Phí ship nội thành (đ):</label>
                                    <input type="number" min="0" step="1000" class="form-control text-sm"
                                        name="data[options][ship_fee_noi_thanh]" id="ship_fee_noi_thanh"
                                        placeholder="Ví dụ: 15000" value="{{ (int) (@$options['ship_fee_noi_thanh'] ?? 0) }}">
                                </div>
                                <div class="form-group col-md-4 col-sm-6">
                                    <label for="ship_fee_ngoai_thanh">Phí ship ngoại thành (đ):</label>
                                    <input type="number" min="0" step="1000" class="form-control text-sm"
                                        name="data[options][ship_fee_ngoai_thanh]" id="ship_fee_ngoai_thanh"
                                        placeholder="Ví dụ: 30000" value="{{ (int) (@$options['ship_fee_ngoai_thanh'] ?? 0) }}">
                                </div>
                                <div class="form-group col-md-4 col-sm-6">
                                    <label for="ship_fee_tinh_xa">Phí ship tỉnh xa (đ):</label>
                                    <input type="number" min="0" step="1000" class="form-control text-sm"
                                        name="data[options][ship_fee_tinh_xa]" id="ship_fee_tinh_xa"
                                        placeholder="Ví dụ: 45000" value="{{ (int) (@$options['ship_fee_tinh_xa'] ?? 0) }}">
                                </div>
                            @endif

                            <div class="col-12 mt-2 mb-2">
                                <h5 class="mb-2 text-primary">Mục tiêu dashboard đơn hàng</h5>
                                <p class="mb-0 text-muted text-sm">Thiết lập mục tiêu doanh thu và số đơn theo tháng cho trang thống kê đơn hàng.</p>
                            </div>
                            <div class="form-group col-md-4 col-sm-6">
                                <label for="order_dashboard_target_month">Tháng áp dụng:</label>
                                <input type="month" class="form-control text-sm"
                                    name="data[options][order_dashboard_target_month]" id="order_dashboard_target_month"
                                    value="{{ @$options['order_dashboard_target_month'] ?: date('Y-m') }}">
                            </div>
                            <div class="form-group col-md-4 col-sm-6">
                                <label for="order_dashboard_target_revenue">Mục tiêu doanh thu tháng (đ):</label>
                                <input type="number" min="0" step="1000" class="form-control text-sm"
                                    name="data[options][order_dashboard_target_revenue]" id="order_dashboard_target_revenue"
                                    placeholder="Ví dụ: 200000000" value="{{ (int) (@$options['order_dashboard_target_revenue'] ?? 0) }}">
                            </div>
                            <div class="form-group col-md-4 col-sm-6">
                                <label for="order_dashboard_target_orders">Mục tiêu số đơn tháng:</label>
                                <input type="number" min="0" step="1" class="form-control text-sm"
                                    name="data[options][order_dashboard_target_orders]" id="order_dashboard_target_orders"
                                    placeholder="Ví dụ: 500" value="{{ (int) (@$options['order_dashboard_target_orders'] ?? 0) }}">
                            </div>

                            @if (!empty($configMan->coords_iframe))
                                <div class="form-group">
                                    <label for="coords_iframe">
                                        <span>Iframe google:</span>
                                        <a class="text-sm font-weight-normal ml-1" href="https://www.google.com/maps"
                                            target="_blank" title="Lấy mã nhúng">(Lấy mã nhúng)</a>
                                    </label>
                                    <textarea class="form-control text-sm code-javascript" name="data[options][coords_iframe]" id="coords_iframe"
                                        rows="5" placeholder="Iframe google">{{ Func::decodeHtmlChars(Flash::get('coords_iframe')) ?: Func::decodeHtmlChars(@$options['coords_iframe']) }}</textarea>
                                </div>
                            @endif
                            <div class="form-group">
                                <label for="analytics">Google analytics:</label>
                                <textarea class="form-control text-sm code-javascript" name="data[analytics]" id="analytics" rows="5"
                                    placeholder="Google analytics">{{ Func::decodeHtmlChars(Flash::get('analytics')) ?: Func::decodeHtmlChars(@$item['analytics']) }}</textarea>
                            </div>
                            <div class="form-group">
                                <label for="mastertool">Google Webmaster Tool:</label>
                                <textarea class="form-control text-sm code-javascript" name="data[mastertool]" id="mastertool" rows="5"
                                    placeholder="Google Webmaster Tool">{{ Func::decodeHtmlChars(Flash::get('mastertool')) ?: Func::decodeHtmlChars(@$item['mastertool']) }}</textarea>
                            </div>
                            <div class="form-group">
                                <label for="headjs">Head JS:</label>
                                <textarea class="form-control text-sm code-javascript" name="data[headjs]" id="headjs" rows="5"
                                    placeholder="Head JS">{{ Func::decodeHtmlChars(Flash::get('headjs')) ?: Func::decodeHtmlChars(@$item['headjs']) }}</textarea>
                            </div>
                            <div class="form-group">
                                <label for="bodyjs">Body JS: </label>
                                <textarea class="form-control text-sm code-javascript" name="data[bodyjs]" id="bodyjs" rows="5"
                                    placeholder="Body JS">{{ Func::decodeHtmlChars(Flash::get('bodyjs')) ?: Func::decodeHtmlChars(@$item['bodyjs']) }}</textarea>
                            </div>
                            <div class="form-group">
                                <label for="firewall">Config Firewall: <span class="text-danger">*Lưu ý: Chỉ gán các giá trị nếu cần thiết</span></label>
                                <textarea class="form-control text-sm code-javascript" name="firewall" id="firewall" rows="5"
                                    placeholder="Config Firewall">{{ $config_firewall??'' }}</textarea>
                                <div class="mt-1">
                                    <span class="text-danger"><strong>*Lưu ý:</strong></span>
                                    <p class="mb-1 last:mb-0"><b>firewall:</b> Bật tắt firewall</p>
                                    <p class="mb-1 last:mb-0"><b>ip_allow:</b> Danh sách IP bỏ qua //VD: '192.168.1.1,192.168.1.2,...'</p>
                                    <p class="mb-1 last:mb-0"><b>ip_deny:</b> Danh sách IP cấm truy cập //VD: '192.168.1.1,192.168.1.2,...</p>
                                    <p class="mb-1 last:mb-0"><b>max_lockcount:</b> Số lần tối đa phát hiện dấu hiệu DDOS và khóa IP do vĩnh viễn</p>
                                    <p class="mb-1 last:mb-0"><b>max_connect:</b> Số kết nối tối đa được giới hạn bởi time_limit</p>
                                    <p class="mb-1 last:mb-0"><b>time_limit:</b> Thời gian được thực hiện tại max_connect kết nối</p>
                                    <p class="mb-1 last:mb-0"><b>time_wait:</b> Thời gian chờ để được mở khóa IP bị khóa tạm thời</p>
                                    <p class="mb-1 last:mb-0"><b>email_admin:</b> Email liên lạc với admin</p>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
                <input type="hidden" name="id"
                    value="{{ !empty($item['id']) && $item['id'] > 0 ? $item['id'] : '' }}">
                <input name="csrf_token" type="hidden" value="{{ csrf_token() }}">
                <div class="btn-header d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-3 bg-navbar-theme navbar-detached">
                    <div class="d-flex align-content-center flex-wrap gap-2" >
                        <div class="d-flex gap-2">
                            <button type="submit" :class="(!$store.UrlAdmin.checkForLetter || !$store.UrlAdmin.isValidLength)?'disabled':''" class="btn btn-primary submit-check waves-effect"><i class="ti ti-device-floppy mr-2"></i> Luu</button>
                            <button type="reset" class="btn btn-secondary waves-effect"><i class="ti ti-repeat mr-2"></i> Làm lại</button>
                            <a class="btn btn-danger color-white waves-effect" href="{{ url('admin',['com'=>$com,'act'=>'man','type'=>$type],[]) }}" title="Thoát"><i class="ti ti-logout mr-2"></i>Thoát</a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('styles')
<link rel="stylesheet" href="@asset('assets/admin/codemirror/lib/codemirror.css')" />
<link rel="stylesheet" href="@asset('assets/admin/codemirror/addon/hint/show-hint.css')" />
<link rel="stylesheet" href="@asset('assets/admin/codemirror/addon/dialog/dialog.css')" />
<link rel="stylesheet" href="@asset('assets/admin/codemirror/addon/display/fullscreen.css')" />
<link rel="stylesheet" href="@asset('assets/admin/codemirror/addon/search/matchesonscrollbar.css')" />
<link rel="stylesheet" href="@asset('assets/admin/codemirror/theme/duotone-light.css')" />
<link rel="stylesheet" href="@asset('assets/admin/codemirror/theme/bespin.css')" />
@endpush
@push('scripts')
<script src="@asset('assets/admin/codemirror/lib/codemirror.js')" type="text/javascript"></script>
<script src="@asset('assets/admin/codemirror/mode/javascript/javascript.js')" type="text/javascript"></script>
<script src="@asset('assets/admin/codemirror/mode/clike/clike.js')" type="text/javascript"></script>
<script src="@asset('assets/admin/codemirror/addon/hint/show-hint.js')" type="text/javascript"></script>
<script src="@asset('assets/admin/codemirror/addon/hint/javascript-hint.js')" type="text/javascript"></script>
<script src="@asset('assets/admin/codemirror/addon/scroll/annotatescrollbar.js')" type="text/javascript"></script>
<script src="@asset('assets/admin/codemirror/addon/search/matchesonscrollbar.js')" type="text/javascript"></script>
<script src="@asset('assets/admin/codemirror/addon/edit/closebrackets.js')" type="text/javascript"></script>
<script src="@asset('assets/admin/codemirror/addon/edit/matchbrackets.js')" type="text/javascript"></script>
<script type="text/javascript">
var editor = {};
let codejavascript = document.getElementsByClassName('code-javascript');
let codephp = document.getElementsByClassName('code-php');
for (var index = 0; index < codejavascript.length; index++) {
    code(codejavascript[index], 'javascript');
}
for (var index = 0; index < codephp.length; index++) {
    code(codephp[index], 'php');
}

function code($element, $lang = 'javascript') {
    let isDarkStyle = window.Helpers.isDarkStyle();
    editor[$element.name] = CodeMirror.fromTextArea($element, {
        mode: $lang,
        theme: !isDarkStyle ? 'duotone-light' : 'bespin',
        tabSize: 2,
        lineNumbers: true,
        lineWrapping: true,
        styleActiveLine: true,
        styleSelectedText: true,
        matchBrackets: true,
        autoCloseBrackets: true
    });
}
document.addEventListener('alpine:init', () => {
    Alpine.store('UrlAdmin', {
        urladmin:'<?=!empty(Flash::has('admin_url')) ? Flash::get('admin_url') : (@$options['admin_url']??'admin')?>',
        get isValidLength() {
            return this.urladmin.length >= 4;
        },
        get checkForLetter() {
            return /[a-zA-Z]/.test(this.urladmin);
        },
        sanitizeInput() {
            this.urladmin = this.urladmin
                .normalize('NFD')
                .replace(/[\u0300-\u036f]/g, '')
                .replace(/[^a-zA-Z0-9]/g, '')
                .replace(/\s+/g, '')
                .toLowerCase();
        },
    });
});
</script>
@endpush
