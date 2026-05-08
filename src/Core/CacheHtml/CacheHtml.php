<?php
namespace LARAVEL\Core\CacheHtml;
use Illuminate\Support\Str;
use JetBrains\PhpStorm\NoReturn;
use LARAVEL\Core\Singleton;
class CacheHtml
{
    use Singleton;
    private function writeFile($content, $name): void
    {
        $myFile = fopen(cache_path().'/'.$name, "w");
        fwrite($myFile, $content);
        fclose($myFile);
    }
    private function readFile($name): string
    {
        if (file_exists(cache_path().'/'.$name)) {
            echo file_get_contents(cache_path().'/'.$name);
        }
        return '';
    }
    public function set($content, $name): void
    {
        if (!empty($content) && !empty($name)) {
            $this->writeFile($content, $name);
        }
    }
     public function checkUrlCache($path): bool
    {
        $cacheUrl =array_merge(config('app.nocache'),['/thumbs','/admin','/watermarks']);
        foreach ($cacheUrl as $url){
            if(strpos($path, $url)){
                return false;
            }
        }
        return true;
    }
    public function checkFile($name): bool
    {
        if(file_exists(cache_path().'/'.$name)){
            if(config('app.cache_pages_time')>0){
                $fileModifiedTime = filemtime(cache_path().'/'.$name);
                $currentTimestamp = time();
                $timeDifference = $currentTimestamp - $fileModifiedTime;
                $timeDifferenceInMinutes = $timeDifference / 60;
                if ($timeDifferenceInMinutes < config('app.cache_pages_time')) return true;
                return false;
            }
            return true;
        }
        return false;
    }
    public function get($name): void
    {
        $this->readFile($name);
    }
}