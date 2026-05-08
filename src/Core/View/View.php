<?php
namespace LARAVEL\Core\View;

// use LARAVEL\Controllers\Admin\AllController;
// use LARAVEL\Core\Singleton;
// use RecursiveDirectoryIterator;
// use RecursiveIteratorIterator;
// use Illuminate\Support\Str;
use LARAVEL\Core\Routing\LARAVELRouter;

class View extends BladeOne
{
    use BladeOneCache;
    public string $viewPath;
    public string $viewPathLayout;
    public function __construct()
    {
        parent::__construct();

        $this->setPathView();
    }
    protected function bootstrap(): void
    {
        $this->templatePath = [$this->viewPath, $this->viewPathLayout];
        $this->compiledPath = config('view.compiled');
        $this->setBaseUrl(config('app.asset') . config('view.asset'));
    }
    protected function setPathView(): void
    {
        \LARAVEL\Facade\EventHandler::addEventListener(\LARAVEL\Core\Routing\EventHandler::EVENT_RENDER_ROUTE, function () {
            clock()->event('Router Render Middlewares')->color('grey')->end();

            $LARAVEL_router = LARAVELRouter::router()->getCurrentProcessingRoute();

            if ($LARAVEL_router->getName() == 'watermark' || $LARAVEL_router->getName() == 'thumb') {
                return;
            }
            $composer = '';
            $view_type  = '';

            if ($LARAVEL_router->getNamespace() == '\LARAVEL\Controllers\Admin') {
                $this->mode = config('view.mode.admin');
                $view_type = 'admin';
                $composer = config('view.composer_admin');
                $this->viewPath = base_path('src/Views/admin');
            } else if (preg_match('/amp/i', $LARAVEL_router->getUrl())) {
                $this->mode = config('view.mode.web');
                $view_type = 'amp';
                $composer = config('view.composer_amp');
                $this->viewPath = config('view.view_amp');
            } else {
                $this->mode = config('view.mode.web');
                $view_type = 'web';
                $composer = config('view.composer');
                if (agent()->isMobile() || agent()->isTablet()) {
                    $this->viewPath = config('view.view_mobile');
                } else {
                    $this->viewPath = config('view.view_templates');
                }
            }
            try {
                clock()->event('All Controller ' . $view_type)->color('green')->begin();
                (new $composer())->composer($this);
                clock()->event('All Controller ' . $view_type)->end();
            } catch (\Exception $e) {
                throw $e;
            }
            $this->viewPathLayout = ROOT_PATH . '/src/Views/layout';
            $this->bootstrap();
        });

        // $path = request()->segment(1);
        // $path_admin_array = explode('/', Str::after(config('app.admin_prefix'), '/'));
        // $path_admin = end($path_admin_array);
        // if ($path == $path_admin) {
        //     $this->mode = config('view.mode.admin');
        //     if (!empty(config('view.composer_admin'))) {
        //         $composer = config('view.composer_admin');
        //         $this->composer('*', $composer);
        //     }
        //     $this->viewPath = base_path('src/Views/admin');
        // } else if ($path == 'amp') {
        //     $this->mode = config('view.mode.web');
        //     if (!empty(config('view.composer_amp'))) {
        //         $composer = config('view.composer_amp');
        //         $this->composer('*', $composer);
        //     }
        //     $this->viewPath = config('view.view_amp');
        // } else {
        //     $this->mode = config('view.mode.web');
        //     if (!empty(config('view.composer'))) {
        //         $composer = config('view.composer');
        //         // $this->composer('*', $composer);
        //         \LARAVEL\Facade\EventHandler::addEventListener(EventHandler::EVENT_RENDER_ROUTE, function () use ($composer) {
        //             clock()->event('All Controller')->color('green')->begin();
        //             (new $composer())->composer($this);
        //             clock()->event('All Controller')->end();
        //         });
        //     }
        //     if (agent()->isMobile() || agent()->isTablet()) {
        //         $this->viewPath = config('view.view_mobile');
        //     } else {
        //         $this->viewPath = config('view.view_templates');
        //     }
        // }
        // $this->viewPathLayout = ROOT_PATH . '/src/Views/layout';
    }

    /**
     * @throws \Exception
     */
    public function view($path, $data = []): void
    {
        \clock()?->event('View')?->color('blue')?->begin();
        $result = $this->run($path, $data);
        \clock()?->event('View')?->end();
        echo $result;
    }

    public function render($path, $data = [])
    {
        return $this->run($path, $data);
    }
}
