<?php


namespace LARAVEL\Middlewares;

use LARAVEL\Core\Support\Facades\Auth;
use Pecee\Http\Middleware\IMiddleware;
use Pecee\Http\Request;
use Illuminate\Support\Str;
class LoginAdmin implements IMiddleware
{
    public function handle(Request $request): void
    {
    	$path_admin_array = explode('/',Str::after(config('app.admin_prefix'),'/'));
        $path_admin = end($path_admin_array);
        if($request->getUrl()->getPath() != (config('app.admin_prefix').'/user/logout/') && session()->has('admin')) return;
        if (!session()->has('admin') && $request->getUrl()->getPath() != substr(config('app.site_path'), 0, -1).'/'.$path_admin.'/user/login/' && (session()->has('admin') == false)) {
            response()->redirect(url('loginAdmin'));
        }
    }
}