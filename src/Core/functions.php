<?php
use LARAVEL\Core\Routing\LARAVELRouter;

use LARAVEL\Core\Support\Facades\Auth;

use Pecee\Http\Url;

use Pecee\Http\Response;

use Pecee\Http\Request as BaseRequest;

function url(?string $name = null, $parameters = null, ?array $getParams = null): string

{

    if (request()->segment(1) !== 'admin' || (count(config('app.langs')) > 1 && config('app.langconfig') === 'link' && $name == 'slugweb')) {

        $parameters['language'] = ((session()->get('locale') ?: config('app.lang_default')));
    }

    $baseUrl = rtrim(LARAVELRouter::router()->getUrl($name, $parameters, $getParams), '/');

    $baseUrl = !(empty($baseUrl)) ? $baseUrl : '/';

    return str_replace('/?', '?', $baseUrl);
}

function response(): Response

{

    return LARAVELRouter::response();
}

function PeceeRequest(): BaseRequest

{

    return LARAVELRouter::request();
}

function input($index = null, $defaultValue = null, ...$methods)

{

    if ($index !== null) {

        return PeceeRequest()->getInputHandler()->value($index, $defaultValue, ...$methods);
    }

    return PeceeRequest()->getInputHandler();
}

if (!function_exists('oldvalue')) {

    function oldvalue($key)
    {

        return (!empty($key)) ? \LARAVEL\Core\Support\Facades\Flash::get($key) : '';
    }
}

if (!function_exists('getFlashError')) {

    function getFlashError($key)

    {

        if (session()->hasError($key)) {

            $value = session()->getError('flashError.' . $key);

            //session()->unset('flashError.'.$key);

            return true;
        }

        return false;
    }
}

if (!function_exists('isPermissions')) {

    function isPermissions($permission): bool

    {



        if (!config('type.users.permission')) return true;

        if (\LARAVEL\Core\Support\Facades\Auth::guard('admin')->user()->hasRole('Admin')) return true;

        $permissions = \LARAVEL\Core\Support\Facades\Auth::guard('admin')->user()->roles()?->first()?->permissions()->pluck('name')->toArray();

        if (in_array($permission, $permissions ?? [])) return true;

        return false;
    }
}

function checkAdminLogin()
{

    return \LARAVEL\Core\Support\Facades\Auth::guard('admin')->check();
}

if (! function_exists('config')) {

    /**

     * Get config setting

     *

     * @param string $key

     * @param string $default

     *

     * @return mixed

     */

    function config($key = null, $default = null)

    {



        if (is_null($key)) {

            return app()->make(__FUNCTION__);
        }

        if (is_array($key)) {

            return app()->make(__FUNCTION__)->set($key);
        }

        return app()->make(__FUNCTION__)->get($key, $default);
    }
}

function redirect(string $url, ?int $code = null): void
{
    if ($code !== null) {

        response()->httpCode($code);
    }

    \LARAVEL\Facade\EventHandler::dispatch(\LARAVEL\Core\Routing\EventHandler::EVENT_FINISH, ['url' => $url, 'code' => $code]);
    response()->redirect($url);
}

function csrf_token(): ?string

{

    $baseVerifier = LARAVELRouter::router()->getCsrfVerifier();

    if ($baseVerifier !== null) {

        return $baseVerifier->getTokenProvider()->getToken();
    }

    return null;
}

if (!function_exists('session')) {

    /**

     * Working on session

     *

     * @return \LARAVEL\Core\Session\Session

     */

    function session(): \LARAVEL\Core\Session\Session

    {

        return app()->make(__FUNCTION__);
    }
}

if (!function_exists('base_path')) {

    /**

     * Get full path from base

     *

     * @param string $path

     *

     * @return string

     */

    function base_path(string $path = ''): string

    {

        return app()->basePath() . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }
}

if (!function_exists('base_path_src')) {

    /**

     * Get full path from base

     *

     * @param string $path

     *

     * @return string

     */

    function base_path_src(string $path = ''): string

    {

        return app()->basePathSrc() . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }
}

if (!function_exists('objectToArray')) {

    function objectToArray(\ArrayObject $inputs): array

    {

        $array = [];

        foreach ($inputs as $object) {

            $array[] = get_object_vars($object);
        }

        return $array;
    }
}

if (!function_exists('snake_case')) {

    function snake_case(string $string)

    {

        $result = "";

        for ($i = 0; $i < strlen($string); $i++) {

            if (ctype_upper($string[$i])) {

                $result .= $i === 0 ? strtolower($string[$i]) : '_' . strtolower($string[$i]);
            } else {

                $result .= strtolower($string[$i]);
            }
        }

        return $result;
    }
}

if (!function_exists('class_name_only')) {

    /**

     * Get class name only

     *

     * @param string $class

     */

    function class_name_only(string $class): string

    {

        $explode = explode('\\', $class);



        return end(

            $explode

        );
    }
}

if (!function_exists('func')) {

    function func()
    {

        return \LARAVEL\Core\Support\Facades\Func::class;
    }
}

