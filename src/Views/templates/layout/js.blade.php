<script>
    var NN_FRAMEWORK = NN_FRAMEWORK || {};
    var ASSET = '{{ assets('assets/') }}';
    var BASE = '{{ assets() }}';
    var CSRF_TOKEN = '{{ csrf_token() }}';
    var WEBSITE_NAME = '{{ !empty($setting['name' . $lang]) ? addslashes($setting['name' . $lang]) : '' }}';
    var RECAPTCHA_ACTIVE = {{ !empty(config('app.recaptcha.active')) ? 'true' : 'false' }};
    var RECAPTCHA_SITEKEY = '{{ config('app.recaptcha.sitekey') }}';
    var GOTOP = ASSET + 'images/top.png';
    var CART_URL = {
        'ADD_CART' : '{{ url('cart', ['action' => 'add-to-cart']) }}',
        'UPDATE_CART' : '{{ url('cart', ['action' => 'update-cart']) }}',
        'DELETE_CART' : '{{ url('cart', ['action' => 'delete-cart']) }}',
        'DELETE_ALL_CART' : '{{ url('cart', ['action' => 'delete-all-cart']) }}',
        'PAGE_CART':'{{ url('giohang') }}',
    };
    var WISHLIST_URL = {
        'STATE': '{{ url('wishlist', ['action' => 'state']) }}',
        'TOGGLE': '{{ url('wishlist', ['action' => 'toggle']) }}',
        'MERGE': '{{ url('wishlist', ['action' => 'merge']) }}',
        'LIST': '{{ url('wishlist', ['action' => 'list']) }}',
        'REMOVE': '{{ url('wishlist', ['action' => 'remove']) }}',
        'GUEST_LIST': '{{ url('wishlist', ['action' => 'guest-list']) }}',
        'VARIANT_OPTIONS': '{{ url('wishlist', ['action' => 'variant-options']) }}'
    };
    var IS_MEMBER = {{ session()->has('member') ? 'true' : 'false' }};
    var MEMBER_ID = {{ (int) (is_array(session()->get('member')) ? (session()->get('member')['member'] ?? 0) : session()->get('member', 0)) }};
     var LANG = {
        'no_keywords': '{{ __('web.no_keywords') }}',
        'thongbao': '{{ __('web.thongbao') }}',
        'dongy': '{{ __('web.dongy') }}',
    }
</script>

@php
    $toast = session()->get('toast');
    $toastText = is_array($toast) ? trim((string) ($toast['text'] ?? '')) : '';
    $toastStatus = is_array($toast) ? trim((string) ($toast['status'] ?? 'success')) : 'success';
    if ($toastText !== '') {
        session()->unset('toast');
    }

    if ($toastText === '') {
        $queryToastText = trim((string) ($_GET['toast_text'] ?? ''));
        $queryToastStatus = trim((string) ($_GET['toast_status'] ?? 'success'));
        if ($queryToastText !== '') {
            $toastText = $queryToastText;
            $toastStatus = in_array($queryToastStatus, ['success', 'warning', 'error'], true)
                ? $queryToastStatus
                : 'success';
        }
    }
@endphp
@if ($toastText !== '')
    <script>
        window.addEventListener('load', function () {
            if (typeof showNotify === 'function') {
                showNotify(@json($toastText), 'Thông báo', @json($toastStatus));
            }
            if (window.history && window.history.replaceState) {
                try {
                    var currentUrl = new URL(window.location.href);
                    currentUrl.searchParams.delete('toast_text');
                    currentUrl.searchParams.delete('toast_status');
                    var nextUrl = currentUrl.pathname + currentUrl.search + currentUrl.hash;
                    window.history.replaceState({}, document.title, nextUrl);
                } catch (e) {}
            }
        });
    </script>
@endif

@php
    jsminify()->set('js/jquery.min.js');
    jsminify()->set('bootstrap/bootstrap.js');
    jsminify()->set('holdon/HoldOn.js');
    jsminify()->set('simplenotify/simple-notify.js');
    jsminify()->set('fancybox5/fancybox.umd.js');
    jsminify()->set('fotorama/fotorama.js');
    jsminify()->set('confirm/confirm.js');
    jsminify()->set('swiper/swiper-bundle.min.js');
    jsminify()->set('js/functions.js');
    jsminify()->set('js/cart.js');
    jsminify()->set('js/wishlist.js');
    jsminify()->set('js/apps.js');
    echo jsminify()->get();
@endphp
@stack('scripts')

@if (!empty(config('app.recaptcha.active'))) 
   
        <script>
            if (isExist($("#form-newsletter")) || isExist($("#form-contact"))) {
                $('<script>').attr({
                    'src': "https://www.google.com/recaptcha/api.js?render={{ config('app.recaptcha.sitekey') }}",
                    'async': ''
                }).insertBefore($('script:first'))
                /* Newsletter */
                document.getElementById('form-newsletter')?.addEventListener("submit", function(event) {
                    event.preventDefault();
                    grecaptcha.ready(async function() {
                        await generateCaptcha('newsletter', 'recaptchaResponseNewsletter', 'form-newsletter');
                    });
                }, false);
                /* Contact */
                document.getElementById('form-contact')?.addEventListener("submit", function(event) {
                    event.preventDefault();
                    grecaptcha.ready(async function() {
                        await generateCaptcha('contact', 'recaptchaResponseContact', 'form-contact');
                    });
                }, false);
            }
        </script>

@endif

@if (!Func::isGoogleSpeed())
    <script src="@asset('assets/js/alpinejs.js')" defer></script>

@endif
{!! Func::decodeHtmlChars($setting['bodyjs']) !!}
