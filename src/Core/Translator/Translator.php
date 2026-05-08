<?php
namespace LARAVEL\Core\Translator;
use LARAVEL\Core\Singleton;
use LARAVEL\Core\Support\Str;
use Illuminate\Support\Arr;
class Translator
{
    use Singleton;
    const DEFAULT_MESSAGE_FILE = "messages";
    public string $locale='vi';
    public ?string $directory = '';
    protected array $loaded = [];
    protected $ext = 'json';
    protected $loadedFile = [];
    public function __construct(string $locale = null)
    {
        self::setInstance($this);
        $locale =$locale?? ((session()->get('locale')?: 'vi'));
        
        $this->setLocale($locale);
    }
    public function getLocale(): string
    {
        return $this->locale;
    }
    public function setLocale(string $locale): static
    {
        $this->locale = $locale;
        return $this;
    }
    public function get(string $key, array $replace = [], string|null $locale = null): string|array
    {
        $locale ? ($this->locale = $locale) : $this->locale;
        $this->load($this->messageFile(Str::before($key, ".")));

        return $this->replaceAttribute(Arr::get($this->loaded, "{$this->locale}.{$key}", ''), $replace)
            ?: $this->getFromDefaultFile($key, $replace, $this->locale);
    }

    /**
     * Load the specified language file.
     */
    public function load(string $file, string $prefix = ''): bool
    {
        if (in_array($file, $this->loadedFile)) {
            return true;
        }
        $prefix = $prefix ?: Str::before(
            Str::replace(DIRECTORY_SEPARATOR, ".", Str::after($file, $this->locale . DIRECTORY_SEPARATOR)),
            ".{$this->ext}"
        );
        $extFile = Str::after(
            Str::replace(DIRECTORY_SEPARATOR, ".", Str::after($file, $this->locale . DIRECTORY_SEPARATOR)),
            "."
        );
        if($extFile === 'php'){
            if (file_exists($file) && is_array($data = @include $file)) {
                $this->loadedFile[] = $file;
                $this->loaded[$this->locale][$prefix] = $data;
                return true;
            }
        }
        if($extFile === 'json'){
            $jsonString = file_get_contents($file);
            $array = json_decode($jsonString, true);
            if ($array !== null) {
                $this->loadedFile[] = $file;
                $this->loaded[$this->locale][$prefix] = $array;
                return true;
            }
        }
        return false;
    }
    public function directory(string $directory): static
    {
        $this->directory = $directory;
        return $this;
    }
    public function getDirectory(): string
    {
        return $this->directory ?: base_path('src/Lang');
    }
    private function messageFile(string $fileName): string
    {
        return implode(DIRECTORY_SEPARATOR, [
                $this->getDirectory(),
                $this->locale,
                $fileName,
            ]) . ".{$this->ext}";
    }
    private function replaceAttribute(string $message=null, array $replace = []): string
    {
        if (Str::isEmpty($message, false)) {
            return '';
        }

        foreach ($replace as $searchKey => $replaceValue) {
            $message = Str::replace(":{$searchKey}", $replaceValue, $message);
            if (Str::contains($message, ":" . ucfirst($searchKey))) {
                $message = Str::replace(
                    ":" . ucfirst($searchKey),
                    ucfirst($replaceValue),
                    $message);
            }
        }

        return $message;
    }
    private function getFromDefaultFile(string $key, array $replace = [], string|null $locale = null): string
    {
        return $this->replaceAttribute(
            Arr::get(
                $this->loaded,
                sprintf("{$locale}.%s.{$key}", static::DEFAULT_MESSAGE_FILE)
            ),
            $replace
        );
    }
}