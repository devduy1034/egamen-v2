<?php


namespace LARAVEL\Helpers;
use LARAVEL\Core\Singleton;
use LARAVEL\Models\SlugModel;
use LARAVEL\Models\TagsModel;
use LARAVEL\Models\OrderStatusModel;
use LARAVEL\Models\NewsModel;
use LARAVEL\Models\SettingModel;
use LARAVEL\Models\CounterModel;
use LARAVEL\Models\PropertiesModel;
use LARAVEL\Models\PropertiesListModel;
use LARAVEL\Models\GalleryModel;
use LARAVEL\Models\UserModel;
use LARAVEL\Models\LinkModel;
use LARAVEL\Models\ProductPropertiesModel;
use Illuminate\Http\Request;
use LARAVEL\Core\Support\Facades\DB;
use IvoPetkov\HTML5DOMDocument;
use Auth;

class LARAVEL
{
    use Singleton;
    private $hash;
    private $cache;


}