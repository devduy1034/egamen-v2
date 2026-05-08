<?php


namespace LARAVEL\Controllers\Admin;
class CKFController
{
    public function show(){
        session()->set('adminckfider',true);
        return view('component.cksource.index',[]);
    }
}