if (!function_exists('request')) {

    /**

     * Get an instance of the current request or an input item from the request.

     *

     * @param  array|string|null  $key

     * @param  mixed  $default

     * @return mixed|\Illuminate\Http\Request|string|array|null

     */

    function request($key = null, $default = null)

    {

        if (is_null($key)) {

            return app('request');
        }

        if (is_array($key)) {

            return app('request')->only($key);
        }

        $value = app('request')->__get($key);

        return is_null($value) ? value($default) : $value;
    }
}

if (!function_exists('agent')) {

    function agent()
    {

        return app('agent');
    }
}

if (!function_exists('view')) {

    function view($view = '',  $data = [])

    {

        return $view ? app()->make('view')->view($view, $data) : app()->make('view');
    }
}

if (!function_exists('app')) {

    function app(string $entity = "")

    {

        if (empty($entity)) {

            return \Illuminate\Container\Container::getInstance();
        }

        return \Illuminate\Container\Container::getInstance()->make($entity);
    }
}

if (!function_exists('getCurrentPath')) {

    function getCurrentPath()

    {

        $scheme = request()->getScheme();

        $host = request()->getHost();

        $url = request()->url();

        return $url;
    }
}

if (!function_exists('trans')) {

    function trans(string $key, array $replace = [], string|null $locale = null, bool $strict = false): string|array|null

    {

        return app()->make('translator')->get($key, $replace, $locale) ?: ($strict ? null : $key);
    }
}

if (!function_exists('__')) {

    function __(string $key, array $replace = [], string|null $locale = null, bool $strict = false): string|array|null

    {

        return trans(...func_get_args());
    }
}

if (!function_exists('upload_path')) {

    /**

     * Return upload path

     *

     * @param string $path

     *

     * @return string

     */

    function upload_path($path = ''): string

    {

        return app('path.upload') . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }
}

if (!function_exists('upload_path_photo')) {

    /**

     * Return upload path

     *

     * @param string $path

     *

     * @return string

     */

    function upload_path_photo($path = '', $name = ''): string

    {

        return app('path.upload') . ($path ? DIRECTORY_SEPARATOR . $path : $path) . ($name ? ('/' . $name) : '');
    }
}

if (!function_exists('cache_path')) {

    /**

     * Return storage path

     *

     * @param string $path

     *

     * @return string

     */

    function cache_path($path = ''): string

    {

        return app('path.cache') . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }
}

if (!function_exists('config_path')) {

    /**

     * Return storage path

     *

     * @param string $path

     *

     * @return string

     */

    function config_path($path = ''): string

    {

        return app('path.config') . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }
}

if (!function_exists('router_path')) {

    /**

     * Return storage path

     *

     * @param string $path

     *

     * @return string

     */

    function router_path($path = ''): string

    {

        return app('path.route') . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }
}

if (! function_exists('seo')) {

    function seo()
    {

        return \LARAVEL\Helpers\Seo::getInstance();
    }
}

if (! function_exists('cssminify')) {

    function cssminify()
    {

        return \LARAVEL\Helpers\CssMinify::getInstance();
    }
}

if (! function_exists('jsminify')) {

    function jsminify()
    {

        return \LARAVEL\Helpers\JsMinify::getInstance();
    }
}

if (! function_exists('assets_photo')) {
    function assets_photo($path, $size, $photo, $type = '')
    {
        $ext = (!empty($type)) ? '.webp' : '';
        return assets() . (!$type ? '' : ($type . '/' . $size . '/')) . 'upload/' . $path . '/' . $photo . $ext;
    }
}

if (! function_exists('thumbs')) {

    function thumbs($path = '')
    {

        return request()->root() . '/' . $path;
    }
}

if (! function_exists('assets')) {

    function assets($path = '')
    {

        return request()->root() . '/' . $path;
    }
}

if (! function_exists('public_path')) {

    /**

     * Get the path to the public folder.

     *

     * @param  string  $path

     * @return string

     */

    function public_path($path = '')

    {

        return app()->publicPath($path);
    }
}

if (! function_exists('view_path')) {

    function view_path()

    {

        return app()->baseViewPath();
    }
}

if (! function_exists('thumb_path')) {

    function thumb_path()

    {

        return app()->getThumbPath();
    }
}

if (! function_exists('watermark_path')) {

    function watermark_path()

    {

        return app()->getWatermarkPath();
    }
}

if (!function_exists('upload')) {

    function upload($path = '', $file = '', $delete = false)
    {

        if ($delete) {

            return base_path('upload/' . $path . '/' . $file);
        }

        return config('app.asset') . 'upload/' . $path . '/' . $file;
    }
}

if (!function_exists('scandirFile')) {

    function scandirFile($path = '', $int = 0)

    {

        return  array_values(

            array_diff(scandir($path, $int), ['..', '.']),

        );
    }
}

if (!function_exists('device')) {

    function device()
    {

        if (agent()->isMobile() || agent()->isTablet()) {

            return 'mobile';
        } else {

            return 'destop';
        }
    }
}

