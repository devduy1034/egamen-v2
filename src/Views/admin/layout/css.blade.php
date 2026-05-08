<!-- Icons -->
{{-- <link rel="stylesheet" href="@asset('assets/admin/vendor/fonts/fontawesome.css')" /> --}}
<link rel="stylesheet" href="@asset('assets/admin/vendor/fonts/tabler-icons.css')" />
{{-- <link rel="stylesheet" href="@asset('assets/admin/vendor/fonts/flag-icons.css')" /> --}}
<!-- Core CSS -->
<link rel="stylesheet" href="@asset('assets/admin/css/style-tailwind.css')" />
<link rel="stylesheet" href="@asset('assets/admin/vendor/css/rtl/core.css')" class="template-customizer-core-css" />
<link rel="stylesheet" href="@asset('assets/admin/vendor/css/rtl/theme-default.css')" class="template-customizer-theme-css" />


<link rel="stylesheet" href="@asset('assets/admin/confirm/confirm.css')">
<link rel="stylesheet" href="@asset('assets/holdon/HoldOn.css')">
{{-- <link rel="stylesheet" href="@asset('assets/admin/toastify/toastify.css')" /> --}}
<!-- Vendors CSS -->
{{-- <link rel="stylesheet" href="@asset('assets/admin/vendor/libs/node-waves/node-waves.css')" /> --}}
<link rel="stylesheet" href="@asset('assets/admin/vendor/libs/perfect-scrollbar/perfect-scrollbar.css')" />
<link rel="stylesheet" href="@asset('assets/admin/vendor/libs/typeahead-js/typeahead.css')" />
<link rel="stylesheet" href="@asset('assets/admin/vendor/libs/select2/select2.css')" />
<link rel="stylesheet" href="@asset('assets/admin/fancybox5/fancybox.css')">
<link rel="stylesheet" href="@asset('assets/admin/cropper/cropper.min.css')">
@if (!empty($com) && $com == 'comment')
    <link rel="stylesheet" href="@asset('assets/admin/css/comment.css')">
@endif
@if (!empty($com) && $com == 'newsletters')
    <link rel="stylesheet" href="@asset('assets/admin/vendor/css/pages/app-email.css')" />
@endif
<!-- Helpers -->
<script src="@asset('assets/admin/vendor/js/helpers.js')"></script>
<script src="@asset('assets/admin/vendor/js/template-customizer.js')"></script>
<script src="@asset('assets/admin/js/config.js')"></script>
<link rel="stylesheet" href="@asset('assets/admin/css/main.css')" />
@stack('styles')
