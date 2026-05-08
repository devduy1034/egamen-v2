<?php


namespace LARAVEL\Controllers\Web;
use Illuminate\Http\Request;
use LARAVEL\Controllers\Controller;
use LARAVEL\Models\PhotoModel;
use LARAVEL\Models\SettingModel;
use LARAVEL\Models\NewsModel;
use LARAVEL\Models\StaticModel;
use LARAVEL\Models\ExtensionsModel;
use LARAVEL\Models\ProductListModel;
use LARAVEL\Core\Container;

class ApiController
{
    public function token(Request $request){
        $token = csrf_token();
        echo $token;
    }
    public function video(Request $request){
        $id = (!empty($request->id)) ? htmlspecialchars($request->id) : 0;
        $video = NewsModel::select('id', 'link', 'photo', 'link', 'namevi')->where('id', $id)->orderBy('id', 'desc')->first();
        return view('ajax.video', ['video' => $video ?? []]);
    }
}