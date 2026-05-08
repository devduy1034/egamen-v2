<?php
namespace LARAVEL\Core\Firewall;
use Illuminate\Contracts\Container\BindingResolutionException;
use LARAVEL\Core\ServiceProvider;
use LARAVEL\Core\Support\Facades\File;

class FirewallServiceProvider extends ServiceProvider
{
    /**
     * @throws BindingResolutionException
     */
    public function boot(): void{
        if(request()->segment('1') !='admin') app()->make('firewall')->run();
    }
    public function register(): void
    {
        $firewallConfigDefault['htaccess'] = ".htaccess";
        $jsonPath = public_path('upload/file/firewall.json');
        if(!File::exists($jsonPath)){
            $firewallConfigDefault = array_merge($firewallConfigDefault,config('firewall'));
        }else{
            $jsonString = file_get_contents(assets('upload/file/firewall.json'));
            $firewallConfigDefault = json_decode($jsonString, true);
        }
        $this->app->singleton('firewall', function () use ($firewallConfigDefault) {
            return new Firewall($firewallConfigDefault);
        });
    }
}