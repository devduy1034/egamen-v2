@extends('layout')
@section('content')
    <section class="order-success-page py-5">
        <div class="container">
            <div class="order-success-card">
                <h1 class="order-success-title">{{ $titleMain ?? 'Đặt hàng thành công!' }}</h1>
                <p class="order-success-subtitle">
                    {{ $subtitle ?? 'Cảm ơn bạn. Đơn hàng của bạn đã được ghi nhận.' }}
                </p>
                <div class="order-success-meta">
                    <p><strong>Mã đơn hàng:</strong> <span>{{ $orderCode ?? '-' }}</span></p>
                    <p><strong>Trạng thái:</strong> <span>{{ $paymentStatusText ?? '-' }}</span></p>
                    <p><strong>Thời gian:</strong> <span>{{ $createdAtText ?? '-' }}</span></p>
                </div>
                <p class="order-success-mail">Thông tin đơn hàng đã được gửi đến email của quý khách.</p>
                <div class="order-success-actions">
                    <a class="btn btn-primary" href="{{ $homeUrl ?? url('home') }}">Trở về trang chủ</a>
                    <a class="btn btn-outline-primary"
                        href="{{ $orderLookupUrl ?? url('user.account', null, ['section' => 'orders']) }}">
                        Kiểm tra đơn hàng
                    </a>
                </div>
            </div>
        </div>
    </section>
@endsection
