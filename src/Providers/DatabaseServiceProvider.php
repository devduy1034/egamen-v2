<?php


namespace LARAVEL\Providers;
use LARAVEL\Core\ServiceProvider;
use LARAVEL\DatabaseCore\Capsule\Manager as Capsule;
use LARAVEL\DatabaseCore\Eloquent\Model;

class DatabaseServiceProvider extends ServiceProvider
{
    protected $defer = true;
    public function register(): void
    {
        $capsule = new Capsule;

        $this->app->singleton('capsule', fn() => $capsule);
    }

    public function boot(): void
    {
        $this->app['capsule']->setEventDispatcher($this->app['events']);
        $defaultConnet = config('database.default');
        $this->app['capsule']->addConnection(config('database.connections.' . $defaultConnet));
        $this->app['capsule']->setAsGlobal();
        $this->app['capsule']->bootEloquent();
        $this->app->singleton('db', fn() =>  $this->app['capsule']->connection());
        Model::setEventDispatcher($this->app['events']);

        $setting = $this->app->make('db')->table('setting')->first();
        $optionSetting = json_decode($setting->options, true);
        $AdminUrl = $optionSetting['admin_url'] ?? 'admin';
        $site_path = $this->app->make('config')->get('app.site_path');
        $this->app->make('config')->set('app.admin_prefix', $site_path . $AdminUrl);
        $this->app->make('config')->set('app.lang_default',$optionSetting['lang_default']);
    }

    public function provides()
    {
        return [
            'capsule',
            'db',
        ];
    }
}
