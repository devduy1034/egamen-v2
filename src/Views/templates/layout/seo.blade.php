<ul class="h-card hidden">
    <li class="h-fn fn">{{ $setting['name' . $lang] }}</li>
    <li class="h-org org">{{ $setting['name' . $lang] }}</li>
    <li class="h-tel tel">{{ preg_replace('/[^0-9]/', '', $optSetting['hotline']) }}</li>
    <li><a class="u-url ul" href="{{ config('app.asset') }}">{{ config('app.asset') }}</a></li>
</ul>
@if (!empty($com) && $com == 'trang-chu')
    <h1 class="hidden">{{ $setting['name' . $lang] }}</h1>
@endif