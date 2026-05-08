<?php


namespace LARAVEL\Helpers;
use Illuminate\Support\Arr;
use LARAVEL\Core\Singleton;
use LARAVEL\Core\Support\Facades\Func;
use LARAVEL\Models\SeoModel;
use LARAVEL\Core\Support\Facades\File;
use DB;
class Seo
{
    use Singleton;
    private $d;
    private $data;
    protected $seolang;
    protected $lang;
    public function __construct() {
        $this->seolang = app()->getSeoLang();
        $this->lang = session()->get('locale');
    }

    public function set($key = '', $value = '') {
        if (!empty($key) && !empty($value)) {
            $this->data[$key] = $value;
        }
    }
    public function get($key): string {
        return $this->data[$key]??'';
    }
    public function getOnDB($id = 0, $com = '', $act = '', $type = ''){
        if ($id || $act == 'update') {
            if ($id) return SeoModel::where('id_parent', $id)->where('com', $com)->where('act', $act)->where('type', $type)->first();
            else return SeoModel::where('com', $com)->where('act', $act)->where('type', $type)->first();
        }
    }
    public function setSeoData($seoPage,$path='seopage',$table='seo'){
        if (!empty($seoPage)) {
            $meta = (!empty($seoPage['meta']))?json_decode($seoPage['meta'], true):[];
            if (!empty($meta) && $meta['metaindex'] == 'index') $meta['metaindex'] = $meta['metaindex'] . ',follow,noodp';
            else if(empty($meta)) {
                $meta['metaindex'] = 'index,follow,noodp';$meta['metaorder'] ='';
            }
            $this->set('meta', rtrim(Arr::join([$meta['metaindex'], ($meta['metaorder']??[])], ','),','));
            $this->set('h1', $seoPage['title'.$this->lang]??'');
            $this->set('type', $seoPage['type']);
            $this->set('schema', $seoPage['schema'.$this->seolang]??'');
            $this->set('title', $seoPage['title'.$this->seolang]??'');
            $this->set('keywords', $seoPage['keywords'. $this->seolang]??'');
            $this->set('description', $seoPage['description' . $this->seolang]??'');
            $this->set('url',request()->url());
            $imgJson = (!empty($seoPage['base_options'])) ? json_decode($seoPage['base_options'], true) : null;
            if (!empty($seoPage['photo']) && File::exists(upload_path_photo($path,$seoPage['photo']))) {
                $storedPhoto = is_array($imgJson) ? (string) ($imgJson['p'] ?? '') : '';
                $hasImageMeta = is_array($imgJson)
                    && isset($imgJson['w'], $imgJson['h'], $imgJson['m']);

                if (empty($imgJson) || !$hasImageMeta || $storedPhoto !== (string) $seoPage['photo']) {
                    $imgJson = Func::getImgSize($seoPage['photo'],upload_path_photo($path,$seoPage['photo']));
                    $this->updateSeoDB(json_encode($imgJson), $table, $seoPage['id']);
                }
                if (!empty($imgJson) && isset($imgJson['w'], $imgJson['h'], $imgJson['m'])) {
                    $this->set('photo', assets_photo($path,$imgJson['w'].'x'.$imgJson['h'].'x1',$seoPage['photo'],'thumbs'));
                    $this->set('photo:width', $imgJson['w']);
                    $this->set('photo:height', $imgJson['h']);
                    $this->set('photo:type', $imgJson['m']);
                }
            }
        }
    }
    public function updateSeoDB($json = '', $table = '', $id = 0){
        return DB::table($table)->where('id', $id)->update(['options' => $json]);
    }
    static public function listCriteria($key = '') {
        $listCriteria = [
            'keywordNotUsed' => 'Đặt Từ khóa tập trung cho nội dung này.',
            'keywordInTitle' => 'Thêm từ khóa chính vào tiêu đề SEO.',
            'titleStartWithKeyword' => 'Sử dụng từ khóa chính gần đầu tiêu đề SEO.',
            'lengthTitle' => 'Tiêu đề của bài viết phải lớn hơn 40 ký tự và khuyến cáo nhỏ hơn 70 ký tự',
            'keywordInMetaDescription' => 'Thêm Từ khóa tập trung vào Mô tả meta SEO của bạn.',
            'lengthMetaDescription' => 'Mô tả meta SEO của bạn nên có từ 155 đến 160 ký tự.',
            'keywordInPermalink' => 'Sử dụng từ khóa chính trong URL.',
            'lengthPermalink' => 'URL không khả dụng. Thêm URL ngắn.',
            'keywordIn10Percent' => 'Sử dụng từ khóa chính ở đầu nội dung của bạn.',
            'keywordInContent' => 'Sử dụng từ khóa chính trong nội dung.',
            'lengthContent' => 'Nội dung phải dài 600-2500 từ.',
            'linksHasInternal' => 'Thêm liên kết nội bộ vào nội dung của bạn.',
            'keywordInSubheadings' => 'Sử dụng từ khóa chính trong (các) tiêu đề phụ như H2, H3, H4, v.v..',
            'keywordInImageAlt' => 'Thêm từ khóa vào thuộc tính alt của hình ảnh',
            'keywordDensity' => 'Mật độ từ khóa là 0. Nhắm đến khoảng 1% Mật độ từ khóa.',
            'contentHasShortParagraphs' => 'Thêm các đoạn văn ngắn và súc tích để dễ đọc và UX hơn.',
            'contentHasAssets' => 'Thêm một vài hình ảnh để làm cho nội dung của bạn hấp dẫn.',
        ];
        if(!empty($key)) return Arr::get($listCriteria, $key);
        return $listCriteria;
    }
}
