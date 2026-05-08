<?php


namespace LARAVEL\Controllers;

use LARAVEL\Core\Support\Facades\File;
use LARAVEL\Core\Support\Facades\Image;
use LARAVEL\Models\PhotoModel;
use Illuminate\Support\Str;

class InterventionController extends Controller
{
    protected function webpQuality(): int
    {
        $quality = (int) config('app.image_webp_quality', 82);

        return max(60, min(100, $quality));
    }

    public function thumb($thumbsize, $path, $folder, $imageUrl)
    {
        $imageUrl = Str::beforeLast($imageUrl, '.webp');
        list($width, $height, $zoom_crop) = array_pad(explode('x', $thumbsize), 3, null);
        $thumb_path = thumb_path() . '/' . $thumbsize . '/' . $path . '/' . $folder;
        $thumbFile = $thumb_path . '/' . $imageUrl . '.webp';
        $this->ensureDirectoryExists($thumb_path);
        $generatedBinary = null;

        if (File::exists($thumbFile)) {
            $this->respondWebp($thumbFile);
        }

        $lockHandle = $this->acquireImageLock($thumbFile);

        try {
            if (!File::exists($thumbFile)) {
                $image = $this->getRead($thumb_path, $folder, $imageUrl, $width, $height, $zoom_crop, $path);
                $generatedBinary = (string) $image->toWebp($this->webpQuality());
                File::put($thumbFile, $generatedBinary);
            }
        } finally {
            $this->releaseImageLock($lockHandle);
        }

        $this->respondWebp($thumbFile, $generatedBinary);
    }

    public function watermark($thumbsize, $path, $folder, $imageUrl)
    {
        $imageUrl = Str::beforeLast($imageUrl, '.webp');
        list($width, $height, $zoom_crop) = array_pad(explode('x', $thumbsize), 3, null);
        $thumb_path = watermark_path() . '/' . $thumbsize . '/' . $path . '/' . $folder;
        $thumbFile = $thumb_path . '/' . $imageUrl . '.webp';
        $this->ensureDirectoryExists($thumb_path);
        $generatedBinary = null;

        if (File::exists($thumbFile)) {
            $this->respondWebp($thumbFile);
        }

        $lockHandle = $this->acquireImageLock($thumbFile);

        try {
            if (!File::exists($thumbFile)) {
                clock()->event('Read image')->color('grey')->begin();
                $image = $this->getRead($thumb_path, $folder, $imageUrl, $width, $height, $zoom_crop);
                clock()->event('Read image')->end();
                $watermark = PhotoModel::where('type', 'watermark_product')->whereRaw("FIND_IN_SET(?,status)", ['hienthi'])->first();
                if (!empty($watermark)) {

                    $options = (!empty($watermark['options'])) ? json_decode($watermark['options'], true) : ['position' => 'top-left', 'offset_x' => 0, 'offset_y' => 0, 'opacity' => 100];
                    clock()->event('Read wte')->color('grey')->begin();
                    $wte = Image::read(upload_path('photo') . '/' . $watermark->photo);
                    clock()->event('Read wte')->end();
                    $wte->contain(config('type.photo.watermark_product.width'), config('type.photo.watermark_product.height'), background: 'ffffff00', position: 'center');
                    $image->place(
                        $wte,
                        $options['position'],
                        $options['offset_x'],
                        $options['offset_y'],
                        $options['opacity'],
                    );
                }
                clock()->event('Read toWebp')->color('grey')->begin();
                $image = $image->toWebp($this->webpQuality());
                clock()->event('Read toWebp')->end();
                clock()->event('save toWebp')->color('grey')->begin();
                $generatedBinary = (string) $image;
                File::put($thumbFile, $generatedBinary);
                clock()->event('save toWebp')->end();
            }
        } finally {
            $this->releaseImageLock($lockHandle);
        }

        $this->respondWebp($thumbFile, $generatedBinary);
    }
    /**
     * @param string $thumb_path
     * @param $folder
     * @param $imageUrl
     * @param $ext
     * @param mixed $width
     * @param mixed $height
     * @return mixed
     */
    public function getRead(string $thumb_path, $folder, $imageUrl, mixed $width, mixed $height, mixed $zoom_crop, $path = ''): mixed
    {
        $this->ensureDirectoryExists($thumb_path);
        $folder = ($path == 'assets') ? base_path('assets/' . $folder) : upload_path($folder);
        $image = Image::read($folder . '/' . $imageUrl);
        if ($zoom_crop == 3) $image->scale($width, $height);
        else if ($zoom_crop == 2) $image->contain($width, $height, background: 'ffffff', position: 'center');
        else $image->cover($width, $height, position: 'center');
        return $image;
    }

    protected function ensureDirectoryExists(string $directory): void
    {
        if (!is_dir($directory) && !@mkdir($directory, 0777, true) && !is_dir($directory)) {
            throw new \RuntimeException('Unable to create thumbnail directory: ' . $directory);
        }
    }

    protected function acquireImageLock(string $thumbFile)
    {
        $lockHandle = @fopen($thumbFile . '.lock', 'c');
        if ($lockHandle !== false) {
            @flock($lockHandle, LOCK_EX);
            return $lockHandle;
        }

        return null;
    }

    protected function releaseImageLock($lockHandle): void
    {
        if (is_resource($lockHandle)) {
            @flock($lockHandle, LOCK_UN);
            @fclose($lockHandle);
        }
    }

    protected function respondWebp(string $thumbFile, ?string $generatedBinary = null): void
    {
        $binary = $generatedBinary;

        if ($binary === null) {
            if (!File::exists($thumbFile)) {
                http_response_code(404);
                exit;
            }

            $binary = @file_get_contents($thumbFile);
            if ($binary === false) {
                http_response_code(404);
                exit;
            }
        }

        while (ob_get_level() > 0) {
            @ob_end_clean();
        }

        if (!headers_sent()) {
            header('Content-Type: image/webp');
            header('Cache-Control: public, max-age=31536000, immutable');
            header('Content-Length: ' . strlen($binary));
        }
        echo $binary;
        exit;
    }
}
