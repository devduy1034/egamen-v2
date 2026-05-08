<?php


namespace LARAVEL\Controllers\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SlugController
{
    public function handle($com, $act, $type, Request $request)
    {
        $path_admin_array = explode('/',Str::after(config('app.admin_prefix'),'/'));
        $admin_base = end($path_admin_array);
        if ($request->is($admin_base.'/*')) {
            $path = explode('/', $request->path());
            $com = $path[1] ?? '';
            $controllerAlias = $com === 'product-crawler' ? 'ProductCrawler' : ucfirst(explode('-', $com)[0]);
            $controllerName = '\LARAVEL\Controllers\Admin\\' . $controllerAlias . 'Controller';
            $controller = new ($controllerName);
            if ($act == 'delete' || $act == 'save'){
                deleteOldThumbnails();;
            }
            if ($act == 'add') { $act = 'edit'; }
            $man = (!empty($com)) ? explode('-', $com) : '';
            $method = $com === 'product-crawler'
                ? $act
                : $act . (!empty($man[1]) ? ucfirst($man[1]) : '');
       
            return $controller->$method($com, $act, $type, $request);
        }
    }
}
