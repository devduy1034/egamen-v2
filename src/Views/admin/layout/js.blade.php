<!-- Js Config -->
<script type="text/javascript">
    var PHP_VERSION = {{ PHP_VERSION_ID }};
    var CONFIG_BASE = "{{ config('app.admin') }}";
    var ADMIN = '{{ config('app.admin') }}';
    var ASSET = '{{ config('app.asset') }}';
    var TOKEN = '{{ config('app.token') }}';
    var SLUG_LANG = @json(implode(',', array_keys(config('app.slugs'))));
    var CSRF_TOKEN = '{{ csrf_token() }}';
    var LINK_FILTER = '{{ url('admin',['com'=>$com,'act'=>'man','type'=>$type]) }}';
    var ID = {{ !empty($id) ? $id : 0 }};
    var COM = '{{ !empty($com) ? $com : '' }}';
    var ACT = '{{ !empty($act) ? $act : '' }}';
    var TYPE = '{{ !empty($type) ? $type : '' }}';
    var HASH = '{{ Func::generateHash() }}';
    var TYPE_IMG = "{{ str_replace(',', '|', config('type.type_img')) }}";
    var TYPE_FILE = "{{ str_replace(',', '|', config('type.type_file')) }}";
    var TYPE_VIDEO = "{{ str_replace(',', '|', config('type.type_video')) }}";
    var ACTIVE_GALLERY = {{ !empty($gallery) ? 'true' : 'false' }};
    var ACTIVE_PROPERTIES_CATEGORIES = {{ !empty($configType->categoriesProperties) ? 'true' : 'false' }};
    var BASE64_QUERY_STRING = '{{ base64_encode($_SERVER['QUERY_STRING']) }}';
    var LOGIN_PAGE = {{ empty($_SESSION['admin_login']['active']) ? 'true' : 'false' }};
    var MAX_DATE = '{{ date('Y/m/d', time()) }}';
    var CHARTS = {!! !empty($charts) ? json_encode($charts) : '{}' !!};
    var ADD_OR_EDIT_PERMISSIONS =
        {{ !empty($com) && $com == 'user' && !empty($act) && in_array($act, ['add_permission_group', 'edit_permission_group']) ? 'true' : 'false' }};
    var IMPORT_IMAGE_EXCELL =
        {{ !empty($com) && $com == 'import' && !empty($config['import']['images']) ? 'true' : 'false' }};
    var ORDER_ADVANCED_SEARCH =
        {{ !empty($com) && $com == 'order' && !empty($configMan->search) ? 'true' : 'false' }};
    var ORDER_MIN_TOTAL = {{ !empty($minTotal) ? $minTotal : 1 }};
    var ORDER_MAX_TOTAL = {{ !empty($maxTotal) ? $maxTotal : 1 }};
    var ORDER_PRICE_FROM = {{ !empty($price_from) ? $price_from : 1 }};
    var ORDER_PRICE_TO = {{ !empty($price_to) ? $price_to : (!empty($maxTotal) ? $maxTotal : 1) }};
</script>

<!-- build -->
<script src="@asset('assets/admin/vendor/libs/jquery/jquery.js')"></script>
{{-- <script src="@asset('assets/admin/vendor/libs/popper/popper.js')"></script> --}}
<script src="@asset('assets/admin/vendor/js/bootstrap.js')"></script>
{{-- <script src="@asset('assets/admin/vendor/libs/node-waves/node-waves.js')"></script> --}}
<script src="@asset('assets/admin/vendor/libs/perfect-scrollbar/perfect-scrollbar.js')"></script>
{{-- <script src="@asset('assets/admin/vendor/libs/hammer/hammer.js')"></script> --}}

<script src="@asset('assets/admin/vendor/libs/typeahead-js/typeahead.js')"></script>
<script src="@asset('assets/admin/vendor/js/menu.js')"></script>
<script src="@asset('assets/admin/vendor/libs/select2/select2.js')" defer></script>
<!-- Main JS -->
<script src="@asset('assets/admin/js/moment.min.js')"></script>
<script src="@asset('assets/admin/confirm/confirm.js')"></script>
<script src="@asset('assets/admin/js/priceFormat.js')" defer></script>
<script src="@asset('assets/holdon/HoldOn.js')"></script>
<script src="@asset('assets/admin/simplenotify/simple-notify.js')"></script>
<script src="@asset('assets/admin/fancybox5/fancybox.umd.js')"></script>
<script src="@asset('assets/admin/fancybox5/fancybox.umd.js')"></script>
<script src="@asset('assets/admin/js/cropper.js')"></script>

@if (!empty($com) && $com == 'newsletters')
    <script src="@asset('assets/admin/js/app-email.js')"></script>
@endif

<script src="@asset('assets/admin/toastify/toastify.js')" defer></script>
<script src="@asset('assets/admin/js/apps.js')" defer></script>
<script src="@asset('assets/js/alpinejs.js')" defer></script>
<script src="@asset('assets/admin/js/main.js')"></script>
<script src="@asset('assets/admin/js/main_crop.js')"></script>
@stack('scripts')