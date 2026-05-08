<?php


use LARAVEL\Core\Routing\LARAVELRouter;

LARAVELRouter::group(['namespace' => 'Web', 'prefix' => config('app.web_prefix'), 'middleware' => [\LARAVEL\Middlewares\LangRequest::class, \LARAVEL\Middlewares\CheckRedirect::class]], function ($language = 'vi') {
    LARAVELRouter::get('/change-lang/{lang}', function ($lang) {
        if (\Illuminate\Support\Arr::has(config('app.langs'), $lang)) {
            session()->set('locale', $lang);
            app()->make('config')->set('app.seo_default', $lang);
            response()->redirect(url('home', ['language' => $lang]));
        }
    })->name('changelang');

    // LARAVELRouter::get('/change-lang/{lang}', function ($lang) {
    //     if(\Illuminate\Support\Arr::has(config('app.langs'),$lang)){
    //         session()->set('locale',$lang);
    //         app()->make('config')->set('app.seo_default',$lang);
    //         response()->redirect(linkReferer());
    //     }
    // });

    LARAVELRouter::get('/', 'HomeController@index')->name('home');
    LARAVELRouter::get('/index', 'HomeController@index')->name('index');

    LARAVELRouter::get('/load-token', 'ApiController@token')->name('token');
    LARAVELRouter::get('/load-video', 'ApiController@video')->name('load-video');

    LARAVELRouter::get('/load-product', 'HomeController@ajaxProduct')->name('load-product');
    LARAVELRouter::get('/san-pham-moi', 'ProductController@newProduct')->name('new-product');
    LARAVELRouter::get('/tags-san-pham', 'TagsController@index')->name('tags');
    LARAVELRouter::get('/hang', 'ProductController@allBrand')->name('hang');

    foreach (config('type.product') as $key => $config) {
        if (isset($config['routes']) && $config['routes'] === true) {
            LARAVELRouter::get('/' . $key, 'ProductController@index')->name($key);
        }
    }

    foreach (config('type.news') as $key => $config) {
        if (isset($config['routes']) && $config['routes'] === true) {
            LARAVELRouter::get('/' . $key, 'NewsController@index')->name($key);
        }
    }

    foreach (config('type.static') as $key => $config) {
        if (isset($config['routes']) && $config['routes'] === true) {
            LARAVELRouter::get('/' . $key, 'StaticController@index')->name($key);
        }
    }

    foreach (config('type.photo') as $key => $config) {
        if (isset($config['routes']) && $config['routes'] === true) {
            LARAVELRouter::get('/' . $key, 'PhotoController@index')->name($key);
        }
    }

    LARAVELRouter::get('/lien-he', 'ContactController@index')->name('lien-he');
    LARAVELRouter::post('/lien-he-post', 'ContactController@submit')->name('lien-he-post');
    LARAVELRouter::post('/dang-ky-nhan-tin', 'HomeController@letter')->name('letter');


    LARAVELRouter::get('/tim-kiem', 'ProductController@searchProduct')->name('tim-kiem');
    LARAVELRouter::get('/tim-kiem-goi-y', 'ProductController@suggestProduct')->name('tim-kiem-goi-y');
    LARAVELRouter::post('/smart-search', 'SmartSearchController@search')->name('smart-search');
    LARAVELRouter::get('/api/search-quick', 'SmartSearchController@quickSearch')->name('api.search-quick');
    LARAVELRouter::get('/api/recommendations', 'RecommendationController@index')->name('api.recommendations');
    LARAVELRouter::post('/api/events/track', 'EventTrackingController@track')->name('api.events.track');

    LARAVELRouter::get('/account/login', 'AuthController@showLogin')->name('user.login');
    LARAVELRouter::post('/account/login', 'AuthController@login')->name('user.login.submit');
    LARAVELRouter::get('/account/forgot-password', 'AuthController@showForgotPassword')->name('user.forgot');
    LARAVELRouter::post('/account/forgot-password', 'AuthController@forgotPassword')->name('user.forgot.submit');
    LARAVELRouter::get('/account/reset-password', 'AuthController@showResetPassword')->name('user.reset');
    LARAVELRouter::post('/account/reset-password', 'AuthController@resetPassword')->name('user.reset.submit');
    LARAVELRouter::get('/account/login/google', 'AuthController@googleRedirect')->name('user.login.google');
    LARAVELRouter::get('/account/login/google/callback', 'AuthController@googleCallback')->name('user.login.google.callback');
    LARAVELRouter::get('/account/register', 'AuthController@showRegister')->name('user.register');
    LARAVELRouter::post('/account/register', 'AuthController@register')->name('user.register.submit');
    LARAVELRouter::get('/account/logout', 'AuthController@logout')->name('user.logout');

    LARAVELRouter::group(['middleware' => [\LARAVEL\Middlewares\LoginUser::class]], function () {
        LARAVELRouter::get('/account', 'AuthController@account')->name('user.account');
        LARAVELRouter::get('/account/orders/detail', 'AuthController@accountOrderDetail')->name('user.account.orders.detail');
        LARAVELRouter::post('/account/orders/cancel', 'AuthController@accountCancelOrder')->name('user.account.orders.cancel');
        LARAVELRouter::post('/account/profile', 'AuthController@updateProfile')->name('user.account.profile');
        LARAVELRouter::post('/account/address', 'AuthController@saveAddress')->name('user.account.address.save');
        LARAVELRouter::post('/account/address/delete', 'AuthController@deleteAddress')->name('user.account.address.delete');
        LARAVELRouter::get('/account/wards', 'AuthController@wardsByCity')->name('user.account.wards');
        LARAVELRouter::post('/account/password/change', 'AuthController@changePassword')->name('user.account.password.change');
        LARAVELRouter::post('/account/google/unlink', 'AuthController@unlinkGoogle')->name('user.account.google.unlink');
        LARAVELRouter::post('/account/session/revoke', 'AuthController@revokeSession')->name('user.account.session.revoke');
        LARAVELRouter::post('/account/session/revoke-others', 'AuthController@revokeOtherSessions')->name('user.account.session.revoke.others');
    });

    LARAVELRouter::post('/cart/{action}', 'CartController@handle')->name('cart');
    LARAVELRouter::get('/vnpay/return', 'CartController@vnpayReturn')->name('vnpay.return');
    LARAVELRouter::get('/vnpay/ipn', 'CartController@vnpayIpn')->name('vnpay.ipn');
    LARAVELRouter::post('/wishlist/{action}', 'WishlistController@handle')->name('wishlist');
    LARAVELRouter::get('/yeu-thich', 'WishlistController@page')->name('wishlist.page');
    LARAVELRouter::get('/gio-hang', 'CartController@showcart')->name('giohang');
    LARAVELRouter::get('/tra-cuu-don-hang', 'CartController@orderLookup')->name('order.lookup');
    LARAVELRouter::get('/dat-hang-thanh-cong', 'CartController@orderSuccess')->name('order.success');

    LARAVELRouter::post('/comment/{action}', 'CommentController@handle')->name('comment');
    LARAVELRouter::get('/{slug}', 'SlugController@handle')->where(['slug' => '.*'])->name('slugweb');
});
