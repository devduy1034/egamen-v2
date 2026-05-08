<aside class="account-sidebar">
    <div class="account-sidebar__head">
        <div class="account-avatar account-avatar--sidebar">
            @if (!empty($user->avatar))
                <img src="{{ assets_photo('user', '70x70x1', $user->avatar, 'thumbs') }}" alt="{{ $user->fullname ?? 'Avatar' }}">
            @else
                <img src="{{ $noImageUrl }}" alt="No image">
            @endif
        </div>
        <div>
            <p class="account-sidebar__name">{{ $user->fullname ?? 'Member' }}</p>
            <p class="account-sidebar__mail">{{ $user->email ?? '-' }}</p>
        </div>
    </div>

    <nav class="account-sidebar__menu">
        <a class="account-nav-btn {{ $activeSection === 'profile' ? 'is-active' : '' }}" href="{{ url('user.account', null, ['section' => 'profile']) }}">Thông tin</a>
        <a class="account-nav-btn {{ $activeSection === 'orders' ? 'is-active' : '' }}" href="{{ url('user.account', null, ['section' => 'orders']) }}">Đơn hàng ({{ (int) ($activeOrdersCount ?? 0) }})</a>
        <a class="account-nav-btn {{ $activeSection === 'address' ? 'is-active' : '' }}" href="{{ url('user.account', null, ['section' => 'address']) }}">Địa chỉ</a>
        <a class="account-nav-btn {{ $activeSection === 'wishlist' ? 'is-active' : '' }}" href="{{ url('user.account', null, ['section' => 'wishlist']) }}">Yêu thích</a>
        <a class="account-nav-btn {{ $activeSection === 'security' ? 'is-active' : '' }}" href="{{ url('user.account', null, ['section' => 'security']) }}">Bảo mật</a>
    </nav>
    <a href="{{ url('user.logout') }}" class="btn account-btn account-btn--outline account-btn--logout w-100 mt-3">Đăng xuất</a>
</aside>
