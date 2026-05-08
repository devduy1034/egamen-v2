<?php


use LARAVEL\Core\Routing\LARAVELRouter;

LARAVELRouter::group(['namespace' => 'Admin', 'prefix' => config('app.admin_prefix'), 'middleware' => \LARAVEL\Middlewares\LoginAdmin::class], function () {
    LARAVELRouter::get('/delete-cache',function (){
        deleteOldThumbnails();

        transfer('Xóa cache thành công !',1,PeceeRequest()->getHeader('http_referer'));
    })->name('deleteCache');
    LARAVELRouter::get('/delete-cache/{path}',function ($path){
        if(!in_array($path,['thumbs','watermarks'])){
            transfer('Không thể thao tác trên thư mục '.$path,0,PeceeRequest()->getHeader('http_referer'));exit;
        }
        deleteOldThumbnails($path);
        transfer('Xóa cache thành công !',1,PeceeRequest()->getHeader('http_referer'));
    });
    LARAVELRouter::get('/ckfinder/','CKFController@show');
    LARAVELRouter::get('/', 'HomeController@index')->name('index');
    
    LARAVELRouter::match(['get', 'post'], '/user/login', 'UserController@login')->name('loginAdmin');
    LARAVELRouter::get('/user/logout', 'UserController@logout')->name('logoutAdmin');
    LARAVELRouter::group(['middleware' => \LARAVELPermission\Middlewares\PermissionAdmin::class], function () {
        LARAVELRouter::get('/status', 'ApiController@status')->name('status');
        LARAVELRouter::get('/slug', 'ApiController@slug')->name('slug');
        LARAVELRouter::get('/numb', 'ApiController@numb')->name('numb');
        LARAVELRouter::get('/copy', 'ApiController@copy')->name('copy');
        LARAVELRouter::post('/category', 'ApiController@category')->name('category');
        LARAVELRouter::post('/deletephoto', 'ApiController@deletephoto')->name('deletephoto');
        LARAVELRouter::post('/check-link', 'ApiController@checklink')->name('checklink');
        LARAVELRouter::post('/category-group', 'ApiController@categoryGroup')->name('categoryGroup');
        LARAVELRouter::post('/filer', 'ApiController@filer')->name('filer');
        LARAVELRouter::post('/database', 'ApiController@database')->name('database');
        LARAVELRouter::post('/place', 'ApiController@place')->name('place');
        LARAVELRouter::post('/properties', 'ApiController@properties')->name('properties');
        LARAVELRouter::post('/propertiesList', 'ApiController@propertiesList')->name('propertiesList');
        LARAVELRouter::post('/productGallery', 'ApiController@productGallery')->name('productGallery');
        LARAVELRouter::post('/readmail', 'ApiController@readmail')->name('readmail');
        LARAVELRouter::post('/reset-link', 'ApiController@resetlink')->name('resetlink');
        
        LARAVELRouter::get('/permission/man', 'PermissionController@index')->name('permission');
        LARAVELRouter::get('/permission/add', 'PermissionController@add')->name('permission_add');
        LARAVELRouter::post('/permission/save', 'PermissionController@save')->name('permission_save');
        LARAVELRouter::get('/permission/edit/', 'PermissionController@edit',['as' => 'permission_edit']);
        LARAVELRouter::get('/permission/delete', 'PermissionController@delete')->name('permission_delete');

        LARAVELRouter::get('/users/man', 'UserController@index')->name('user');
        LARAVELRouter::get('/users/add', 'UserController@add')->name('user.add');
        LARAVELRouter::post('/users/save', 'UserController@save')->name('user.save');
        LARAVELRouter::get('/users/edit/', 'UserController@edit',['as' => 'user.edit']);
        LARAVELRouter::get('/users/delete', 'UserController@delete')->name('user.delete');
        LARAVELRouter::post('/chat', 'chatGPTController@chat')->name('chat');
        LARAVELRouter::match(['get', 'post'], '/{com}/{act}/{type}', 'SlugController@handle')->name('admin');
    });
});
