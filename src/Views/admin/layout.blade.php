<!DOCTYPE html>
<html lang="vi" class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" dir="ltr" data-theme="theme-default" data-assets-path="{{assets('assets/admin/')}}" data-template="vertical-menu-template">
<head>
    @include('layout.head')
    @include('layout.css')
</head>

<body>
    @if (request()->path() == 'admin/user/login')
        @yield('content')
    @else
        <div class="layout-wrapper layout-content-navbar">
            <div class="layout-container">
                @include('layout.menu')
                <div class="layout-page">
                    @include('layout.navbar')
                    <div class="content-wrapper ">
                        @yield('content')
                        @include('layout.footer')
                        <div class="content-backdrop fade"></div>
                    </div>
                </div>
            </div>
            <div class="layout-overlay layout-menu-toggle"></div>
            <div class="drag-target"></div>
        </div>
    @endif
    @include('layout.js')
</body>

</html>
