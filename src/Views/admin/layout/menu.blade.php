<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
    @php
        $sessionAdmin = $_SESSION['admin'] ?? null;
        $adminId = is_array($sessionAdmin) ? $sessionAdmin['admin'] ?? null : $sessionAdmin;
        $adminUser = \LARAVEL\Models\UserModel::find($adminId);
    @endphp
    <div class="app-brand demo justify-content-center">
        <a href="{{ url('index') }}" class="">
            <span class="app-brand-logo demo">
                <img src="@asset('assets/admin/img/avatars/logo1bce.png')" alt class="h-auto transition" />
            </span>
        </a>
        <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large d-xxl-none close-menu-admin">
            <i class="ti menu-toggle-icon d-none d-xl-block ti-sm align-middle"></i>
            <i class="ti ti-x d-block d-xl-none ti-sm align-middle"></i>
        </a>
    </div>
    <div class="menu-inner-shadow"></div>
    <ul class="menu-inner py-1 scrolling-ul">
        <!-- GROUP -->
        @php $newArrayGroup = []; @endphp
        @if (!empty($configType->group))
            @foreach ($configType->group as $k => $group)
                @if (isset($group->group))
                    @php $newArrayGroup = array_merge($newArrayGroup, $group->group); @endphp
                @endif
                <li class="menu-header small">
                    <a href="javascript:void(0);" class="menu-title">
                        <div class="text-uppercase bold">{{ $group->title_main }}</div>
                    </a>
                    <!-- sanpham -->
                    <ul class="menu-body">
                        @foreach ($configType->product as $key => $value)
                            @if (in_array($key, $group->group))
                                @if (!empty($value->categories))
                                    <li
                                        class="menu-item menu-item-main {{ $type == $key && in_array('product', explode('-', $com)) ? 'open' : '' }}">
                                        <a href="javascript:void(0);" class="menu-link menu-name menu-toggle">
                                            <i class="menu-icon tf-icons ti ti-brand-superhuman"></i>
                                            <div>{{ $value->title_main }}</div>
                                        </a>
                                        <ul class="menu-sub">

                                            @if (!empty($value->categories))
                                                @foreach ($value->categories as $k => $v)
                                                    @if (Func::chekcPermission('product.' . $k . '.' . $key . '.man', $permissions))
                                                        <li
                                                            class="menu-item {{ $type == $key && $com == 'product-' . $k ? 'active' : '' }}">
                                                            <a href="product-{{ $k }}/man/{{ $key }}"
                                                                class="menu-link">
                                                                <div>{{ $v->title_main_categories }}</div>
                                                            </a>
                                                        </li>
                                                    @endif
                                                @endforeach
                                            @endif

                                            @if (Func::chekcPermission('product.brand.' . $key . '.man', $permissions))
                                                @if (!empty($value->brand))
                                                    <li
                                                        class="menu-item {{ $type == $key && $com == 'product-brand' ? 'active' : '' }}">
                                                        <a href="product-brand/man/{{ $key }}"
                                                            class="menu-link">
                                                            <div>{{ $value->brand->title_main_brand }}</div>
                                                        </a>
                                                    </li>
                                                @endif
                                            @endif

                                            @if (Func::chekcPermission('product.' . $key . '.man', $permissions))
                                                <li
                                                    class="menu-item {{ $type == $key && $com == 'product' ? 'active' : '' }}">
                                                    <a href="product/man/{{ $key }}" class="menu-link">
                                                        <div>{{ $value->title_main }}</div>
                                                    </a>
                                                </li>
                                            @endif
                                        </ul>
                                    </li>
                                @else
                                    @if (Func::chekcPermission('product.' . $key . '.man', $permissions))
                                        <li
                                            class="menu-item menu-item-main {{ $type == $key && $com == 'product' ? 'active' : '' }}">
                                            <a href="product/man/{{ $key }}" class="menu-link">
                                                <i class="menu-icon tf-icons ti ti-brand-superhuman"></i>
                                                <div>{{ $value->title_main }}</div>
                                            </a>
                                        </li>
                                    @endif
                                @endif
                            @endif
                        @endforeach
                    </ul>
                    <!-- baibiet -->
                    <ul class="menu-body">
                        @foreach ($configType->news as $key => $value)
                            @if (in_array($key, $group->group))
                                @if (!empty($value->categories))
                                    <li class="menu-item menu-item-main {{ $type == $key ? 'open' : '' }}">
                                        <a href="javascript:void(0);" class="menu-link menu-name menu-toggle">
                                            <i class="menu-icon tf-icons ti ti-brand-superhuman"></i>
                                            <div>{{ $value->title_main }}</div>

                                        </a>
                                        <ul class="menu-sub">
                                            @if (!empty($value->categories))
                                                @foreach ($value->categories as $k => $v)
                                                    @if (Func::chekcPermission('news.' . $k . '.' . $key . '.man', $permissions))
                                                        <li
                                                            class="menu-item {{ $type == $key && $com == 'news-' . $k ? 'active' : '' }}">
                                                            <a href="news-{{ $k }}/man/{{ $key }}"
                                                                class="menu-link">
                                                                <div>{{ $v->title_main_categories }}</div>
                                                            </a>
                                                        </li>
                                                    @endif
                                                @endforeach
                                            @endif
                                            @if (Func::chekcPermission('news.' . $key . '.man', $permissions))
                                                <li
                                                    class="menu-item {{ $type == $key && $com == 'news' ? 'active' : '' }}">
                                                    <a href="news/man/{{ $key }}" class="menu-link">
                                                        <i class="menu-icon tf-icons ti ti-brand-superhuman"></i>
                                                        <div>{{ $value->title_main }}</div>
                                                    </a>
                                                </li>
                                            @endif
                                        </ul>
                                    </li>
                                @else
                                    @if (Func::chekcPermission('news.' . $key . '.man', $permissions))
                                        @if (empty($value->dropdown))
                                            <li
                                                class="menu-item menu-item-main {{ $type == $key && $com == 'news' ? 'active' : '' }}">
                                                <a href="news/man/{{ $key }}" class="menu-link">
                                                    <i class="menu-icon tf-icons ti ti-brand-superhuman"></i>
                                                    <div>{{ $value->title_main }}</div>
                                                </a>
                                            </li>
                                        @endif
                                    @endif
                                @endif
                            @endif
                        @endforeach
                    </ul>
                    <!-- trangtinh -->
                    <ul class="menu-body">
                        @foreach ($configType->static as $key => $value)
                            @if (in_array($key, $group->group))
                                @if (Func::chekcPermission('static.' . $key . '.man', $permissions))
                                    <li
                                        class="menu-item menu-item-main {{ $type == $key && $com == 'static' ? 'active' : '' }}">
                                        <a href="static/man/{{ $key }}" class="menu-link">
                                            <i class="menu-icon tf-icons ti ti-brand-superhuman"></i>
                                            <div>{{ $value->title_main }}</div>
                                        </a>
                                    </li>
                                @endif
                            @endif
                        @endforeach
                    </ul>
                    <!-- photo -->
                    <ul class="menu-body">
                        @if (!empty($configType->photo))
                            @foreach ($configType->photo as $key => $value)
                                @if (in_array($key, $group->group))
                                    @if (Func::chekcPermission('photo.' . $value->kind . '.' . $key . '.man', $permissions))
                                        <li
                                            class="menu-item menu-item-main {{ $type == $key && $com == 'photo-' . $value->kind ? 'active' : '' }}">
                                            <a href="photo-{{ $value->kind }}/man/{{ $key }}"
                                                class="menu-link">
                                                <i class="menu-icon tf-icons ti ti-brand-superhuman"></i>
                                                <div>{{ $value->title_main }}</div>
                                            </a>
                                        </li>
                                    @endif
                                @endif
                            @endforeach
                        @endif
                    </ul>

                    <!-- tags -->
                    <ul class="menu-body">
                        @foreach ($configType->tags as $key => $value)
                            @if (in_array($key, $group->group))
                                @if (Func::chekcPermission('tags.' . $key . '.man', $permissions))
                                    <li
                                        class="menu-item menu-item-main {{ $type == $key && $com == 'tags' ? 'active' : '' }}">
                                        <a href="tags/man/{{ $key }}" class="menu-link">
                                            <i class="menu-icon tf-icons ti ti-brand-superhuman"></i>
                                            <div>{{ $value->title_main }}</div>
                                        </a>
                                    </li>
                                @endif
                            @endif
                        @endforeach
                    </ul>
                </li>
            @endforeach
        @endif

        <!-- PRODUCT -->
        @if (!empty($configType->product))
            <li class="menu-header small">
                <a href="javascript:void(0);" class="menu-title">
                    <div class="text-uppercase bold">QUẢN LÝ SẢN PHẨM</div>
                </a>
                <ul class="menu-body">
                    @foreach ($configType->product as $key => $value)
                        @if (!in_array($key, $newArrayGroup))
                            @if (!empty($value->categories))
                                <li
                                    class="menu-item menu-item-main {{ $type == $key && in_array('product', explode('-', $com)) ? 'open' : '' }}">
                                    <a href="javascript:void(0);" class="menu-link menu-name menu-toggle">
                                        <i class="menu-icon tf-icons ti ti-brand-superhuman"></i>
                                        <div>{{ $value->title_main }}</div>
                                    </a>
                                    <ul class="menu-sub">

                                        @if (!empty($value->categories))
                                            @foreach ($value->categories as $k => $v)
                                                @if (Func::chekcPermission('product.' . $k . '.' . $key . '.man', $permissions))
                                                    <li
                                                        class="menu-item {{ $type == $key && $com == 'product-' . $k ? 'active' : '' }}">
                                                        <a href="product-{{ $k }}/man/{{ $key }}"
                                                            class="menu-link">
                                                            <div>{{ $v->title_main_categories }}</div>
                                                        </a>
                                                    </li>
                                                @endif
                                            @endforeach
                                        @endif

                                        @if (Func::chekcPermission('product.brand.' . $key . '.man', $permissions))
                                            @if (!empty($value->brand))
                                                <li
                                                    class="menu-item {{ $type == $key && $com == 'product-brand' ? 'active' : '' }}">
                                                    <a href="product-brand/man/{{ $key }}" class="menu-link">
                                                        <div>{{ $value->brand->title_main_brand }}</div>
                                                    </a>
                                                </li>
                                            @endif
                                        @endif

                                        @if (Func::chekcPermission('product.' . $key . '.man', $permissions))
                                            <li
                                                class="menu-item {{ $type == $key && $com == 'product' ? 'active' : '' }}">
                                                <a href="product/man/{{ $key }}" class="menu-link">
                                                    <div>{{ $value->title_main }}</div>
                                                </a>
                                            </li>
                                        @endif
                                    </ul>
                                </li>
                            @else
                                @if (Func::chekcPermission('product.' . $key . '.man', $permissions))
                                    <li
                                        class="menu-item menu-item-main {{ $type == $key && $com == 'product' ? 'active' : '' }}">
                                        <a href="product/man/{{ $key }}" class="menu-link">
                                            <i class="menu-icon tf-icons ti ti-brand-superhuman"></i>
                                            <div>{{ $value->title_main }}</div>
                                        </a>
                                    </li>
                                @endif
                            @endif
                        @endif
                    @endforeach
                </ul>
            </li>
        @endif

        <!-- ĐƠN HÀNG -->

        @if (!empty($configType->order))
            <li class="menu-header small">
                <a href="javascript:void(0);" class="menu-title">
                    <div class="text-uppercase bold">Quản lý đơn hàng</div>
                </a>
                <ul class="menu-body">
                    @foreach ($configType->order as $key => $value)
                        @if (Func::chekcPermission('order.' . $key . '.man', $permissions))
                            <li
                                class="menu-item menu-item-main {{ $type == $key && $com == 'order' ? 'active' : '' }}">
                                <a href="order/man/{{ $key }}" class="menu-link">
                                    <i class="menu-icon tf-icons ti ti-brand-superhuman"></i>
                                    <div>{{ $value->title_main }}</div>
                                </a>
                            </li>
                        @endif
                    @endforeach
                </ul>
            </li>
        @endif

        @if (!empty($configType->voucher))
            <li class="menu-header small">
                <a href="javascript:void(0);" class="menu-title">
                    <div class="text-uppercase bold">Quản lý voucher</div>
                </a>
                <ul class="menu-body">
                    @foreach ($configType->voucher as $key => $value)
                        @if (Func::chekcPermission('voucher.' . $key . '.man', $permissions))
                            <li class="menu-item menu-item-main {{ $type == $key && $com == 'voucher' ? 'active' : '' }}">
                                <a href="voucher/man/{{ $key }}" class="menu-link">
                                    <i class="menu-icon tf-icons ti ti-ticket"></i>
                                    <div>{{ $value->title_main }}</div>
                                </a>
                            </li>
                        @endif
                    @endforeach
                </ul>
            </li>
        @endif

        <!-- PROPERTIES -->
        @if (!empty($configType->properties))
            <li class="menu-header small">
                <a href="javascript:void(0);" class="menu-title">
                    <div class="text-uppercase bold">QUẢN LÝ THUỘC TÍNH</div>
                </a>
                <ul class="menu-body">
                    @foreach ($configType->properties as $key => $value)
                        @if (!empty($value->categories))
                            <li
                                class="menu-item menu-item-main {{ $type == $key && in_array('properties', explode('-', $com)) ? 'open' : '' }}">
                                <a href="javascript:void(0);" class="menu-link menu-name menu-toggle">
                                    <i class="menu-icon tf-icons ti ti-brand-superhuman"></i>
                                    <div>{{ $value->title_main }}</div>
                                </a>
                                <ul class="menu-sub">
                                    @if (!empty($value->categories))
                                        @foreach ($value->categories as $k => $v)
                                            @if (Func::chekcPermission('properties.' . $k . '.' . $key . '.man', $permissions))
                                                <li
                                                    class="menu-item {{ $type == $key && $com == 'properties-' . $k ? 'active' : '' }}">
                                                    <a href="properties-{{ $k }}/man/{{ $key }}"
                                                        class="menu-link">
                                                        <div>{{ $v->title_main_categories }}</div>
                                                    </a>
                                                </li>
                                            @endif
                                        @endforeach
                                    @endif
                                    @if (Func::chekcPermission('properties.' . $key . '.man', $permissions))
                                        <li
                                            class="menu-item {{ $type == $key && $com == 'properties' ? 'active' : '' }}">
                                            <a href="properties/man/{{ $key }}" class="menu-link">
                                                <div>{{ $value->title_main }}</div>
                                            </a>
                                        </li>
                                    @endif
                                </ul>
                            </li>
                        @else
                            @if (Func::chekcPermission('properties.' . $key . '.man', $permissions))
                                <li
                                    class="menu-item menu-item-main {{ $type == $key && $com == 'properties' ? 'active' : '' }}">
                                    <a href="properties/man/{{ $key }}" class="menu-link">
                                        <i class="menu-icon tf-icons ti ti-brand-superhuman"></i>
                                        <div>{{ $value->title_main }}</div>
                                    </a>
                                </li>
                            @endif
                        @endif
                    @endforeach
                </ul>
            </li>
        @endif

        <!-- NEWS -->
        @if (!empty($configType->news))
            <li class="menu-header small">
                <a href="javascript:void(0);" class="menu-title">
                    <div class="text-uppercase bold">QUẢN LÝ BÀI VIẾT</div>
                </a>
                <ul class="menu-body">
                    @foreach ($configType->news as $key => $value)
                        @if (!in_array($key, $newArrayGroup))
                            @if (!empty($value->categories))
                                <li class="menu-item menu-item-main {{ $type == $key ? 'open' : '' }}">
                                    <a href="javascript:void(0);" class="menu-link menu-name menu-toggle">
                                        <i class="menu-icon tf-icons ti ti-brand-superhuman"></i>
                                        <div>{{ $value->title_main }}</div>

                                    </a>
                                    <ul class="menu-sub">
                                        @if (!empty($value->categories))
                                            @foreach ($value->categories as $k => $v)
                                                @if (Func::chekcPermission('news.' . $k . '.' . $key . '.man', $permissions))
                                                    <li
                                                        class="menu-item {{ $type == $key && $com == 'news-' . $k ? 'active' : '' }}">
                                                        <a href="news-{{ $k }}/man/{{ $key }}"
                                                            class="menu-link">
                                                            <div>{{ $v->title_main_categories }}</div>
                                                        </a>
                                                    </li>
                                                @endif
                                            @endforeach
                                        @endif
                                        @if (Func::chekcPermission('news.' . $key . '.man', $permissions))
                                            <li
                                                class="menu-item {{ $type == $key && $com == 'news' ? 'active' : '' }}">
                                                <a href="news/man/{{ $key }}" class="menu-link">
                                                    <i class="menu-icon tf-icons ti ti-brand-superhuman"></i>
                                                    <div>{{ $value->title_main }}</div>
                                                </a>
                                            </li>
                                        @endif
                                    </ul>
                                </li>
                            @else
                                @if (Func::chekcPermission('news.' . $key . '.man', $permissions))
                                    @if (empty($value->dropdown))
                                        <li
                                            class="menu-item menu-item-main {{ $type == $key && $com == 'news' ? 'active' : '' }}">
                                            <a href="news/man/{{ $key }}" class="menu-link">
                                                <i class="menu-icon tf-icons ti ti-brand-superhuman"></i>
                                                <div>{{ $value->title_main }}</div>
                                            </a>
                                        </li>
                                    @endif
                                @endif
                            @endif
                        @endif
                    @endforeach
                </ul>
            </li>
        @endif

        <!-- STATIC -->

        @if (!empty($configType->static))
            <li class="menu-header small">
                <a href="javascript:void(0);" class="menu-title">
                    <div class="text-uppercase bold">QUẢN LÝ TRANG TĨNH</div>
                </a>
                <ul class="menu-body">
                    @foreach ($configType->static as $key => $value)
                        @if (!in_array($key, $newArrayGroup))
                            @if (Func::chekcPermission('static.' . $key . '.man', $permissions))
                                <li
                                    class="menu-item menu-item-main {{ $type == $key && $com == 'static' ? 'active' : '' }}">
                                    <a href="static/man/{{ $key }}" class="menu-link">
                                        <i class="menu-icon tf-icons ti ti-brand-superhuman"></i>
                                        <div>{{ $value->title_main }}</div>
                                    </a>
                                </li>
                            @endif
                        @endif
                    @endforeach
                </ul>
            </li>
        @endif

        <!-- TAGS -->
        @if (!empty($configType->tags))
            <li class="menu-header small">
                <a href="javascript:void(0);" class="menu-title">
                    <div class="text-uppercase bold">Quản lý tags</div>
                </a>
                <ul class="menu-body">
                    @foreach ($configType->tags as $key => $value)
                        @if (!in_array($key, $newArrayGroup))
                            @if (Func::chekcPermission('tags.' . $key . '.man', $permissions))
                                <li
                                    class="menu-item menu-item-main {{ $type == $key && $com == 'tags' ? 'active' : '' }}">
                                    <a href="tags/man/{{ $key }}" class="menu-link">
                                        <i class="menu-icon tf-icons ti ti-brand-superhuman"></i>
                                        <div>{{ $value->title_main }}</div>
                                    </a>
                                </li>
                            @endif
                        @endif
                    @endforeach
                </ul>
            </li>
        @endif

        <!-- BINH LUAN -->

        @if (!empty($configType->comment))
            <li class="menu-header small">
                <a href="javascript:void(0);" class="menu-title">
                    <div class="text-uppercase bold">Quản lý bình luận</div>
                </a>
                <ul class="menu-body">
                    @foreach ($configType->comment as $key => $value)
                        @if (Func::chekcPermission('comment.' . $key . '.man', $permissions))
                            <li
                                class="menu-item menu-item-main {{ $type == $key && $com == 'comment' ? 'active' : '' }}">
                                <a href="comment/man/{{ $key }}" class="menu-link">
                                    <i class="menu-icon tf-icons ti ti-brand-superhuman"></i>
                                    <div>{{ $value->title_main }}</div>
                                </a>
                            </li>
                        @endif
                    @endforeach
                </ul>
            </li>
        @endif

        <!-- PHOTO -->
        @if (!empty($configType->photo))
            <li class="menu-header small">
                <a href="javascript:void(0);" class="menu-title">
                    <div class="text-uppercase bold">QUẢN LÝ HÌNH ẢNH - LIÊN KẾT</div>
                </a>
                <ul class="menu-body">
                    @if (!empty($configType->photo))
                        @foreach ($configType->photo as $key => $value)
                            @if (!in_array($key, $newArrayGroup))
                                @if (Func::chekcPermission('photo.' . $value->kind . '.' . $key . '.man', $permissions))
                                    <li
                                        class="menu-item menu-item-main {{ $type == $key && $com == 'photo-' . $value->kind ? 'active' : '' }}">
                                        <a href="photo-{{ $value->kind }}/man/{{ $key }}"
                                            class="menu-link">
                                            <i class="menu-icon tf-icons ti ti-brand-superhuman"></i>
                                            <div>{{ $value->title_main }}</div>
                                        </a>
                                    </li>
                                @endif
                            @endif
                        @endforeach
                    @endif
                </ul>
            </li>
        @endif

        <!-- ĐĂNG KÝ NHẬN TIN -->

        @if (!empty($configType->newsletters))
            <li class="menu-header small">
                <a href="javascript:void(0);" class="menu-title">
                    <div class="text-uppercase bold">Quản lý email</div>
                </a>
                <ul class="menu-body">
                    @foreach ($configType->newsletters as $key => $value)
                        @if (Func::chekcPermission('newsletters.' . $key . '.man', $permissions))
                            <li
                                class="menu-item menu-item-main {{ $type == $key && $com == 'newsletters' ? 'active' : '' }}">
                                <a href="newsletters/man/{{ $key }}" class="menu-link">
                                    <i class="menu-icon tf-icons ti ti-brand-superhuman"></i>
                                    <div>{{ $value->title_main }}</div>
                                </a>
                            </li>
                        @endif
                    @endforeach
                </ul>
            </li>
        @endif

        <!-- ĐỊA ĐIỂM -->

        @if (!empty($configType->places))
            <li class="menu-header small">
                <a href="javascript:void(0);" class="menu-title">
                    <div class="text-uppercase bold">Quản lý địa chỉ</div>
                </a>
                <ul class="menu-body">
                    @foreach ($configType->places as $key => $value)
                        @if (!empty($value->categories))
                            <li
                                class="menu-item menu-item-main {{ $type == $key && in_array('places', explode('-', $com)) ? 'open' : '' }}">
                                <a href="javascript:void(0);" class="menu-link menu-name menu-toggle">
                                    <i class="menu-icon tf-icons ti ti-brand-superhuman"></i>
                                    <div>{{ $value->title_main }}</div>
                                </a>
                                <ul class="menu-sub">
                                    @if (!empty($value->categories))
                                        @foreach ($value->categories as $k => $v)
                                            @if (Func::chekcPermission('places.' . $k . '.' . $key . '.man', $permissions))
                                                <li
                                                    class="menu-item {{ $type == $key && $com == 'places-' . $k ? 'active' : '' }}">
                                                    <a href="places-{{ $k }}/man/{{ $key }}"
                                                        class="menu-link">
                                                        <div>{{ $v->title_main_categories }}</div>
                                                    </a>
                                                </li>
                                            @endif
                                        @endforeach
                                    @endif
                                </ul>
                            </li>
                        @endif
                    @endforeach
                </ul>
            </li>
        @endif

        @if (!empty($configType->users->active) && $adminUser->role == 3)
            <li class="menu-header small">
                <a href="javascript:void(0);" class="menu-title">
                    <div class="text-uppercase bold">Quản lý tài khoản</div>
                </a>
                <ul class="menu-body">
                    @if (!empty($configType->users->admin))
                        <li class="menu-item menu-item-main {{ $com == 'users' ? 'active' : '' }}">
                            <a href="users/man" class="menu-link">
                                <i class="menu-icon tf-icons ti ti-brand-superhuman"></i>
                                <div>Quản lý thành viên</div>
                            </a>
                        </li>
                    @endif
                    @if (!empty($configType->users->permission))
                        <li class="menu-item menu-item-main {{ $com == 'permission' ? 'active' : '' }}">
                            <a href="permission/man" class="menu-link">
                                <i class="menu-icon tf-icons ti ti-brand-superhuman"></i>
                                <div>Quản lý nhóm quyền</div>
                            </a>
                        </li>
                    @endif
                    @if (!empty($configType->users->member) && Func::chekcPermission('members.man', $permissions))
                        <li class="menu-item menu-item-main {{ $com == 'members' ? 'active' : '' }}">
                            <a href="members/man/thanh-vien" class="menu-link">
                                <i class="menu-icon tf-icons ti ti-brand-superhuman"></i>
                                <div>Quản lý user web</div>
                            </a>
                        </li>
                    @endif
                </ul>
            </li>
        @endif

        @if (!empty($configType->setting))
            <li class="menu-header small">
                <a href="javascript:void(0);" class="menu-title">
                    <div class="text-uppercase bold">CẤU HÌNH</div>
                </a>
                <ul class="menu-body">
                    @if (Func::chekcPermission('setting.cau-hinh.man', $permissions))
                        <li
                            class="menu-item menu-item-main {{ $com == 'setting' && $type == 'cau-hinh' ? 'active' : '' }}">
                            <a href="{{ url('admin', ['com' => 'setting', 'act' => 'man', 'type' => 'cau-hinh']) }}"
                                class="menu-link">
                                <i class="menu-icon tf-icons ti ti-brand-superhuman"></i>
                                <div>Thiết lập thông tin</div>
                            </a>
                        </li>
                    @endif
                </ul>
            </li>
        @endif

        @if (!empty($configType->seo))
            <li class="menu-header small">
                <a href="javascript:void(0);" class="menu-title">
                    <div class="text-uppercase bold">Quản lý seopage</div>
                </a>
                <ul class="menu-body">
                    @foreach ($configType->seo->page as $key => $value)
                        @if (Func::chekcPermission('seopage.' . $key . '.man', $permissions))
                            <li
                                class="menu-item menu-item-main {{ $type == $key && $com == 'seopage' ? 'active' : '' }}">
                                <a href="seopage/man/{{ $key }}" class="menu-link">
                                    <i class="menu-icon tf-icons ti ti-brand-superhuman"></i>
                                    <div>{{ $value }}</div>
                                </a>
                            </li>
                        @endif
                    @endforeach
                </ul>
            </li>
        @endif

        @if (!empty($configType->link))
            <li class="menu-header small">
                <a href="javascript:void(0);" class="menu-title">
                    <div class="text-uppercase bold">Công cụ seo</div>
                </a>
                <ul class="menu-body">
                    @foreach ($configType->link as $key => $value)
                        @if (Func::chekcPermission('link.' . $key . '.man', $permissions))
                            <li
                                class="menu-item menu-item-main {{ $type == $key && $com == 'link' ? 'active' : '' }}">
                                <a href="link/man/{{ $key }}" class="menu-link">
                                    <i class="menu-icon tf-icons ti ti-brand-superhuman"></i>
                                    <div>{{ $value->title_main }}</div>
                                </a>
                            </li>
                        @endif
                    @endforeach
                    @if (Func::chekcPermission('setting.dieu-huong.man', $permissions))
                        @if (config('type.setting.dieu-huong'))
                            <li
                                class="menu-item menu-item-main {{ $com == 'setting' && $type == 'dieu-huong' ? 'active' : '' }}">
                                <a href="{{ url('admin', ['com' => 'setting', 'act' => 'man', 'type' => 'dieu-huong']) }}"
                                    class="menu-link">
                                    <i class="menu-icon tf-icons ti ti-brand-superhuman"></i>
                                    <div>Điều hướng link</div>
                                </a>
                            </li>
                        @endif
                    @endif
                </ul>
            </li>
        @endif

    </ul>
</aside>


