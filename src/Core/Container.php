<?php
namespace LARAVEL\Core;
class Container extends \Illuminate\Container\Container
{
    private string $basePath;
    protected $deferredProviders = [];
    protected $serviceProviders = [];
    public function __construct(string $basePath)
    {
        $this->basePath = $basePath;
        $this->instance('path.route', $this->getRoutePath());
        $this->instance('path.base', $this->basePath());
        $this->instance('path.views', $this->baseViewPath());
        $this->instance('path.cache', $this->getCachePath());
        $this->instance('path.config', $this->getConfigPath());
        $this->instance('path.thumb', $this->getThumbPath());
        $this->instance('path.watermark', $this->getWatermarkPath());
        $this->instance('path.upload', $this->getUploadPath());
        self::$instance = $this;
    }


    public function registerDeferredProvider($provider)
    {
        $instance = new $provider($this);
        foreach ($instance->provides() as $service) {
            $this->deferredProviders[$service] = $instance;
        }
    }

    public function registerProvider($provider)
    {
        $instance = new $provider($this);
        $instance->register();
        $this->serviceProviders[] = $instance;
    }

    public function get($abstract, $parameters = [])
    {
        if (isset($this->deferredProviders[$abstract])) {
            $provider = $this->deferredProviders[$abstract];
            unset($this->deferredProviders[$abstract]);
            $provider->register();
            $this->serviceProviders[] = $provider;
            if (method_exists($provider, 'boot')) {
                $provider->boot();
            }
        }
        return parent::get($abstract, $parameters);
    }

    public function bootProviders()
    {
        foreach ($this->serviceProviders as $provider) {
            if (method_exists($provider, 'boot')) {
                $provider->boot();
            }
        }
    }
    public static function getInstance(): Container|static
    {
        if (is_null(static::$instance)) {
            static::$instance = new static;
        }
        return static::$instance;
    }
    public function baseViewPath(): string
    {
        return $this->basePath() . DIRECTORY_SEPARATOR . 'src/Views';
    }
    public function getThumbPath(): string
    {
        return $this->basePath() . DIRECTORY_SEPARATOR . 'thumbs';
    }
    public function getWatermarkPath(): string
    {
        return $this->basePath() . DIRECTORY_SEPARATOR . 'watermarks';
    }
    private function getRoutePath(): string
    {
        return $this->basePath() . DIRECTORY_SEPARATOR . 'src/Routes';
    }
    public function basePath(string $path = ''): string
    {
        return !$path ? $this->basePath : $this->basePath . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }
    public function basePathSrc(string $path = ''): string
    {
        return (!$path ? $this->basePath : $this->basePath . ($path ? DIRECTORY_SEPARATOR . $path : $path)).'\src';
    }
    private function getUploadPath(): string
    {
        return $this->basePath() . DIRECTORY_SEPARATOR . 'upload';
    }
    private function getCachePath(): string
    {
        return $this->basePath() . DIRECTORY_SEPARATOR . 'caches';
    }
    private function getConfigPath(): string
    {
        return $this->basePath() . DIRECTORY_SEPARATOR . 'config';
    }
    public function getOS(): string
    {
        return match (PHP_OS) {
            stristr(PHP_OS, 'DAR') => 'macros',
            stristr(PHP_OS, 'WINNT') => 'windows',
            stristr(PHP_OS, 'LINUX') => 'linux',
            default => 'unknown',
        };
    }

    public function getSizePath($path=''): string {
        return (empty($path))?'':$this->formatSizeUnits($this->getDirectorySize($this->basePath($path)));
    }
    protected function getDirectorySize($dir): float {
        $size = 0;
        if ($dh = opendir($dir)) {
            while (($file = readdir($dh)) !== false) {
                if ($file != "." && $file != "..") {
                    $path = $dir . DIRECTORY_SEPARATOR . $file;
                    if (is_dir($path)) {
                        $size += $this->getDirectorySize($path);
                    } else {
                        $size += filesize($path);
                    }
                }
            }
            closedir($dh);
        }
        return $size;
    }
    protected function formatSizeUnits($bytes): string {
        if ($bytes >= 1073741824) {
            $bytes = number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            $bytes = number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            $bytes = number_format($bytes / 1024, 2) . ' KB';
        } elseif ($bytes > 1) {
            $bytes = $bytes . ' bytes';
        } elseif ($bytes == 1) {
            $bytes = $bytes . ' byte';
        } else {
            $bytes = '0 bytes';
        }
        return $bytes;
    }

    public function isWindows(): bool
    {
        return "windows" === $this->getOs();
    }
    public function isMacos(): bool
    {
        return "macros" === $this->getOs();
    }
    public function isLinux(): bool
    {
        return "linux" === $this->getOs();
    }
    public function unknownOs(): bool
    {
        return "unknown" === $this->getOs();
    }
    public function publicPath($path = ''): string
    {
        return $this->joinPaths($this->getUploadPath(), $path);
    }
    public function joinPaths($basePath, $path = ''): string
    {
        return $basePath.($path != '' ? DIRECTORY_SEPARATOR.ltrim($path, DIRECTORY_SEPARATOR) : '');
    }
    public function getSeoLang(){
        return (count($this->make('config')->get('app.slugs'))===1?$this->make('config')->get('app.seo_default'):((session()->get('locale')?: $this->make('config')->get('app.seo_default'))));
    }
}