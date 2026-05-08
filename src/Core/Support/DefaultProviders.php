<?php
namespace LARAVEL\Core\Support;

class DefaultProviders
{
    protected array $providers;
    public function __construct(?array $providers = null)
    {
        $this->providers = $providers ?: [
            \LARAVEL\Core\Session\SessionServiceProvider::class,
            \LARAVEL\Providers\DatabaseServiceProvider::class,
            \LARAVEL\Core\Hashing\HashServiceProvider::class,
            \LARAVEL\Core\Translator\TranslatorServiceProvider::class,
            \LARAVEL\Core\Config\ConfigServiceProvider::class,
            \LARAVEL\Providers\FlashServiceProvider::class,
            \LARAVEL\Core\Validator\ValidationServiceProvider::class,
            \LARAVEL\Core\Auth\AuthenticationServiceProvider::class,
            \LARAVEL\Providers\ViewServiceProvider::class,
            \LARAVEL\Core\Agent\AgentServiceProvider::class,
            \LARAVEL\Core\CacheHtml\CacheHtmlServiceProvider::class,
            \LARAVEL\Providers\BreadCrumbsServiceProvider::class,
            \LARAVEL\Providers\SeoServiceProvider::class,
            \LARAVEL\Providers\FuncServiceProvider::class,
            \LARAVEL\Core\FileSystem\FileSystemServiceProvider::class,
            \LARAVEL\Core\Statistic\StatisticServiceProvider::class,
            \LARAVEL\Core\Intervention\ImageServiceProvider::class,
            \LARAVEL\Core\Cookie\CookieServiceProvider::class,
            \LARAVEL\Core\Firewall\FirewallServiceProvider::class,
        ];
    }
    public function merge(array $providers): static
    {
        $this->providers = array_merge($this->providers, $providers);
        return new static($this->providers);
    }
    public function replace(array $replacements): static
    {
        $current = collect($this->providers);
        foreach ($replacements as $from => $to) {
            $key = $current->search($from);

            $current = is_int($key) ? $current->replace([$key => $to]) : $current;
        }
        return new static($current->values()->toArray());
    }
    public function except(array $providers): static
    {
        return new static(collect($this->providers)
            ->reject(fn ($p) => in_array($p, $providers))
            ->values()
            ->toArray());
    }
    public function toArray(): array
    {
        return $this->providers;
    }
}