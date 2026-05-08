<?php


namespace LARAVEL\Helpers;

use LARAVEL\Core\Singleton;

class CssMinify
{
    use Singleton;
    private array $path = array();
    private bool $debug;
    private array $access;
    private string $cacheFile = '';
    private string $cacheLink = '';
    private bool $cacheSize = false;
    private int|float $cacheTime = 3600 * 24 * 30;
    private $file = [];

    function __construct()
    {
        $this->debug = !(config('app.cache_css'));
        $this->access = array(
            'server' => app()->make('path.base') . '/assets/',
            'asset' => assets('assets/'),
            'folder' => 'caches/'
        );
    }

    public function init($name): void
    {
        if (!$this->debug && !file_exists($this->cacheLink . $this->access['server'] . $this->access['folder'])) {
            if (!mkdir($this->cacheLink . $this->access['server'] . $this->access['folder'], 0777, true)) {
                die('Failed to create folders...');
            }
        }
        $cacheName = '';
        $cacheName = $name;
        $this->cacheFile = $this->cacheFile . $this->access['server'] . $this->access['folder'] . $cacheName . '.css';
        $this->cacheLink = $this->cacheLink . $this->access['asset'] . $this->access['folder'] . $cacheName . '.css';
        $this->cacheSize = (file_exists($this->cacheFile)) ? filesize($this->cacheFile) : 0;
    }

    public function set($path)
    {
        $this->path[] = [
            'server' => $this->access['server'] . $path,
            'asset' => $this->access['asset'] . $path
        ];
        $this->file[] = $path;
    }

    public function get()
    {
        $this->init(md5(implode(",", $this->file)));
        if (empty($this->path)) die("No files to optimize");
        return ($this->debug) ? $this->links() : $this->minify();
    }

    private function minify()
    {
        $strCss = '';
        $extension = '';

        if (!$this->cacheSize || $this->isExpire($this->cacheFile)) {
            foreach ($this->path as $path) {
                $parts = pathinfo($path['server']);
                $extension = strtolower($parts['extension']);
                if ($extension != 'css') die("Invalid file");
                $myfile = fopen($path['server'], "r") or die("Unable to open file");
                $sizefile = filesize($path['server']);
                if ($sizefile) $strCss .= $this->compress(fread($myfile, $sizefile));
                fclose($myfile);
            }

            if ($strCss) {
                $file = fopen($this->cacheFile, "w") or die("Unable to open file");
                fwrite($file, $strCss);
                fclose($file);
            }
        }

        return '<link href="' . $this->cacheLink . ((config('app.environment') == 'dev') ? ('') : '') . '" rel="stylesheet">';
    }

    private function links()
    {
        $linkCss = '';
        $extension = '';

        if ($this->cacheSize) {
            $file = fopen($this->cacheFile, "r+") or die("Unable to open file");
            ftruncate($file, 0);
            fclose($file);
        }

        foreach ($this->path as $path) {
            $parts = pathinfo($path['server']);
            $extension = strtolower($parts['extension']);
            if ($extension != 'css') die("Invalid file");
            $linkCss .= '<link href="' . $path['asset'] . '?v=' . $this->stringRandom(10) . '" rel="stylesheet">' . PHP_EOL;
        }

        return $linkCss;
    }

    private function compress($buffer)
    {
        $buffer = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $buffer);
        $buffer = str_replace(': ', ':', $buffer);
        $buffer = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $buffer);
        return $buffer;
    }

    /* String random */
    private function stringRandom($sokytu = 10)
    {
        $str = '';

        if ($sokytu > 0) {
            $chuoi = 'ABCDEFGHIJKLMNOPQRSTUVWXYZWabcdefghijklmnopqrstuvwxyzw0123456789';
            for ($i = 0; $i < $sokytu; $i++) {
                $vitri = mt_rand(0, strlen($chuoi));
                $str = $str . substr($chuoi, $vitri, 1);
            }
        }

        return $str;
    }

    private function isExpire($file)
    {
        $fileTime = filemtime($file);
        $isExpire = false;

        if ((time() - $fileTime) > $this->cacheTime) {
            $isExpire = true;
        }

        return $isExpire;
    }
}
