<?php
namespace LARAVEL\Core\Config;

use LARAVEL\Core\ServiceProvider;

class ConfigServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        date_default_timezone_set(config('app.timezone'));
    }
    public function register(): void
    {
        if(env('APP_PROTOCOL') !== null){
            if(env('APP_PROTOCOL')==true) {
                $this->app->make('config')->set('app.secure_ssl', true);
            }else{
                http_response_code(500);
                die("Hệ thống gặp lỗi nghiêm trọng, vui lòng liên hệ quản trị website!");
            }
        }else{
            $this->app->make('config')->set('app.secure_ssl', false);
        }
    }
}