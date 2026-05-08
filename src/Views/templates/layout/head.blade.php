<base href="{{ config('app.asset') }}">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>{!! Seo::get('title') !!}</title>
<meta name="description" content="{!! Seo::get('description') !!}">
<meta name="keywords" content="{!! Seo::get('keywords') !!}">
@if (!empty(Request()->preview))
    <meta name="robots" content="noindex,nofollow">
@else
    <meta name="robots"
        content="{{ !\LARAVEL\Core\Support\Str::isEmpty(Seo::get('meta')) ? Seo::get('meta') : 'index,follow,noodp' }}">
@endif
<link rel="icon" type="image/x-icon" href="{{ assets_photo('photo', '48x48x1', $favicon['photo']) }}">
{!! Func::decodeHtmlChars($setting['mastertool']) !!}
{!! Func::decodeHtmlChars($setting['analytics']) !!}
{!! Func::decodeHtmlChars($setting['headjs']) !!}
<meta name="geo.region" content="VN">
<meta name="geo.placename" content="Ho Chi Minh">
<meta name="geo.position" content="10.823099;106.629664">
<meta name="ICBM" content="10.823099, 106.629664">
<meta name='revisit-after' content='1 days'>
<meta name="author" content="{!! $setting['namevi'] !!}">
<meta name="copyright" content="{!! $setting['namevi'] . ' - [' . $optSetting['email'] . ']' !!}">
<meta property="og:type" content="{{ Seo::get('type') }}">
<meta property="og:site_name" content="{!! $setting['namevi'] !!}">
<meta property="og:title" content="{!! Seo::get('title') !!}">
<meta property="og:description" content="{!! Seo::get('description') !!}">
<meta property="og:url" content="{{ Seo::get('url') }}">
<meta property="og:image" content="{{ Seo::get('photo') }}">
<meta property="og:image:alt" content="{!! Seo::get('title') !!}">
<meta property="og:image:type" content="{{ Seo::get('photo:type') }}">
<meta property="og:image:width" content="{{ Seo::get('photo:width') }}">
<meta property="og:image:height" content="{{ Seo::get('photo:height') }}">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:site" content="{{ $optSetting['email'] }}">
<meta name="twitter:creator" content="{!! $setting['namevi'] !!}">
@canonical
<meta name="format-detection" content="telephone=no">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
@if (!Func::isGoogleSpeed())
@endif
