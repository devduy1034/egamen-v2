@php
    $mobileProductMenu = $listProductMenu ?? $productListMenu ?? [];
    $logoThumb = config('type.photo.logo.thumb') ?? '134x46x1';
    $logoSrc = !empty($logoPhoto['photo'])
        ? assets_photo('photo', $logoThumb, $logoPhoto['photo'], 'thumbs')
        : assets('assets/admin/img/avatars/logo1bce.png');
@endphp
<div class="offcanvas offcanvas-start" id="menu-mobile">
    <div class="offcanvas-body">
        <span class="btn-close btn-close-menu" data-bs-dismiss="offcanvas"></span>
        <nav class="menu-mobile">
            <div class="head-menu">
                <a class="logo-header" href="{{ url('home') }}">
                    <img src="{{ $logoSrc }}" alt="{{ $setting['name' . $lang] }}" loading="eager" decoding="async">
                </a>
                <div class="search-menu">
                    <label for="keyword-mobile" class="mb-2">Bạn cần tìm sản phẩm gì</label>
                    <div class="form-floating form-floating-cus">
                        <input type="text" id="keyword-mobile" class="" placeholder="Bạn cần tìm sản phẩm gì"
                            onkeypress="doEnter(event,'keyword-mobile');">
                    </div>
                    <p class="mb-0" onclick="onSearch('keyword-mobile');"><i class="fal fa-search"></i></p>
                </div>
            </div>

            <ul>
                <li><a class="transition" href="{{ url('home') }}" title="Trang chủ"><i class="fa-solid fa-house"></i> Trang chủ</a></li>
                <li><a class="transition" href="{{ url('gioi-thieu') }}" title="Giới thiệu"><i class="fa-solid fa-address-card"></i> Giới thiệu</a></li>

                <li>
                    <a class="transition" href="{{ url('san-pham') }}" title="Sản phẩm"><i class="fa-brands fa-product-hunt"></i> Sản phẩm</a>
                    @if (count($mobileProductMenu))
                        <span data-bs-toggle="collapse" data-bs-target="#menu-product" class="scroll"><i class="ml-auto fa-solid fa-angle-right"></i></span>
                        <ul class="collapse" id="menu-product">
                            @foreach ($mobileProductMenu as $vlist)
                                <li>
                                    <a class="" href="{{ $vlist[$sluglang] }}" title="{!! $vlist['name' . $lang] !!}">{{ $vlist['name' . $lang] }}</a>
                                    @if (!empty($vlist->getCategoryCats) && $vlist->getCategoryCats->isNotEmpty())
                                        <span data-bs-toggle="collapse" data-bs-target="#product-list-{{ $vlist['id'] }}" class="scroll"><i class="ml-auto fa-solid fa-angle-right"></i></span>
                                        <ul class="collapse" id="product-list-{{ $vlist['id'] }}">
                                            @foreach ($vlist->getCategoryCats ?? [] as $vcat)
                                                <li>
                                                    <a class="" href="{{ $vcat[$sluglang] }}" title="{!! $vcat['name' . $lang] !!}">{{ $vcat['name' . $lang] }}</a>
                                                    @if (!empty($vcat->getCategoryItems) && $vcat->getCategoryItems->isNotEmpty())
                                                        <span data-bs-toggle="collapse" data-bs-target="#product-cat-{{ $vcat['id'] }}" class="scroll"><i class="ml-auto fa-solid fa-angle-right"></i></span>
                                                        <ul class="collapse" id="product-cat-{{ $vcat['id'] }}">
                                                            @foreach ($vcat->getCategoryItems ?? [] as $vitem)
                                                                <li>
                                                                    <a class="item" href="{{ $vitem[$sluglang] }}" title="{!! $vitem['name' . $lang] !!}">{{ $vitem['name' . $lang] }}</a>
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
                        </ul>
                    @endif
                </li>

                <li><a class="transition" href="{{ url('hang') }}" title="Hãng"><i class="fa-brands fa-bandcamp"></i> Hãng</a></li>
                <li class="group"><a class="transition" href="{{ url('album') }}" title="Album"><i class="fa-solid fa-image"></i> Album</a></li>
                <li class="group"><a class="transition" href="{{ url('video') }}" title="Video"><i class="fa-solid fa-video"></i> Video</a></li>
                <li class="group"><a class="transition" href="{{ url('tin-tuc') }}" title="Tin tức"><i class="fa-solid fa-newspaper"></i> Tin tức</a></li>
                <li class="group"><a class="transition" href="{{ url('lien-he') }}" title="Liên hệ"><i class="fa-solid fa-address-book"></i> Liên hệ</a></li>
            </ul>

            <div class="company">
                <p>Địa chỉ: <span>{{ $setting['address' . $lang] }}</span></p>
                <p>Điện thoại: <span>{{ $optSetting['hotline'] }}</span></p>
                <p>Email: <span>{{ $optSetting['email'] }}</span></p>
                <p>Website: <span>{{ $optSetting['website'] }}</span></p>
            </div>
        </nav>
    </div>
</div>
