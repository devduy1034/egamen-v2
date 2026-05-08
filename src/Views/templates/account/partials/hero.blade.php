<div class="account-hero">
    <div>
        <p class="account-hero__eyebrow">My Account</p>
        <h1>Xin chào, {{ $user->fullname ?? 'Member' }}</h1>
        <p>Quản lý hồ sơ, đơn hàng và bảo mật tài khoản tại một nơi.</p>
    </div>
    <div class="account-hero__chips">
        <span class="account-chip"><strong>{{ $ordersCount }}</strong> đơn hàng</span>
        <span class="account-chip"><strong>{{ $addressesCount }}</strong> địa chỉ</span>
        <span class="account-chip"><strong>{{ $wishlistCount ?? 0 }}</strong> yêu thích</span>
        <span class="account-chip"><strong>{{ !empty($googleLinked) ? 'Đã liên kết' : 'Chưa liên kết' }}</strong> Google</span>
    </div>
</div>
