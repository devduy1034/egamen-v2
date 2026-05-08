<div class="navigation">
    <div class="container">
        <div class="navigation__main">
            <div class="menu-mobile-btn block lg:hidden">
                <span></span>
                <span></span>
                <span></span>
            </div>
            <div class="wrap-menu">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="left-menu">
                        <a class="logo-menu" href="">
                            @component('component.image', [
                                'class' => '',
                                'w' => config('type.photo.' . $logoPhoto['type'] . '.width'),
                                'h' => config('type.photo.' . $logoPhoto['type'] . '.height'),
                                'z' => config('type.photo.' . $logoPhoto['type'] . '.opt'),
                                'is_watermarks' => false,
                                'destination' => 'photo',
                                'image' => $logoPhoto['photo'] ?? '',
                                'alt' => $setting['name' . $lang] ?? '',
                            ])
                            @endcomponent
                        </a>
                    </div>
                    <div class="center-menu">
                        <div class="menu">
                            <ul class="ulmn">
                                <li class="nav-item">
                                    <a class="transition {{ ($com ?? ' ') == 'trang-chu' ? 'active' : '' }}"
                                        href=""
                                        title="{{ $setting['name' . $lang] . '-' . __('web.home') }}">{{ __('web.home') }}</a>
                                </li>
                                <li class="nav-item">
                                    <a class="transition {{ ($com ?? '') == 'gioi-thieu' ? 'active' : '' }} "
                                        href="{{ url('gioi-thieu') }}"
                                        title="{{ $setting['name' . $lang] . '-' . __('web.gioithieu') }}">{{ __('web.gioithieu') }}
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="transition {{ ($com ?? '') == 'san-pham' ? 'active' : '' }} "
                                        href="{{ url('san-pham') }}"
                                        title="{{ $setting['name' . $lang] . '-' . __('web.sanpham') }}">{{ __('web.sanpham') }}
                                    </a>
                                    @if ($listProductMenu->isNotEmpty())
                                        <span class="btn-dropdown-menu"><i class="fa fa-angle-right"></i></span>
                                        <ul class="sub-menu none level0">
                                            @foreach ($listProductMenu ?? [] as $vlist)
                                                <li>
                                                    <a class="transition "
                                                        href="{{ url('slugweb', ['slug' => $vlist['slug' . $lang]]) }}"
                                                        title="{{ __('web.sanpham') . '-' . $vlist['name' . $lang] }}">{{ $vlist['name' . $lang] }}
                                                    </a>
                                                    @if ($vlist->getCategoryCats->isNotEmpty())
                                                        <span class="btn-dropdown-menu"><i
                                                                class="fa fa-angle-right"></i></span>
                                                        <ul class="sub-menu none level1">
                                                            @foreach ($vlist->getCategoryCats ?? [] as $vcat)
                                                                <li>
                                                                    <a class="transition "
                                                                        href="{{ url('slugweb', ['slug' => $vcat['slug' . $lang]]) }}"
                                                                        title="{{ __('web.sanpham') . '-' . $vcat['name' . $lang] }}">{{ $vcat['name' . $lang] }}
                                                                    </a>
                                                                    @if ($vcat->getCategoryItems->isNotEmpty())
                                                                        <span class="btn-dropdown-menu"><i
                                                                                class="fa fa-angle-right"></i></span>
                                                                        <ul class="sub-menu none level2">
                                                                            @foreach ($vcat->getCategoryItems ?? [] as $vitem)
                                                                                <li>
                                                                                    <a class="transition "
                                                                                        href="{{ url('slugweb', ['slug' => $vitem['slug' . $lang]]) }}"
                                                                                        title="{{ __('web.sanpham') . '-' . $vitem['name' . $lang] }}">{{ $vitem['name' . $lang] }}</a>
                                                                                </li>
                                                                            @endforeach
                                                                        </ul>
                                                                    @endif
                                                                </li>
                                                            @endforeach

                                                        </ul>
                                                    @endif
                                                </li>
                                            @endforeach
                                            <li class="banner_menu">
                                                @component('component.image', [
                                                    'class' => '',
                                                    'w' => config('type.photo.' . $banner_menu['type'] . '.width'),
                                                    'h' => config('type.photo.' . $banner_menu['type'] . '.height'),
                                                    'z' => config('type.photo.' . $banner_menu['type'] . '.opt'),
                                                    'is_watermarks' => false,
                                                    'destination' => 'photo',
                                                    'image' => $banner_menu['photo'] ?? '',
                                                    'alt' => $setting['name' . $lang] ?? '',
                                                ])
                                                @endcomponent
                                            </li>
                                        </ul>
                                    @endif
                                </li>
                                <li class="nav-item">
                                    <a class="transition {{ ($com ?? '') == 'san-pham-moi' ? 'active' : '' }} "
                                        href="{{ url('new-product') }}"
                                        title="{{ $setting['name' . $lang] . '-' . __('web.sanphammoi') }}">{{ __('web.sanphammoi') }}
                                        <span class="menu-new">New</span>
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a class="transition {{ ($com ?? '') == 'tin-tuc' ? 'active' : '' }} "
                                        href="{{ url('tin-tuc') }}"
                                        title="{{ $setting['name' . $lang] . '-' . __('web.tintuc') }}">{{ __('web.tintuc') }}
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="transition {{ ($com ?? '') == 'lien-he' ? 'active' : '' }}"
                                        href="{{ url('lien-he') }}"
                                        title="{{ $setting['name' . $lang] . '-' . __('web.lienhe') }}">{{ __('web.lienhe') }}
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="transition menu-outlet {{ ($com ?? '') == 'tra-cuu-don-hang' ? 'active' : '' }} "
                                        href="{{ url('order.lookup') }}"
                                        title="{{ $setting['name' . $lang] . '-' . 'Tra cứu đơn hàng' }}">{{ 'Tra cứu đơn hàng' }}
                                    </a>
                                </li>
                            </ul>
                            {{-- <div class="search-menu">
                                <p class="icon-search-menu transition"><i class="fa-regular fa-magnifying-glass"></i>
                                </p>
                                <div class="search-grid w-clear">
                                    <input type="text" name="keyword" id="keyword"
                                        placeholder="{{ __('web.timkiem') }}" onkeypress="doEnter(event,'keyword');"
                                        value="{{ !empty($_GET['keyword']) ? $_GET['keyword'] : '' }}" />
                                    <p onclick="onSearch('keyword');"><i class="fa-solid fa-magnifying-glass"></i></p>
                                </div>
                            </div> --}}
                        </div>
                    </div>
                    <div class="right-menu">

                        <div class="d-flex justify-content-between align-items-center">
                            <div class="search">
                                <a class="search-toggle" href="{{ url('tim-kiem') }}"
                                    aria-label="{{ __('web.timkiem') }}" title="Tìm kiếm thông minh">
                                    <i class="bi bi-search"></i>
                                </a>
                            </div>
                            <div class="icon-account">
                                <i class="bi bi-person"></i>
                                <div class="account-action">
                                    @if (session()->has('member'))
                                        @php
                                            $memberName = session()->get('member_name');
                                            if (is_array($memberName)) {
                                                $memberName = $memberName['member_name'] ?? reset($memberName);
                                            }
                                        @endphp
                                        <a class="account-greeting" href="{{ url('user.account') }}">Hello,
                                            {{ (string) ($memberName ?? 'Member') }}</a>
                                        <a href="{{ url('user.logout') }}"
                                            title="{{ __('web.dangxuat') }}">{{ __('web.dangxuat') }}</a>
                                    @else
                                        <a href="{{ url('user.login') }}"
                                            title="{{ __('web.dangnhap') }}">{{ __('web.dangnhap') }}</a>
                                        <a href="{{ url('user.register') }}"
                                            title="{{ __('web.dangky') }}">{{ __('web.dangky') }}</a>
                                    @endif
                                </div>
                            </div>
                            @php
                                $wishlistUrl = session()->has('member')
                                    ? url('user.account', null, ['section' => 'wishlist'])
                                    : url('wishlist.page');
                            @endphp
                            <a href="{{ $wishlistUrl }}" class="wishlist-link" title="Sản phẩm yêu thích">
                                <i class="bi bi-heart"></i>
                                <span class="js-wishlist-count">0</span>
                            </a>
                            <a href="gio-hang" class="cart-head"><i class="bi bi-bag-check"></i><span
                                    class="count-cart">{{ Cart::count() }}</span></a>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
    <div class="navigation-search-panel">
        <div class="container">
            <div class="navigation-search-panel__inner">
                <button type="button" class="search-panel-close" aria-label="Đóng tìm kiếm">
                    <i class="bi bi-x-lg"></i>
                </button>
                <form class="navigation-search-form" action="{{ url('tim-kiem') }}" method="GET" data-quick-search-url="{{ url('tim-kiem-goi-y') }}" data-track-url="{{ url('api.events.track') }}">
                    <input type="text" name="keyword" id="keyword-navigation" class="search-auto"
                        placeholder="{{ __('web.timkiem') }}" 
                        value="{{ !empty($_GET['keyword']) ? $_GET['keyword'] : '' }}" autocomplete="off" />
                    <button type="submit" aria-label="{{ __('web.timkiem') }}">
                        <i class="bi bi-search"></i>
                    </button>
                </form>
                <div class="show-search"></div>
            </div>
        </div>
    </div>
    <div class="opacity-menu"></div>
    <div class="header-left-fixwidth">
        <div class="section wrap-header">
            <a class="logos-menu" href="">
                @component('component.image', [
                    'class' => '',
                    'w' => config('type.photo.' . $logoPhoto['type'] . '.width'),
                    'h' => config('type.photo.' . $logoPhoto['type'] . '.height'),
                    'z' => config('type.photo.' . $logoPhoto['type'] . '.opt'),
                    'is_watermarks' => false,
                    'destination' => 'photo',
                    'image' => $logoPhoto['photo'] ?? '',
                    'alt' => $setting['name' . $lang] ?? '',
                ])
                @endcomponent
            </a>
            <div class="nav-menu">

            </div>
        </div>
    </div>
</div>
