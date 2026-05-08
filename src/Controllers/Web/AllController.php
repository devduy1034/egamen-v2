<?php


namespace LARAVEL\Controllers\Web;

use LARAVEL\Controllers\Controller;
use LARAVEL\Models\PhotoModel;
use LARAVEL\Models\SettingModel;
use LARAVEL\Models\NewsModel;
use LARAVEL\Models\StaticModel;
use LARAVEL\Models\ExtensionsModel;
use LARAVEL\Models\ProductListModel;
use LARAVEL\Models\ProductCatModel;
use LARAVEL\Core\Container;

class AllController extends Controller
{
    function composer($view): void
    {

        $all = remember('all', 86400, function () {
            $photos = PhotoModel::select('photo', 'name' . $this->lang, 'link', 'type')
                ->whereIn('type', ['logo', 'logoft', 'favicon', 'social', 'mangxahoi1', 'banner_quangcao', 'banner_menu'])
                ->whereRaw("FIND_IN_SET(?, status)", ['hienthi'])
                ->orderBy('numb', 'asc')
                ->orderBy('id', 'desc')
                ->get();

            $logoPhoto = $photos->where('type', 'logo')->first();

            $logoPhotoFooter = $photos->where('type', 'logoft')->first();

            $favicon = $photos->where('type', 'favicon')->first();

            $banner_menu = $photos->where('type', 'banner_menu')->first();


            $social = $photos->where('type', 'social');

            $social1 = $photos->where('type', 'mangxahoi1');

            $banner_quangcao = $photos->where('type', 'banner_quangcao');

            $listProductMenu = ProductListModel::select('name' . $this->lang, 'desc' . $this->lang, 'slug' . $this->lang, 'id', 'photo', 'type')
                ->where('type', 'san-pham')
                ->whereRaw("FIND_IN_SET(?,status)", ['hienthi'])
                ->with([
                    'getCategoryCats' => function ($query) {
                        $query->with([
                            'getCategoryItems' => function ($query) {
                                $query->with([
                                    'getCategorySubs' => function ($query) {
                                        $query->whereRaw("FIND_IN_SET(?,status)", ['hienthi']);
                                    }
                                ])->whereRaw("FIND_IN_SET(?,status)", ['hienthi']);
                            }
                        ])->whereRaw("FIND_IN_SET(?,status)", ['hienthi']);
                    }
                ])
                ->orderBy('numb', 'asc')
                ->orderBy('id', 'desc')
                ->get();


            $footer = StaticModel::select('name' . $this->lang, 'content' . $this->lang, 'desc' . $this->lang, 'slug' . $this->lang, 'type', 'photo')
                ->where('type', 'footer')
                ->first();

            $extHotline = ExtensionsModel::select('*')
                ->where('type', 'hotline')
                ->whereRaw("FIND_IN_SET(?,status)", ['hienthi'])
                ->first();

            $extSocial = ExtensionsModel::select('*')
                ->where('type', 'social')
                ->whereRaw("FIND_IN_SET(?,status)", ['hienthi'])
                ->first();

            $extPopup = ExtensionsModel::select('*')
                ->where('type', 'popup')
                ->whereRaw("FIND_IN_SET(?,status)", ['hienthi'])
                ->first();


            $support = NewsModel::select('name' . $this->lang, 'slug' . $this->lang,  'id', 'type')
                ->where('type', 'ho-tro-khach-hang')
                ->whereRaw("FIND_IN_SET(?,status)", ['hienthi'])
                ->orderBy('numb', 'asc')
                ->orderBy('id', 'desc')
                ->get();

            $setting = SettingModel::select('*')->first();

            $optSetting = json_decode($setting['options'], true);

            $configType = json_decode(json_encode(config('type')));

            $lang = session()->get('locale') ?? config('app.lang_default');

            $sluglang = $this->sluglang;

            return get_defined_vars();
        });

        // $all['statistic'] = \Statistic::getCounter();

        $view->share($all);
    }
}