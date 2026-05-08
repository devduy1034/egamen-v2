<?php


namespace LARAVEL\Controllers\Web;
use LARAVEL\Core\Support\Facades\View;
use Illuminate\Http\Request;
use LARAVEL\Controllers\Controller;
use LARAVEL\Core\Support\Facades\Seo;
use LARAVEL\Models\StaticModel;
use LARAVEL\Core\Support\Facades\BreadCrumbs;

class StaticController extends Controller
{
    public function index(Request $request)
    {
        $rowDetail = StaticModel::select('name' . $this->lang, 'photo', 'desc' . $this->lang, 'content' . $this->lang, 'type', 'id')
            ->where('type', $this->type)
            ->first();
        $seoPage = $rowDetail?->getSeo('static', 'save')->first();
        $seoPage['type'] = 'article';
        $titleMain =  $this->infoSeo('static', $this->type, 'title');
        BreadCrumbs::setBreadcrumb(type: $this->type, title: __('web.'.$titleMain));
        Seo::setSeoData($seoPage, 'news');
        return View::share('com', $this->type)->view('static.static', ['static' => $rowDetail]);
    }
}