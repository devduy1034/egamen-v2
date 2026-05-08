<?php


namespace LARAVEL\Providers;
use LARAVEL\Core\ServiceProvider;
use LARAVEL\Helpers\Comment;

class CommentServiceProvider extends ServiceProvider
{
    protected $defer = true;
    public function register(): void
    {
        $this->app->singleton('comment', function () {
            return new Comment();
        });
    }
    public function provides(){
        return ['comment'];
    }
}