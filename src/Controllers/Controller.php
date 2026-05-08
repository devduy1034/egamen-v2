<?php


namespace LARAVEL\Controllers;
use LARAVEL\Core\Routing\LARAVELController;
use LARAVEL\Core\Support\Facades\Seo;
use LARAVEL\Models\SeoPageModel;


class Controller extends LARAVELController
{
    protected string $lang;
    protected string $sluglang;
    protected string $seolang;
    protected ?string $type;
    protected array $configTypeArr;

    public function __construct(){
        $this->lang = session()->get('locale')??config('app.lang_default');
        $this->seolang = app()->getSeoLang();
        $this->sluglang = (count(config('app.slugs') ) > 1) ? 'slug'.$this->lang : 'slug'.config('app.lang_default');
        $this->type = (config('app.langconfig') === 'link') ? request()->segment(2) : (request()->segment(1)??'trang-chu');
        $this->type = $this->type == 'index' ? 'trang-chu' : $this->type;
        $this->configTypeArr = json_decode(json_encode(config('type')), true);
    }
    public function seoPage( $titleMain = '',$type=null): void {
        $seoPage = SeoPageModel::select('*')
            ->where('type', $this->type)
            ->first();
        $seoPage['title' . $this->lang] = $seoPage['title' . $this->lang]?? $titleMain;
        $seoPage['type'] = $type??'website';
        Seo::setSeoData($seoPage,'seopage', 'seopage');
    }
    public function infoSeo($com = '', $type = '', ...$field){
        return config('type.' . $com . '.' . $type . '.website.'.implode('.',$field))??[];
    }

}