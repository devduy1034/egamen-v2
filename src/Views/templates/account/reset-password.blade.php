@extends('layout')
@section('content')
    <section class="account-page py-4">
        <div class="max-width">
            <div class="account-box">
                <p class="account-eyebrow">Reset Password</p>
                <h1 class="account-title">{{ $titleMain ?? 'Đặt lại mật khẩu' }}</h1>
                <p class="account-subtitle">Nhập mật khẩu mới cho tài khoản của bạn.</p>
                @if (!empty($error))
                    <div class="alert alert-danger">{!! $error !!}</div>
                @endif
                <form method="post" action="{{ url('user.reset.submit') }}" class="account-form">
                    <div class="mb-3">
                        <label class="form-label" for="password-reset">Mật khẩu mới</label>
                        <div class="account-password-wrap">
                            <input type="password" id="password-reset" name="password" class="form-control"
                                placeholder="Tối thiểu 6 ký tự" required>
                            <button class="account-password-toggle js-toggle-password" type="button"
                                data-target="#password-reset" aria-label="Hiện mật khẩu">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="password-confirm-reset">Xác nhận mật khẩu mới</label>
                        <div class="account-password-wrap">
                            <input type="password" id="password-confirm-reset" name="password_confirm" class="form-control"
                                placeholder="Nhập lại mật khẩu mới" required>
                            <button class="account-password-toggle js-toggle-password" type="button"
                                data-target="#password-confirm-reset" aria-label="Hiện mật khẩu">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>
                    <input type="hidden" name="token" value="{{ $token ?? '' }}">
                    <input type="hidden" name="csrf_token" value="{{ csrf_token() }}">
                    <button type="submit" class="btn account-btn w-100">Cập nhật mật khẩu</button>
                </form>
            </div>
        </div>
    </section>
@endsection
