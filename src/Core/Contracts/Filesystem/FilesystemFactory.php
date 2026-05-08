<?php
namespace LARAVEL\Core\Contracts\Filesystem;

interface FilesystemFactory
{
    public function disk($name = null);
}