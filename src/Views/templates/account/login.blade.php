@extends('layout')
@section('content')
    <section class="account-page py-4">
        <div class="max-width">
            <div class="account-box">
                <p class="account-eyebrow">Welcome Back</p>
                <h1 class="account-title">{{ $titleMain ?? 'Đăng nhập' }}</h1>
                <p class="account-subtitle">Đăng nhập để theo dõi đơn hàng và quản lý thông tin tài khoản của bạn.</p>
                @if (!empty($error))
                    <div class="alert alert-danger">{!! $error !!}</div>
                @endif
                <form method="post" action="{{ url('user.login.submit') }}" class="account-form">
                    <div class="mb-3">
                        <label class="form-label" for="identity-login">Số điện thoại hoặc email</label>
                        <input type="text" id="identity-login" name="identity" class="form-control"
                            placeholder="Nhập số điện thoại hoặc email" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="password-login">Mật khẩu</label>
                        <div class="account-password-wrap">
                            <input type="password" id="password-login" name="password" class="form-control"
                                placeholder="Nhập mật khẩu" required>
                            <button class="account-password-toggle js-toggle-password" type="button"
                                data-target="#password-login" aria-label="Hiện mật khẩu">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                        <div class="account-forgot">
                            <a href="{{ url('user.forgot') }}">Quên mật khẩu?</a>
                        </div>
                    </div>
                    <input type="hidden" name="redirect" value="{{ $redirect ?? '' }}">
                    <input type="hidden" name="csrf_token" value="{{ csrf_token() }}">
                    <button type="submit" class="btn account-btn w-100">Đăng nhập</button>
                </form>
                <div class="account-social">
                    <span class="account-social__line">Hoặc</span>
                    <a class="btn account-btn-google w-100" href="{{ url('user.login.google') }}">
                        <svg class="google-logo" viewBox="0 0 18 18" aria-hidden="true" focusable="false">
                            <path fill="#EA4335" d="M17.64 9.2c0-.64-.06-1.25-.16-1.84H9v3.48h4.84a4.14 4.14 0 0 1-1.8 2.72v2.26h2.92c1.7-1.56 2.68-3.86 2.68-6.62z"/>
                            <path fill="#34A853" d="M9 18c2.43 0 4.47-.8 5.96-2.18l-2.92-2.26c-.8.54-1.84.86-3.04.86-2.34 0-4.32-1.58-5.03-3.7H.96v2.34A9 9 0 0 0 9 18z"/>
                            <path fill="#FBBC05" d="M3.97 10.72A5.4 5.4 0 0 1 3.7 9c0-.6.1-1.18.27-1.72V4.94H.96A9 9 0 0 0 0 9c0 1.45.35 2.82.96 4.06l3.01-2.34z"/>
                            <path fill="#4285F4" d="M9 3.58c1.32 0 2.5.46 3.43 1.36l2.57-2.57C13.47.95 11.43 0 9 0A9 9 0 0 0 .96 4.94l3.01 2.34C4.68 5.16 6.66 3.58 9 3.58z"/>
                        </svg>
                        Đăng nhập với Google
                    </a>
                </div>
                <p class="account-link">Chưa có tài khoản? <a href="{{ url('user.register') }}">Đăng ký ngay</a></p>
            </div>
        </div>
    </section>
@endsection
