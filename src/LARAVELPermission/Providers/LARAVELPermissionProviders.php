<?php



namespace LARAVELPermission\Providers;

use LARAVEL\Core\ServiceProvider;
use LARAVEL\Core\Support\Facades\Auth;

class LARAVELPermissionProviders extends ServiceProvider
{
    protected $defer = true;
    public function boot(): void {
        if(request()->segment(1)=='admin'){
            if(Auth::guard('admin')->check() && !Auth::guard('admin')->user()->hasRole('Admin') && config('type.users.permission')){
                $permissions = Auth::guard('admin')->user()->roles()->first()->permissions()->pluck('name')->toArray();
                app()->make('view')->setCanFunction(function($action, $subject = null) use ($permissions) {
                    if(in_array($action,$permissions)) return true;
                    return false;
                });
            }
        }
    }
    public function register(): void {}
}