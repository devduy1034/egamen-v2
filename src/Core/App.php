<?php
namespace LARAVEL\Core;

use LARAVEL\Core\Routing\LARAVELRouter;

use Pecee\Http\Middleware\Exceptions\TokenMismatchException;
use Pecee\SimpleRouter\Event\EventArgument;
use Pecee\SimpleRouter\Exceptions\HttpException;
use Pecee\SimpleRouter\Exceptions\NotFoundHttpException;
use Pecee\SimpleRouter\Route\IGroupRoute;
use Pecee\SimpleRouter\Route\ILoadableRoute;
use Illuminate\Container\Container;
use Dotenv\Dotenv;
use Dotenv\Repository\RepositoryBuilder;
use Dotenv\Repository\Adapter\PutenvAdapter;
use ErrorException;

class App
{
    use Singleton;
    private $routePaths = array();
    private $d;
    private $arguments = [];
    private $match;
    protected $aliases = [];
    private Container $container;
    public function __construct(Container $container)
    {
        $this->container = $container;
        if (file_exists(base_path('.env'))) {
            Dotenv::create(
                RepositoryBuilder::createWithNoAdapters()->addAdapter(PutenvAdapter::class)->immutable()->make(),
                base_path()
            )->safeLoad();
        }
        $this->registerConfigProvider();
        new AliasLoader();
    }
    private function registerConfigProvider(): void
    {
        $this->container->singleton('config', function () {
            return new \LARAVEL\Core\Config\Config();
        });
    }
    private function loadConfiguration(): void
    {

        if (file_exists(cache_path('config.php'))) {
            $configArray = require cache_path('config.php');
            $this->container->make('config')->setConfigCache($configArray);
        } else {
            $configArray = require config_path('config.php');
            $this->container->make('config')->setConfigCache($configArray);
        }

        $this->setDebug();

        $providers = [
            \LARAVEL\Core\Request\RequestServiceProvider::class,
            \LARAVEL\Providers\EventHandlerServiceProvider::class,
            \LARAVEL\Providers\EventServiceProvider::class,
            \LARAVEL\Helpers\Clockwork\ClockworkServiceProvider::class,
            \LARAVEL\Providers\AppServiceProvider::class
        ];

        if (!empty($providers)) {
            $initializedProviders = [];
            foreach ($providers as $provider) {
                $providerInstance = new $provider($this->container);
                $providerInstance->register();
                $initializedProviders[] = $providerInstance;
            }
            foreach ($initializedProviders as $providerInstance) {
                if (method_exists($providerInstance, 'boot')) {
                    $providerInstance->boot();
                }
            }
        }

        LARAVELRouter::addEventHandler(app('event_handler'));

        \LARAVEL\Facade\EventHandler::addEventListener(\LARAVEL\Core\Routing\EventHandler::EVENT_INIT, function () {
         
            if (app('clockwork.support')->isCollectingData()) {
               
                clock()->event('Router load routes')->color('grey')->end();
                clock()->event('Router Init')->color('grey')->begin();
                clock()->event('App')->color('purple')->begin();
    
                clock()->event('Router load routes')->color('grey')->begin();
    
                \LARAVEL\Facade\EventHandler::addEventListener(\LARAVEL\Core\Routing\EventHandler::EVENT_RENDER_ROUTE, function () {
                  
                    clock()->event('Router Run Route')->color('grey')->begin();
                    clock()->event('Controller')->color('green')->begin();
                });
    
                \LARAVEL\Facade\EventHandler::addEventListener(\LARAVEL\Core\Routing\EventHandler::EVENT_FINISH, function () {
                    clock()->event('App')->end();
                    clock()->event('Controller')->end();
                    clock()->event('Router Run Route')->color('grey')->end();
                    clock()->resolveRequest();
                    app()['clockwork.support']->recordRequest();
                });

                app('clockwork.support')->addDataSources()->listenToEvents();
                app('clockwork.support')->processRequest(\request(), \response());
            }

            \LARAVEL\Facade\EventHandler::addEventListener(\LARAVEL\Core\Routing\EventHandler::EVENT_MATCH_ROUTE, function () {
                clock()->event('Router Init')->color('grey')->end();
                clock()->event('Router Match')->color('grey')->begin();
            });
    
            \LARAVEL\Facade\EventHandler::addEventListener(\LARAVEL\Core\Routing\EventHandler::EVENT_RENDER_MIDDLEWARES, function () {
                clock()->event('Router Match')->color('grey')->end();
                clock()->event('Router Render Middlewares')->color('grey')->begin();
            });
        });

    }
    public function registerServiceProvider(): void
    {
        clock()->event("registerServiceProvider")->color('grey')->begin();
        $providers = \LARAVEL\Core\ServiceProvider::defaultProviders()
            ->merge(config('app.providers'))
            ->toArray();
        if (!empty($providers)) {
            $initializedProviders = [];
            foreach ($providers as $provider) {
                clock()->event("regist: {$provider}")->color('grey')->begin();
                $providerInstance = new $provider($this->container);
                $providerInstance->register();
                $initializedProviders[] = $providerInstance;

                clock()->event("regist: {$provider}")->end();
            }

            foreach ($initializedProviders as $providerInstance) {
                $class_name = $providerInstance::class;
                clock()->event("boot: {$class_name}")->color('grey')->begin();
                if (method_exists($providerInstance, 'boot')) {
                    $providerInstance->boot();
                }
                clock()->event("boot: {$class_name}")->end();
            }
        }
        clock()->event("registerServiceProvider")->end();
    }

