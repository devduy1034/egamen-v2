
@if (!Func::isGoogleSpeed() || $com!='trang-chu' || $com!='')
    <div class="a2a_kit a2a_kit_size_32 a2a_default_style">
        <a class="a2a_dd" href="https://www.addtoany.com/share"></a>
        <a class="a2a_button_facebook"></a>
        <a class="a2a_button_twitter"></a>
        <a class="a2a_button_facebook_messenger"></a>
        <a class="a2a_button_copy_link"></a>
    </div>
    @push('scripts')
    <script src="https://sp.zalo.me/plugins/sdk.js"></script>
    <script async src="https://static.addtoany.com/menu/page.js"></script>
    @endpush
@endif