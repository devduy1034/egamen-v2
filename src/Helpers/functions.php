<?php


if (!function_exists('transfer')) {
    function transfer($showtext = '', $numb = '', $page_transfer = '')
    {
        return view('component.transfer', ['showtext' => $showtext, 'numb' => $numb, 'page_transfer' => $page_transfer]);
    }
}


if (!function_exists('linkReferer')) {
    function linkReferer(): array|string|null
    {
        if (!empty(request()->server('HTTP_REFERER'))) {
            return urldecode(request()->server('HTTP_REFERER'));
        } else {
            return '';
        }
    }
}

if (!function_exists('alert')) {
    function alert($notify = ''): void
    {
        echo '<script language="javascript">alert("' . $notify . '")</script>';
    }
}
if (! function_exists('minify_html')) {
    function minify_html($html): array|string|null
    {
        $search = array(
            '/\>[^\S ]+/s',
            '/[^\S ]+\</s',
            '/(\s)+/s'
        );
        $replace = array(
            '>',
            '<',
            '\\1'
        );
        return preg_replace($search, $replace, $html);
    }
}
if (! function_exists('convertToModelClass')) {
    function convertToModelClass($input)
    {
        $camelCase = str_replace(' ', '', ucwords(str_replace('-', ' ', $input)));
        $modelClass = $camelCase . 'Model';
        $namespace = '\\LARAVEL\\Models\\' . $modelClass;
        return $namespace;
    }
}

if (! function_exists('remember')) {
    function remember(string $cacheKey, int $timeCache, callable $callback, \Psr\Cache\CacheItemPoolInterface $cachePool = null)
    {
        $cachePool = new \Symfony\Component\Cache\Adapter\FilesystemAdapter('', 0, ROOT_PATH . '/caches');
        try {
            $cacheItem = $cachePool->getItem($cacheKey);
            if (!$cacheItem->isHit() || config('app.environment') == 'dev') {
                $data = $callback();
                $cacheItem->set($data);
                $cacheItem->expiresAfter($timeCache);
                $cachePool->save($cacheItem);
                return $data;
            } else {
                return $cacheItem->get();
            }
        } catch (\Psr\Cache\InvalidArgumentException $e) {
            throw $e;
        }
    }
}

if (! function_exists('assets_photo')) {
    function assets_photo($path, $size, $photo, $type = '')
    {
        $ext = (!empty($type)) ? '.webp' : '';
        return assets() . (!$type ? '' : ($type . '/' . $size . '/')) . 'upload/' . $path . '/' . $photo . $ext;
    }
}