    // private function generateIPFromApi($ip):string{
    //     $curl = curl_init();
    //     @curl_setopt_array($curl, array(
    //     CURLOPT_URL => 'https://api.LARAVEL.vn/private_api/generateIP',
    //     CURLOPT_RETURNTRANSFER => true,
    //     CURLOPT_ENCODING => '',
    //     CURLOPT_MAXREDIRS => 10,
    //     CURLOPT_TIMEOUT => 0,
    //     CURLOPT_FOLLOWLOCATION => true,
    //     CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    //     CURLOPT_CUSTOMREQUEST => 'POST',
    //     CURLOPT_POSTFIELDS => ['ip' => $ip],
    //     CURLOPT_HTTPHEADER => array(
    //         'Authorization: Bearer LARAVEL^^[]#!@123'
    //     ),
    //     CURLOPT_SSL_VERIFYPEER => false, 
    //     CURLOPT_SSL_VERIFYHOST => false,
    //     ));
    //     $response = @curl_exec($curl);
    //     if (curl_errno($curl)) {
    //         $error_msg = curl_error($curl);
    //     }
    //     curl_close($curl);
    //     if (!isset($error_msg)) {
    //         $arr_response = json_decode($response, true);
    //         if (json_last_error() === JSON_ERROR_NONE) {
    //             if (isset($arr_response['success']) && $arr_response['success']) {
    //                 $ipList = $arr_response['data'];
    //                 return $ipList;
    //             } 
    //         }
    //     }
    //         return '';
    // }
    // private function generateKeyFromApi($dbName, $domain,$key_old):void{
    //     $curl = curl_init();
    //     @curl_setopt_array($curl, array(
    //     CURLOPT_URL => 'https://api.LARAVEL.vn/private_api/GenerateKey',
    //     CURLOPT_RETURNTRANSFER => true,
    //     CURLOPT_ENCODING => '',
    //     CURLOPT_MAXREDIRS => 10,
    //     CURLOPT_TIMEOUT => 0,
    //     CURLOPT_FOLLOWLOCATION => true,
    //     CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    //     CURLOPT_CUSTOMREQUEST => 'POST',
    //     CURLOPT_POSTFIELDS => ['dbname' => $dbName, 'domain' => $domain,'code' => $key_old],
    //     CURLOPT_HTTPHEADER => [
    //             'Authorization: Bearer LARAVEL^^[]#!@123'
    //         ],
    //     CURLOPT_SSL_VERIFYPEER => false, 
    //     CURLOPT_SSL_VERIFYHOST => false,
    //     ));
    //     @curl_exec($curl);
    //     curl_close($curl);
    // }
    private function isLocalIp($ip):bool {
        $localDomains = ['localhost', '127.0.0.1', '::1'];
        $localIpRanges = [
            '10.0.0.0|10.255.255.255',
            '172.16.0.0|172.31.255.255',
            '192.168.0.0|192.168.255.255',
        ];
        if (in_array($ip, $localDomains)) {
            return true;
        }
        foreach ($localIpRanges as $range) {
            list($start, $end) = explode('|', $range);
            if (ip2long($ip) >= ip2long($start) && ip2long($ip) <= ip2long($end)) {
                return true;
            }
        }
        return false;
    }
    // private function checkIPHost():void{
    //     $ipListFile = __DIR__.'/Database/Data.dat';
    //     $serverIp = gethostbyname(gethostname());
    // if (!$this->isLocalIp($serverIp)) {
    //         if (file_exists($ipListFile)) {
    //             $ipList = trim(file_get_contents($ipListFile));
    //         }else{
    //             $ipList = trim($this->generateIPFromApi($serverIp));
    //             file_put_contents($ipListFile, $ipList);   
    //         }
    //         $ipaddress_encode = md5($serverIp.'!sf@D');
    //         if ($ipList=='' || $ipaddress_encode!=$ipList) {
    //             $ipList_new = $this->generateIPFromApi($serverIp);
    //             if(!empty($ipList_new)) file_put_contents($ipListFile, $ipList_new);  
    //             die('Config Error.!');
    //         }
    //     }
    // }
    // private function checkDomain():void{
    //     $key = config('app.random_key');
    //     $db = config('database.connections.mysql');
    //     $domain = (isset($_SERVER["HTTP_HOST"]))?$_SERVER["HTTP_HOST"]:'localhost';
    //     $domain = preg_replace('/:\d+$/', '', $domain);
    //     $domain = preg_replace('/^http:\/\/www\.|^http:\/\/|^www\.|\/$/', '', $domain);
    //     $salt1 = '$$#*d*934FD546';
    //     $salt2 = '$$#fdsDFDsfd84348fDF8f*d*';
    //     $hash = md5($salt1.$db['database'].$salt2);
    //     $localDomains = ['localhost', '127.0.0.1', '::1'];
    //     $mainDomains = ['LARAVELvietnam.com.vn', 'LARAVELvietnam.org'];
    //     $isDemo = false;
    //     foreach ($mainDomains as $mainDomain) {
    //         if (substr($domain, -strlen($mainDomain)) === $mainDomain && strlen($domain) > strlen($mainDomain)) {
    //             $isDemo = true;
    //             break;
    //         }
    //     }
    //     if (!in_array($domain, $localDomains) && $isDemo ==false) {
    //             if ($key!= $hash) {
    //                 $this->generateKeyFromApi($db['database'],$domain,$key);
    //                 die('Config Error..!');
    //             }
    //     }
    // }
    private function loadRoutes(): void
    {
        $basePath = substr(config('app.site_path'), 0, -1);
        LARAVELRouter::csrfVerifier(new \LARAVEL\Middlewares\CsrfVerifier());
        LARAVELRouter::setDefaultNamespace('\LARAVEL\Controllers');
        LARAVELRouter::enableMultiRouteRendering(false);
        LARAVELRouter::group(['prefix' => substr(config('app.site_path'), 0, -1)], function () {

            if (app('clockwork.support')->isEnabled()) {
                app('clockwork.support')->registerRoutes();
            }

            LARAVELRouter::get('/thumbs/{thumbsize}/{path}/{folder}/{imageUrl}', 'InterventionController@thumb')->where(['imageUrl' => '.*'])->name('thumb');
            LARAVELRouter::get('/watermarks/{thumbsize}/{path}/{folder}/{imageUrl}', 'InterventionController@watermark')->where(['imageUrl' => '.*'])->name('watermark');
            LARAVELRouter::get('/sitemap.xml', '\LARAVEL\Controllers\Web\SitemapController@index')->name('sitemap');
            LARAVELRouter::get('/factory-data', '\LARAVEL\Controllers\Admin\FakeDataController@index')->name('factory-data');

            LARAVELRouter::get('/LARAVEL-create-cache', function () {
                execWriteConfigCache();
                createRouterCache();
                transfer('Tạo cache thành công !', 1, PeceeRequest()->getHeader('http_referer'));
            });
            LARAVELRouter::get('/not-found', function () {
                view('error.notfound');
            });
            LARAVELRouter::get('/forbidden', function () {
                view('error.forbidden');
            });
        });

        LARAVELRouter::error(function (\Pecee\Http\Request $request, \Exception $exception) {
            switch ($exception->getCode()) {
                case 404:
                    view('error.notfound');
                    LARAVELRouter::response()->httpCode(404);
                    exit();
                case 403:
                    view('error.forbidden');
                    LARAVELRouter::response()->httpCode(403);
                    exit();
            }
        });
        if (file_exists(cache_path('router.php'))) {
            include_once cache_path('router.php');
        } else {
            $routerArray = ['admin', 'api', 'web'];
            foreach ($routerArray as $file) {
                if (file_exists(router_path($file . '.php'))) {
                    include_once router_path($file . '.php');
                }
            }
        }
    }

