@extends('login')
@section('content')
    <div class="authentication-wrapper authentication-basic container-p-y">
        <div class="authentication-inner py-4">
            <!-- Login -->
            <a class="nav-link nav-link-website" target="_blank"
                href="{{ request()->getSchemeAndHttpHost() . config('app.site_path') }}">
                <i class="ti ti-world"></i> Xem website
            </a>
            <div class="card card-login">
                <div class="card-body">
                    <!-- Logo -->
                    <div class="app-brand ps-0 justify-content-center mb-1">
                        <a href="{{ url('index') }}" class="app-brand-link gap-2">
                            <span class="app-brand-logo demo">
                                <img src="@asset('assets/admin/img/avatars/logo1bce.png')" alt="EGA Men Style" />
                            </span>
                        </a>
                    </div>
                    <!-- /Logo -->
                    <h4 class="mb-1 pt-2 text-center blue bold font-20">HỆ THỐNG QUẢN TRỊ</h4>
                    <p class="mb-4 text-center font-15">Vui lòng đăng nhập vào tài khoản của bạn !</p>

                    <form id="formAuthentication"
                        action="{{ url('loginAdmin', null, ['redirect' => request()->query('redirect')]) }}" method="POST">
                        <div class="mb-3 user-login">
                            <label for="email" class="form-label font-15">Tài khoản</label>
                            <input type="text" class="form-control" id="username" name="username"
                                placeholder="Tài khoản" required autofocus />
                            <i class="ti ti-mail"></i>
                        </div>
                        <div class="mb-3 form-password-toggle">
                            <div class="d-flex justify-content-between">
                                <label class="form-label font-15" for="password">Mật khẩu</label>
                            </div>
                            <div class="input-group input-group-merge" x-data="{ open: false }">
                                <input x-bind:type="(!open) ? 'password' : 'text'" id="password" class="form-control"
                                    required name="password"
                                    placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                                    aria-describedby="password" />

                                <span @click="open=!open" class="input-group-text cursor-pointer"><i class="ti"
                                        :class="(!open) ? 'ti-eye-off' : 'ti-eye'"></i></span>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="form-check form-check-primary">
                                <input class="form-check-input" type="checkbox" name="remember" id="remember-me">
                                <label class="form-check-label font-15" for="remember-me"> Ghi nhớ đăng nhập </label>
                            </div>
                        </div>
                        <div class="mb-3">
                            <input name="csrf_token" type="hidden" value="{{ csrf_token() }}">
                            <button type="submit" class="btn btn-primary w-100">Đăng nhập</button>
                            @if (!empty($mess))
                                <p class="messlogin mb-0 mt-1">{{ $mess }}</p>
                            @endif
                        </div>
                    </form>
                </div>
            </div>

            <!-- /Register -->
        </div>

        <div class="copy-right mt-5 font-15">ALL rights reserved</div>
    </div>
@endsection
@pushonce('styles')
    <link rel="stylesheet" href="@asset('assets/admin/vendor/css/pages/page-auth.css')" />
@endpushonce
