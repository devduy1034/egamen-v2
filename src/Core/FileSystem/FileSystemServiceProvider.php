<?php
namespace LARAVEL\Core\FileSystem;
use Illuminate\Filesystem\FilesystemManager;
use LARAVEL\Core\ServiceProvider;
class FileSystemServiceProvider extends ServiceProvider
{
    protected $defer = true;
    public function register(): void
    {
        $this->registerNativeFilesystem();
        $this->registerFlysystem();
        $this->registerCoreContainerAliases();
    }
    protected function registerNativeFilesystem(): void
    {
        $this->app->singleton('files', function () {
            return new FileSystem;
        });
    }
    protected function registerFlysystem(): void
    {
        $this->registerManager();
        $this->app->singleton('filesystem.disk', function ($app) {
            return $app['filesystem']->disk($this->getDefaultDriver());
        });
    }
    protected function registerManager(): void
    {
        $this->app->singleton('filesystem', function ($app) {
            return new FilesystemManager($app);
        });
    }
    protected function getDefaultDriver()
    {
        return $this->app['config']['filesystems.default'];
    }
    protected function getCloudDriver()
    {
        return $this->app['config']['filesystems.cloud'];
    }
    public function provides()
    {
        return ['filesystem.disk','filesystem'];
    }
    private function registerCoreContainerAliases(): void{
        foreach ([
                     'filesystem' => [\Illuminate\Filesystem\FilesystemManager::class, \Illuminate\Contracts\Filesystem\Factory::class],
                     'filesystem.disk' => [\Illuminate\Contracts\Filesystem\Filesystem::class],
                 ] as $key => $aliases) {
            foreach ($aliases as $alias) {
                $this->app->alias($key, $alias);
            }
        }
    }
}