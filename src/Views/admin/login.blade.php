<!DOCTYPE html>
<html lang="vi" class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" dir="ltr" data-bs-theme-color="color-3" data-theme="theme-default" data-assets-path="{{assets('assets/admin/')}}" data-template="vertical-menu-template">
<head>
    @include('layout.head')
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&ampdisplay=swap"
            rel="stylesheet" />
    <link rel="stylesheet" href="@asset('assets/admin/vendor/fonts/fontawesome.css')" />
    <link rel="stylesheet" href="@asset('assets/admin/vendor/fonts/tabler-icons.css')" />
    <link rel="stylesheet" href="@asset('assets/admin/vendor/fonts/flag-icons.css')" />
    <link rel="stylesheet" href="@asset('assets/admin/css/style-tailwind.css')" />
    <link rel="stylesheet" href="@asset('assets/admin/vendor/css/rtl/core.css')" class="template-customizer-core-css" />
    <link rel="stylesheet" href="@asset('assets/admin/vendor/css/rtl/theme-default.css')" class="template-customizer-theme-css" />
    <link rel="stylesheet" href="@asset('assets/admin/css/main.css')" />
    @stack('styles')
</head>
<body>
    @yield('content')
    <script type="text/javascript">
        var PHP_VERSION = {{ PHP_VERSION_ID }};
        var CONFIG_BASE = "{{ config('app.admin') }}";
        var TOKEN = '{{ config('app.token') }}';
        var CSRF_TOKEN = '{{ csrf_token() }}';
        var LOGIN_PAGE = {{ \LARAVEL\Core\Support\Facades\Auth::guard('admin')->check() ? 'true' : 'false' }};
    </script>
    <script src="@asset('assets/js/alpinejs.js')" defer></script>
    @stack('scripts')
</body>
</html>