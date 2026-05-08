<nav class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached align-items-center justify-content-between bg-navbar-theme container-fluid"
    id="layout-navbar">
    @php
        $sessionAdmin = $_SESSION['admin'] ?? null;
        $adminId = is_array($sessionAdmin) ? $sessionAdmin['admin'] ?? null : $sessionAdmin;
        $adminUser = \LARAVEL\Models\UserModel::find($adminId);
    @endphp
    <div class=" navbar-nav d-flex flex-row align-items-xl-center me-3 me-xl-0 d-flex align-items-center">
        <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none">
            <a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)">
                <i class="ti ti-menu-2 ti-sm"></i>
            </a>
        </div>
        <a class="nav-item nav-link px-0 me-xl-4 d-none d-xl-flex" href="javascript:void(0)" x-data="timeOfDayIcons"
            x-init="setTimeOfDay()">
            <i class="ti ti-sm" x-text="icon"></i>
            <span data-i18n="WelcomeAdmin" class="welcome">Xin chào: {{ $adminUser->fullname }}
            </span>
        </a>
        <div class="navbar-nav align-items-center">
            <div class="nav-item navbar-search-wrapper mb-0">
                <a class="nav-item nav-link search-toggler d-flex align-items-center px-0" href="javascript:void(0);">
                    <i class="ti ti-search ti-md me-2"></i>
                    <span class="d-none d-md-inline-block text-muted">Tìm kiếm (Ctrl+/)</span>
                </a>
            </div>
        </div>
    </div>
    <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
        <ul class="navbar-nav flex-row align-items-center ms-auto">
            <!-- Style Switcher -->
            <li class="nav-item me-2 me-xl-0">
                <a class="nav-link text-primary bold">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-brand-php"
                        width="30" height="30" viewBox="0 0 24 24" stroke-width="1.5" stroke="#81aacc"
                        fill="none" stroke-linecap="round" stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                        <path d="M12 12m-10 0a10 9 0 1 0 20 0a10 9 0 1 0 -20 0" />
                        <path
                            d="M5.5 15l.395 -1.974l.605 -3.026h1.32a1 1 0 0 1 .986 1.164l-.167 1a1 1 0 0 1 -.986 .836h-1.653" />
                        <path
                            d="M15.5 15l.395 -1.974l.605 -3.026h1.32a1 1 0 0 1 .986 1.164l-.167 1a1 1 0 0 1 -.986 .836h-1.653" />
                        <path d="M12 7.5l-1 5.5" />
                        <path d="M11.6 10h2.4l-.5 3" />
                    </svg>
                    VERSION {{ phpversion() }}
                </a>
            </li>
            <li class="nav-item me-2 me-xl-0">
                <a class="nav-link" target="_blank"
                    href="{{ request()->getSchemeAndHttpHost() . config('app.site_path') }}">
                    <i class="ti ti-md ti-arrow-back" data-bs-toggle="tooltip" data-bs-trigger="hover"
                        data-bs-placement="bottom" title="Xem Website"></i>
                </a>
            </li>
            <li class="nav-item dropdown-style-switcher dropdown me-2 me-xl-0">
                <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">
                    <i class="ti ti-md"></i>
                </a>
                <ul class="dropdown-menu dropdown-menu-end dropdown-styles">
                    <li>
                        <a class="dropdown-item" href="javascript:void(0);" data-theme="light">
                            <span class="align-middle"><i class="ti ti-sun me-2"></i>Light</span>
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="javascript:void(0);" data-theme="dark">
                            <span class="align-middle"><i class="ti ti-moon me-2"></i>Dark</span>
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="javascript:void(0);" data-theme="system">
                            <span class="align-middle"><i class="ti ti-device-desktop me-2"></i>System</span>
                        </a>
                    </li>
                </ul>
            </li>
            @if ($adminUser->role == 3)
                <li class="nav-item dropdown-shortcuts navbar-dropdown dropdown me-2 me-xl-0">
                    <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown"
                        data-bs-auto-close="outside" aria-expanded="false">
                        <i class="ti ti-layout-grid-add ti-md" data-bs-toggle="tooltip" data-bs-trigger="hover"
                        data-bs-placement="bottom" title="Liên kết nhanh"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end py-0">
                        <div class="dropdown-menu-header border-bottom">
                            <div class="dropdown-header d-flex align-items-center py-3">
                                <h5 class="text-body mb-0 me-auto">Liên kết nhanh</h5>
                            </div>
                        </div>
                        <div class="dropdown-shortcuts-list scrollable-container">
                            <div class="row row-bordered overflow-visible g-0">
                                @foreach (config('type.quicklink') ?? [] as $k => $v)
                                    <div class="dropdown-shortcuts-item col-6">
                                        <span class="dropdown-shortcuts-icon rounded-circle mb-2">
                                            {!! $v['icon'] !!}
                                        </span>
                                        <a href="{{ url('admin', $v['link']) }}"
                                            class="stretched-link">{{ $v['title'] }}</a>
                                        <small class="text-muted mb-0">{{ $v['sub_title'] }}</small>
                                    </div>
                                @endforeach
                            </div>
                            <div class="row row-bordered overflow-visible g-0">
                                <div class="dropdown-shortcuts-item col">
                                    <span class="dropdown-shortcuts-icon rounded-circle mb-2">
                                        <i class="ti ti-users fs-4"></i>
                                    </span>
                                    <a href="{{ url('admin', ['com' => 'user-admin', 'act' => 'man', 'type' => 'tai-khoan']) }}"
                                        class="stretched-link">Tài khoản</a>
                                    <small class="text-muted mb-0">Thông tin đăng nhập</small>
                                </div>
                                <div class="dropdown-shortcuts-item col">
                                    <span class="dropdown-shortcuts-icon rounded-circle mb-2">
                                        <i class="ti ti-lock fs-4"></i>
                                    </span>
                                    <a href="{{ url('admin', ['com' => 'user-admin', 'act' => 'man', 'type' => 'tai-khoan']) }}?changepass=1"
                                        class="stretched-link">Tài khoản</a>
                                    <small class="text-muted mb-0">Đổi mật khẩu</small>
                                </div>
                            </div>
                            <div class="row row-bordered overflow-visible g-0">
                                <div class="dropdown-shortcuts-item col">
                                    <span class="dropdown-shortcuts-icon rounded-circle mb-2">
                                        <i class="ti ti-chart-bar fs-4"></i>
                                    </span>
                                    <a href="{{ url('index') }}" class="stretched-link">Dashboard</a>
                                    <small class="text-muted mb-0">Bảng điều khiển</small>
                                </div>
                                <div class="dropdown-shortcuts-item col">
                                    <span class="dropdown-shortcuts-icon rounded-circle mb-2">
                                        <i class="ti ti-settings fs-4"></i>
                                    </span>
                                    <a href="{{ url('admin', ['com' => 'setting', 'act' => 'man', 'type' => 'cau-hinh']) }}"
                                        class="stretched-link">Cấu hình</a>
                                    <small class="text-muted mb-0">Thông tin công ty</small>
                                </div>
                            </div>

                        </div>
                    </div>
                </li>
            @endif
            <li class="nav-item dropdown-notifications navbar-dropdown dropdown me-3 me-xl-1">
                <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown"
                    data-bs-auto-close="outside" aria-expanded="false">
                    <i class="ti ti-bell ti-md"></i>
                    <span class="badge bg-danger rounded-pill badge-notifications">{{ Func::allNoty() }}</span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end py-0">
                    <li class="dropdown-menu-header border-bottom">
                        <div class="dropdown-header d-flex align-items-center py-3">
                            <h5 class="text-body mb-0 me-auto">Thông báo</h5>

                        </div>
                    </li>
                    <li class="dropdown-notifications-list scrollable-container">
                        <ul class="list-group list-group-flush">
                            @if (!empty($configType->newsletters))
                                @foreach ($configType->newsletters as $key => $v)
                                    <li class="list-group-item list-group-item-action dropdown-notifications-item">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-shrink-0 me-3">
                                                <div class="icon-notification">
                                                    <i class="ti ti-bell ti-md"></i>
                                                </div>
                                            </div>
                                            <div class="flex-grow-1">
                                                <h6 class="mb-1">{{ $v->title_main }} mới</h6>
                                                <small
                                                    class="text-muted">{{ \LARAVEL\Models\NewslettersModel::where('confirm_status', 1)->where('type', $key)->count() }}
                                                    tin mới</small>
                                            </div>
                                            <div class="flex-shrink-0 dropdown-notifications-actions">
                                                <a href="{{ url('admin', ['com' => 'newsletters', 'act' => 'man', 'type' => $key]) }}"
                                                    class=""><span class="badge badge-dot">Xem tin <i
                                                            class="fa-regular fa-eye"></i></span></a>
                                            </div>
                                        </div>
                                    </li>
                                @endforeach
                            @endif
                            @if (!empty($configType->order))
                                <li class="list-group-item list-group-item-action dropdown-notifications-item">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0 me-3">
                                            <div class="icon-notification">
                                                <i class="ti ti-bell ti-md"></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1">Đơn hàng mới</h6>
                                            <small
                                                class="text-muted">{{ \LARAVEL\Models\OrdersModel::where('order_status', 1)->count() }}
                                                đơn hàng mới</small>
                                        </div>
                                        <div class="flex-shrink-0 dropdown-notifications-actions">
                                            <a href="{{ url('admin', ['com' => 'order', 'act' => 'man', 'type' => 'don-hang']) }}"
                                                class=""><span class="badge badge-dot">Xem đơn hàng <i
                                                        class="fa-regular fa-eye"></i></span></a>
                                        </div>
                                    </div>
                                </li>
                            @endif

                        </ul>
                    </li>

                </ul>
            </li>
            <!--/ Notification -->

            <!-- User -->
            <li class="nav-item navbar-dropdown dropdown-user dropdown">
                <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">
                    <div class="avatar avatar-online">
                        <i class="ti ti-user ti-md" data-bs-toggle="tooltip" data-bs-trigger="hover"
                            data-bs-placement="bottom" title="Cấu hình"></i>

                    </div>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        @if ($adminUser->role == 3)
                            <a class="dropdown-item"
                                href="{{ url('admin', ['com' => 'user-admin', 'act' => 'man', 'type' => 'tai-khoan']) }}">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0 me-2">
                                        <div class="avatar avatar-online">
                                            <i class="ti ti-user ti-sm"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1">
                                        <span
                                            class="fw-medium d-block text-capitalize">{{ $adminUser->fullname }}</span>

                                    </div>
                                </div>
                            </a>
                        @else
                            <a class="dropdown-item"
                                href="{{ url('user.edit', ['com' => 'users', 'act' => 'edit'], ['id' => session('admin')]) }}">
                                <div class="d-flex">
                                    <div class="flex-shrink-0 me-2">
                                        <div class="avatar avatar-online">
                                            <i class="ti ti-user ti-sm"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1">
                                        <span
                                            class="fw-medium d-block text-capitalize">{{ $adminUser->fullname }}</span>

                                    </div>
                                </div>
                            </a>
                        @endif

                    </li>

                    <li>
                        <div class="dropdown-divider"></div>
                    </li>
                    @if ($adminUser->role == 3)
                        <li>
                            <a class="dropdown-item"
                                href="{{ url('admin', ['com' => 'extensions', 'act' => 'man', 'type' => 'hotline']) }}">
                                <i class="ti ti-phone me-2 ti-sm"></i>
                                <span class="align-middle">Cấu hình điện thoại</span>
                            </a>
                        </li>
                        <li>
                            <div class="dropdown-divider"></div>
                        </li>
                        <li>
                            <a class="dropdown-item"
                                href="{{ url('admin', ['com' => 'extensions', 'act' => 'man', 'type' => 'social']) }}">
                                <i class="ti ti-social me-2 ti-sm"></i>
                                <span class="align-middle">Cấu hình mạng xã hội</span>
                            </a>
                        </li>
                        <li>
                            <div class="dropdown-divider"></div>
                        </li>
                        <li>
                            <a class="dropdown-item"
                                href="{{ url('admin', ['com' => 'extensions', 'act' => 'man', 'type' => 'popup']) }}">
                                <i class="ti ti-device-camera-phone me-2 ti-sm"></i>
                                <span class="align-middle">Cấu hình popup</span>
                            </a>
                        </li>
                        <li>
                            <div class="dropdown-divider"></div>
                        </li>
                        <li>
                            <a class="dropdown-item"
                                href="{{ url('admin', ['com' => 'setting', 'act' => 'man', 'type' => 'cau-hinh']) }}">
                                <i class="ti ti-settings me-2 ti-sm"></i>
                                <span class="align-middle">Cấu hình chung</span>
                            </a>
                        </li>
                        <li>
                            <div class="dropdown-divider"></div>
                        </li>
                        <li>
                            <a class="dropdown-item" href="{{ url('deleteCache') }}">
                                <i class="ti ti-trash-off me-2 ti-sm"></i>
                                <span class="align-middle">Xóa files cache</span>
                            </a>
                        </li>
                        <li>
                            <div class="dropdown-divider"></div>
                        </li>
                        <li>
                            <a class="dropdown-item"
                                href="{{ url('admin', ['com' => 'log', 'act' => 'man', 'type' => 'history']) }}">
                                <svg xmlns="http://www.w3.org/2000/svg"
                                    class="icon icon-tabler icon-tabler-history me-2" width="22" height="22"
                                    viewBox="0 0 24 24" stroke-width="1.5" stroke="#666" fill="none"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                    <path d="M12 8l0 4l2 2" />
                                    <path d="M3.05 11a9 9 0 1 1 .5 4m-.5 5v-5h5" />
                                </svg>
                                <span class="align-middle">Lịch sử</span>
                            </a>
                        </li>

                        <li>
                            <div class="dropdown-divider"></div>
                        </li>
                    @endif
                    <li>
                        <a class="dropdown-item" href="{{ url('logoutAdmin') }}">
                            <i class="ti ti-logout me-2 ti-sm"></i>
                            <span class="align-middle">Đăng xuất</span>
                        </a>
                    </li>
                </ul>
            </li>
            <!--/ User -->
        </ul>
    </div>

    <div class="navbar-search-wrapper search-input-wrapper d-none">
        <span class="twitter-typeahead" style="position: relative; display: inline-block;"><input type="text"
                class="form-control search-input border-0 container-fluid tt-input"
                placeholder="Nhập từ khóa tìm kiếm..." aria-label="Search..." autocomplete="off" spellcheck="false"
                dir="auto" style="position: relative; vertical-align: top;">
            <pre aria-hidden="true"
                style="position: absolute; visibility: hidden; white-space: pre; font-family: &quot;Public Sans&quot;, -apple-system, BlinkMacSystemFont, &quot;Segoe UI&quot;, Oxygen, Ubuntu, Cantarell, &quot;Fira Sans&quot;, &quot;Droid Sans&quot;, &quot;Helvetica Neue&quot;, sans-serif; font-size: 15px; font-style: normal; font-variant: normal; font-weight: 400; word-spacing: 0px; letter-spacing: 0px; text-indent: 0px; text-rendering: auto; text-transform: none;"></pre>
            <div class="tt-menu navbar-search-suggestion ps"
                style="position: absolute; top: 100%; left: 0px; z-index: 100; display: none;">
                <div class="tt-dataset tt-dataset-pages"></div>
                <div class="tt-dataset tt-dataset-files"></div>
                <div class="tt-dataset tt-dataset-members"></div>
                <div class="ps__rail-x" style="left: 0px; bottom: 0px;">
                    <div class="ps__thumb-x" tabindex="0" style="left: 0px; width: 0px;"></div>
                </div>
                <div class="ps__rail-y" style="top: 0px; right: 0px;">
                    <div class="ps__thumb-y" tabindex="0" style="top: 0px; height: 0px;"></div>
                </div>
            </div>
        </span>
        <i class="ti ti-x ti-sm search-toggler cursor-pointer"></i>
    </div>
</nav>
@pushonce('scripts')
    <script>
        function timeOfDayIcons() {
            return {
                icon: '',
                setTimeOfDay() {
                    const hours = new Date().getHours();
                    if (hours >= 6 && hours < 10) {
                        this.icon = '🌅';
                    } else if (hours >= 10 && hours < 17) {
                        this.icon = '🌞';
                    } else if (hours >= 17 && hours < 20) {
                        this.icon = '🌇';
                    } else {
                        this.icon = '🌙';
                    }
                }
            }
        }
    </script>
@endpushonce
