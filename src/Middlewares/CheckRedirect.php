<?php



namespace LARAVEL\Middlewares;

use Pecee\Http\Middleware\IMiddleware;
use Pecee\Http\Request;

class CheckRedirect implements IMiddleware
{
    public function handle(Request $request) : void{
        $urlRequest = trim(($request->getUrl()->getScheme()??'http').'://'.$request->getHost().$request->getUrl()->getPath(),'/');

        $checkRedirect = remember('checkRedirect', 86400, function () use ($urlRequest) {
            $row = \LARAVEL\Models\PhotoModel::select(['link','link_redirect','redirect'])->where('link',$urlRequest)->where('type','dieu-huong')->first();
            return $row;
        });

        if(!empty($checkRedirect)){
            response()->redirect($checkRedirect['link_redirect'],(int)$checkRedirect['redirect']);
        }
    }
}