function deleteOldThumbnails($path = '')

{

    if (!empty($path)) {

        $directoriesToCheck = [

            app()->basePath($path),

        ];
    } else {

        $directoriesToCheck = [

            app()->basePath('thumbs'),

            app()->basePath('watermarks'),

            app()->basePath('caches/@')

        ];
    }

    foreach ([app()->basePath('compiled'), app()->basePath('caches')] as $directory) {
        if (File::exists($directory) && File::isDirectory($directory)) {

            $files = File::files($directory);

            foreach ($files as $file) File::delete($file);
        }
    }

    foreach ($directoriesToCheck as $directory) {

        if (File::exists($directory) && File::isDirectory($directory)) {

            $subDirectories = File::directories($directory);

            foreach ($subDirectories as $subDirectory) File::deleteDirectory($subDirectory);
        }
    }
}

if (!function_exists('items_in_folder')) {

    /**

     * Get all items in folder

     *

     * @param string $folder

     * @param bool $included

     *

     * @return array

     */

    function items_in_folder(string $folder, bool $included = true): array

    {

        $dir = new \RecursiveDirectoryIterator(

            $folder,

            \FilesystemIterator::SKIP_DOTS

        );

        $iterators = new \RecursiveIteratorIterator(

            $dir,

            \RecursiveIteratorIterator::SELF_FIRST

        );

        $items = [];

        foreach ($iterators as $file_info) {

            if (

                $file_info->isFile()

                && $file_info !== basename(__FILE__)

                && $file_info->getFilename() != '.gitignore'

            ) {

                $path = !empty($iterators->getSubPath())

                    ? $iterators->getSubPath() . DIRECTORY_SEPARATOR . $file_info->getFilename()

                    : $file_info->getFilename();

                $items[] = ($included ? $folder . DIRECTORY_SEPARATOR : '') . $path;
            }
        }



        return $items;
    }
}

function execWriteConfigCache(): void

{

    if (!\LARAVEL\Core\Support\Facades\File::exists(cache_path('config.php'))) {

        \LARAVEL\Core\Support\Facades\File::delete(cache_path('config.php'));
    }

    $array_config_type = ['type-products', 'type-news', 'type-photo', 'type-static', 'type-newsletters', 'type-tags', 'config'];

    $cachePath = cache_path();

    $configPath = config_path();

    $items = items_in_folder($configPath);

    if (!is_dir($cachePath)) mkdir($cachePath, 0755, true);

    $cacheFile = $cachePath . '/config.php';

    $myfile = fopen($cacheFile, "w");

    if (!$myfile) die("Unable to open file!");

    fwrite($myfile, "<?php\n");

    fwrite($myfile, "return [\n");



    foreach ($items as $file) {

        $filename = str_replace('.php', '', str_replace($configPath . DIRECTORY_SEPARATOR, '', $file));

        if (!in_array($filename, $array_config_type)) {

            fwrite($myfile, "'{$filename}' => array(\n");

            $config = include $file;

            $file = str_replace($configPath . DIRECTORY_SEPARATOR, '', $file);

            if (is_array($config)) {

                foreach ($config as $key => $value) {

                    if (is_array($value)) {

                        _handleArrayConfig($key, $myfile, $value);
                    } else {

                        fwrite($myfile, "'$key' => '{$value}',\n");
                    }
                }
            }

            fwrite($myfile, "    ),\n");
        }
    }

    fwrite($myfile, "];");

    fclose($myfile);
}

function _handleArrayConfig(string $key, $myfile, array $values): void

{

    fwrite($myfile, "'{$key}' => array(\n");

    foreach ($values as $k => $v) {

        if (is_array($v)) {

            _handleArrayConfig($k, $myfile, $v);
        } else {

            fwrite($myfile, "        '{$k}' => '{$v}',\n");
        }
    }

    fwrite($myfile, "    ),\n");
}

function createRouterCache()

{

    if (!\LARAVEL\Core\Support\Facades\File::exists(cache_path('router.php'))) {

        \LARAVEL\Core\Support\Facades\File::delete(cache_path('router.php'));
    }

    $listRouter = ['admin', 'api', 'web'];

    $routerPath = router_path();

    $cachePath = cache_path();

    $cacheFile = $cachePath . '/router.php';

    $myfile = fopen($cacheFile, "w");

    if (!$myfile) {

        die("Unable to open file!");
    }

    $cacheContent = "<?php\n";

    $cacheContent .= "use LARAVEL\Core\Routing\LARAVELRouter;\n";

    foreach ($listRouter as $file) {

        if (file_exists(router_path($file . '.php'))) {

            $routerContent = file_get_contents(router_path($file . '.php'));

            $content = preg_replace('/<\?php/', '', $routerContent);

            $content = preg_replace('/use\s+LARAVEL\\\\Core\\\\Routing\\\\LARAVELRouter\s*;/', '', $content);

            $content = preg_replace('/^\h*\v+/m', '', $content);

            $cacheContent .= $content;
        }
    }

    file_put_contents($cacheFile, $cacheContent);
}
