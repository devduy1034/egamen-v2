<?php

namespace LARAVEL\Controllers\Web;

use LARAVEL\Core\Support\Facades\View;
use Illuminate\Http\Request;
use LARAVEL\Controllers\Controller;
use LARAVEL\Core\Support\Facades\Seo;
use LARAVEL\Models\PhotoModel;
use LARAVEL\Core\Support\Facades\BreadCrumbs;

class PhotoController extends Controller
{

    private array $template;

    private function initTemplate(?string $type = null)
    {
        $this->template['folder'] = isset($this->configTypeArr['photo'][$type ?? $this->type]['template']) ? $this->configTypeArr['photo'][$type ?? $this->type]['template'] : 'video';
        $this->template['list'] = isset($this->configTypeArr['photo'][$type ?? $this->type]['template-list']) ? $this->configTypeArr['photo'][$type ?? $this->type]['template-list'] : 'video';
    }

    public function index()
    {
        $lang = $this->lang;
        $photo = PhotoModel::select('photo', 'name' . $lang, 'type', 'link_video', 'id')
            ->where('type', $this->type)
            ->whereRaw("FIND_IN_SET(?,status)", ['hienthi'])
            ->orderBy('numb', 'asc')
            ->orderBy('id', 'desc')
            ->paginate(12);
        $titleMain = __('web.' . config('type.photo.' . $this->type . '.website.title'));
        BreadCrumbs::setBreadcrumb(type: $this->type, title: $titleMain);
        $this->seoPage($titleMain, $this->infoSeo('photo', $this->type, 'type', 'index'));

        $this->initTemplate();

        return View::share(['com' => $this->type])->view(implode('.', $this->template), compact('photo', 'titleMain'));
    }
}