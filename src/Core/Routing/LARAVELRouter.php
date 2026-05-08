<?php
namespace LARAVEL\Core\Routing;
use LARAVEL\Core\Support\Facades\Hash;
use Pecee\Http\Url;
use Pecee\SimpleRouter\Router;
use Pecee\SimpleRouter\SimpleRouter;
use LARAVEL\Core\Support\Facades\CacheHtml;
class LARAVELRouter extends SimpleRouter
{
    public static function router(): Router
    {
        if (static::$router === null) {
            static::$router = new RouterCustom();
        }
        return static::$router;
    }
    public static function start(): void
    {
        foreach (static::router()->getRoutes() as $route) {
            static::addDefaultNamespace($route);
        }
    }
    public static function runRouter() {
        $path = request()->fullUrl().'__'.((!config('app.mobile'))?agent()->deviceType():'');
        if(!CacheHtml::checkUrlCache($path) || self::request()->getMethod()!=='get' || self::request()->isAjax() || !config('app.cache_file')) return static::router()->start();
        else self::getCache($path);
    }
    public static function getCache($path){
        if(CacheHtml::checkFile(md5($path)) && config('app.cache_file')===true){
            CacheHtml::get(md5($path));
        }else{
            ob_start();
            echo static::router()->start();
            $content = ob_get_contents();
            ob_end_clean();
            CacheHtml::set(minify_html($content),md5($path));
            echo $content;
        }
    }
}