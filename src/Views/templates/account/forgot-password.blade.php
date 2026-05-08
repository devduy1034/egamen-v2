@extends('layout')
@section('content')
    <section class="account-page py-4">
        <div class="max-width">
            <div class="account-box">
                <p class="account-eyebrow">Account Recovery</p>
                <h1 class="account-title">{{ $titleMain ?? 'Quên mật khẩu' }}</h1>
                <p class="account-subtitle">
                    Nhập email đã đăng ký. Hệ thống sẽ gửi link đặt lại mật khẩu cho bạn.
                </p>
                @if (!empty($error))
                    <div class="alert alert-danger">{!! $error !!}</div>
                @endif
                @if (!empty($status))
                    <div class="alert alert-success">{{ $status }}</div>
                @endif
                <form method="post" action="{{ url('user.forgot.submit') }}" class="account-form">
                    <div class="mb-3">
                        <label class="form-label" for="email-forgot">Email</label>
                        <input type="email" id="email-forgot" name="email" class="form-control"
                            placeholder="Nhập email đăng ký" required>
                    </div>
                    <input type="hidden" name="csrf_token" value="{{ csrf_token() }}">
                    <button type="submit" class="btn account-btn w-100">Gửi link đặt lại mật khẩu</button>
                </form>
                <p class="account-link">Nhớ mật khẩu rồi? <a href="{{ url('user.login') }}">Đăng nhập</a></p>
            </div>
        </div>
    </section>
@endsection
