<?php



namespace LARAVEL\Helpers;

use LARAVEL\Core\Singleton;
use LARAVEL\Core\Support\Str;

class BreadCrumbs
{
    use Singleton;
    private $data = array();
    protected $lang;
    protected $seolang;
    public function __construct()
    {
        $this->seolang = app()->getSeoLang();
        $this->lang = session()->get('locale');
  
    }

    public function set($slug = '', $name = '')
    {
        if ($name != '') {
            $this->data[] = array('slug' => $slug, 'name' => $name);
        }
    }
    public function get(): string
    {
        $json = array();
        $breadcumb = '';
        if ($this->data) {
            $breadcumb .= '<ol class="breadcrumb">';
            $breadcumb .= '<li class="breadcrumb-item"><a class="text-decoration-none" href="' . url('home') . '"><span>Trang chủ</span></a></li>';
            $k = 1;

            foreach ($this->data as $key => $value) {
                if ($value['name'] != '') {
                    $slug = $value['slug'];
                    $name = $value['name'];
                    $active = ($key == count($this->data) - 1) ? "active" : "";
                    $breadcumb .= '<li class="breadcrumb-item ' . $active . '"><a class="text-decoration-none" href="' . $slug . '"><span>' . $name . '</span></a></li>';
                    $json[] = array("@type" => "ListItem", "position" => $k, "name" => $name, "item" => config('app.asset').$slug);
                    $k++;
                }
            }
            $breadcumb .= '</ol>';
            $breadcumb .= '<script type="application/ld+json">{"@context": "https://schema.org","@type": "BreadcrumbList","itemListElement": ' . ((json_encode($json,JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT))) . '}</script>';
        }
        return $breadcumb;
    }
    public function setBreadcrumb($type='',$title = '', $detail = '', $list = '', $cat = '', $item = '', $sub = ''): string{
        if (Str::isNotEmpty($title??'')) $this->set(url($type), $title);
        $this->extracted($list, $cat);
        if (!empty($item)) $this->set(url('slugweb',['slug'=>$item['slug' . $this->seolang]]), $item['name' . $this->lang]);
        $this->extracted($sub, $detail);
        return $this->get();
    }

    /**
     * @param mixed $list
     * @param mixed $cat
     * @return void
     */
    public function extracted(mixed $list, mixed $cat): void
    {
        if (!empty($list)) $this->set(url('slugweb', ['slug' => $list['slug' . $this->seolang]]), $list['name' . $this->lang]);
        if (!empty($cat)) $this->set(url('slugweb', ['slug' => $cat['slug' . $this->seolang]]), $cat['name' . $this->lang]);
    }
}