    protected function setDebug(): void
    {
        if (config('app.environment') == "dev") {
            error_reporting(E_ALL);
            ini_set('display_errors', 1);
            \Spatie\Ignition\Ignition::make()->register();
        } else {
            error_reporting(0);
            ini_set('display_errors', 0);
            set_error_handler(function ($errno, $errstr, $errfile, $errline) {
                if (!(error_reporting() & $errno)) {
                    return;
                }
                throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
            });
            set_exception_handler(function ($exception) {
                http_response_code(500);
                echo " Hệ thống gặp lỗi nghiêm trọng, vui lòng liên hệ quản trị website!";
                error_log($exception);
            });
        }
    }
    protected function getLinkNoLang(): void
    {
        $configLink = array_merge(
            config('app.linkdefault') ?? [],
            ['thumbs', 'watermarks', 'admin', 'sitemap.xml', 'not-found', 'forbidden']
        );
        $langs = config('app.langs');
        if (count($langs) <= 1) return;
        $langConfig = config('app.langconfig');
        if (empty($langConfig) || $langConfig !== 'link') return;
        $firstSegment = request()->segment(1);
        if (in_array($firstSegment, $configLink) || array_key_exists($firstSegment, $langs)) return;
        response()->redirect(config('app.asset') . config('app.lang_default') . '/');
    }




    /**
     * @throws HttpException
     * @throws NotFoundHttpException
     * @throws TokenMismatchException
     */
    public function run()
    {
        $this->loadConfiguration();
        $this->registerServiceProvider();
        $this->loadRoutes();
        LARAVELRouter::start();
        // @$this->checkIPHost();
        // @$this->checkDomain();
        $this->getLinkNoLang();
        LARAVELRouter::runRouter();
    }
}
