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

class Func
{
    use Singleton;
    private $hash;
    private $cache;


    public function convert_utf8_to_iconv($str = '')
    {
        return iconv('UTF-8', 'ISO-8859-1', $str);
    }

    public function get_youtube_shorts($str)
    {
        $char = 'shorts/';
        $pos = strpos($str, $char);
        if ($pos !== false) {
            // Explode the string and store the result in a variable
            $parts = explode($char, $str);
            // Use end() on the variable
            $videoId = end($parts);
            // Return the YouTube URL
            $str = "https://www.youtube.com/watch?v=" . $videoId;
        }

        return $str;
    }

    public function get_youtube($str)
    {
        // Nếu là link Shorts
        if (strpos($str, 'shorts/') !== false) {
            $parts = explode('shorts/', $str);
            $id = end($parts);
        }
        // Nếu là link dạng watch?v=
        elseif (strpos($str, 'watch?v=') !== false) {
            $parts = explode('watch?v=', $str);
            $id = end($parts);
        }
        // Nếu đã là embed sẵn
        elseif (strpos($str, 'embed/') !== false) {
            $parts = explode('embed/', $str);
            $id = end($parts);
        } else {
            // Không đúng định dạng YouTube, trả nguyên chuỗi
            return $str;
        }

        // Xóa các tham số dư (nếu có)
        $id = strtok($id, '&?');

        // Trả về link embed chuẩn, tự động phát + tắt tiếng
        return "https://www.youtube.com/embed/{$id}?autoplay=1&mute=1&loop=1&playlist={$id}";
    }

    public function chekcPermission($permissive = '', $permissions = [])
    {
        if (!empty(config('type.users.permission')) && !empty(Auth::guard('admin')->check())) {
            if (in_array($permissive, $permissions) || Auth::guard('admin')->user()->hasRole('Admin')) {
                return true;
            } else {
                return false;
            }
        }
        return true;
    }

    public function isGoogleSpeed()
    {
        if (!isset($_SERVER['HTTP_USER_AGENT']) || (stripos($_SERVER['HTTP_USER_AGENT'], 'Lighthouse') === false && stripos($_SERVER['HTTP_USER_AGENT'], 'Google-InspectionTool') === false)) {
            return false;
        }
        return true;
    }

    public function checkRedirect()
    {
        global $http;
        $getLink = $this->cache->get("select link,link2,loaidieuhuong from #_photo where type = ? and find_in_set('hienthi',status) order by numb,id desc", array('dieuhuonglink'), 'result', 7200);
        foreach ($getLink as $v) {
            if ($http . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] == $v['link']) {
                $this->redirect($v['link2'], ($v['loaidieuhuong'] == 1) ? 301 : 302);
            }
        }
    }
    /* Markdown */
    public function markdown($path = '', $params = array())
    {
        $content = '';

        if (!empty($path)) {
            ob_start();
            include dirname(__DIR__) . "/sample/" . $path . ".php";
            $content = ob_get_contents();
            ob_clean();
        }

        return $content;
    }
    public function webpinfo($file)
    {
        if (!is_file($file)) {
            return false;
        } else {
            $file = realpath($file);
        }
        $fp = fopen($file, 'rb');
        if (!$fp) return false;
        $data = fread($fp, 90);
        fclose($fp);
        unset($fp);
        $header_format = 'A4Riff/' .
            'I1Filesize/' .
            'A4Webp/' .
            'A4Vp/' .
            'A74Chunk';
        $header = unpack($header_format, $data);
        unset($data, $header_format);
        if (!isset($header['Riff']) || strtoupper($header['Riff']) !== 'RIFF') return false;
        if (!isset($header['Webp']) || strtoupper($header['Webp']) !== 'WEBP') return false;
        if (!isset($header['Vp']) || strpos(strtoupper($header['Vp']), 'VP8') === false) return false;
        if (strpos(strtoupper($header['Chunk']), 'ANIM') !== false || strpos(strtoupper($header['Chunk']), 'ANMF') !== false) {
            $header['Animation'] = true;
        } else {
            $header['Animation'] = false;
        }
        if (strpos(strtoupper($header['Chunk']), 'ALPH') !== false) {
            $header['Alpha'] = true;
        } else {
            if (strpos(strtoupper($header['Vp']), 'VP8L') !== false) {
                $header['Alpha'] = true;
            } else {
                $header['Alpha'] = false;
            }
        }
        unset($header['Chunk']);
        return $header;
    }

    public function writeJson()
    {
        $row = SlugModel::select('*')
            ->where('slugvi', '<>', '')
            ->get();

        $data = array();
        if (!empty($row)) {
            foreach ($row as $k => $v) {
                $data['slug'][] = $v;
                unset($row[$k]);
            }
        }
        /* Put data */
        $this->putJson('slug.json', $data);

        return true;
    }

    public function nameSlug($id = 0, $type = '', $field = 'namevi')
    {
        $row = SlugModel::select('namevi', 'slugvi')
            ->where('id_parent', $id)
            ->where('type', $type)
            ->first();

        return $row[$field];
    }

    /* Put json */
    public function putJson($filename = '', $data = array())
    {

        if (!empty($data)) {
            $data = json_encode($data, JSON_UNESCAPED_UNICODE);
            $file = fopen(app()->make('path.base') . '/assets/admin/json/' . $filename, "w+");

            if (!empty($file)) {
                fwrite($file, $data);
                fclose($file);
            }
        } else if (file_exists(app()->make('path.base') . '/assets/admin/json/' . $filename)) {
            $this->deleteFile(app()->make('path.base') . '/assets/admin/json/' . $filename);
        }
        return true;
    }
    public function allNoty()
    {
        $allNoty = 0;
        if (!empty(config('type.newsletters'))) {
            foreach (config('type.newsletters') as $key => $v) {
                $allNoty += \LARAVEL\Models\NewslettersModel::where('confirm_status', 1)->where('type', $key)->count();
            }
        }
        if (!empty(config('type.order'))) {
            $allNoty += \LARAVEL\Models\OrdersModel::where('order_status', 1)->count();
        }
        if (!empty(config('type.comment'))) {
            $allNoty += \LARAVEL\Models\CommentModel::where('status', '')->count();
        }
        return $allNoty;
    }
    /* Check URL */
    public function checkURL($index = false)
    {
        global $configBase;

        $url = '';
        $urls = array('index', 'index.html', 'trang-chu', 'trang-chu.html');

        if (array_key_exists('REDIRECT_URL', $_SERVER)) {
            $url = explode("/", $_SERVER['REDIRECT_URL']);
        } else {
            $url = explode("/", $_SERVER['REQUEST_URI']);
        }

        if (is_array($url)) {
            $url = $url[count($url) - 1];
            if (strpos($url, "?")) {
                $url = explode("?", $url);
                $url = $url[0];
            }
        }

        if ($index) array_push($urls, "index.php");
        else if (array_search('index.php', $urls)) $urls = array_diff($urls, ["index.php"]);
        if (in_array($url, $urls)) $this->redirect($configBase, 301);
    }

    public function checkCart($id = 0)
    {
        $row = PropertiesListModel::select('id', 'status')
            ->where('id', $id)
            ->whereRaw("FIND_IN_SET(?,status)", ['cart'])
            ->first();
        if (!empty($row)) {
            return true;
        } else {
            return false;
        }
    }

    public function checkPhotoProperties($id = '')
    {
        if (!empty($id)) {

            $arrayId = explode(',', $id);

            $row = PropertiesListModel::select('id', 'status')
                ->whereRaw("FIND_IN_SET(?,status)", ['photo'])
                ->whereIn("id", $arrayId)
                ->first();
            if (!empty($row)) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function infoProperties($id = null, $id_product = '')
    {
        if (!empty($id)) {
            $row = ProductPropertiesModel::select('id', 'namevi', 'regular_price', 'sale_price', 'number', 'status')
                ->where('id_parent', $id_product)
                ->where('id_properties', $id)
                ->first();
            return $row;
        }
        return false;
    }

    /* Check Is Ajax Request */
    public function isAjax()
    {
        return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && (strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'));
    }

    /* Check HTTP */
    public function checkHTTP($http, $arrayDomain, &$configBase, $configUrl)
    {
        if (count($arrayDomain) == 0 && $http == 'https://') {
            $configBase = 'http://' . $configUrl;
        }
    }

    /* Create sitemap */


    /* Kiểm tra dữ liệu nhập vào */
    public function cleanInput($input = '', $type = '')
    {
        $output = '';

        if ($input != '') {
            /*
					// Loại bỏ HTML tags
					'@<[\/\!]*?[^<>]*?>@si',
*/

$search = array(
'script' => '@<script[^>]*?>.*?</script>@si',
    'style' => '@<style[^>]*?>.*?</style>@siU',
        'blank' => '@
        <![\s\S]*?--[ \t\n\r]*>@',
                'iframe' => '/<iframe(.*?)<\/iframe>/is',
                'title' => '/<title(.*?)<\/title>/is',
                'pre' => '/<pre(.*?)<\/pre>/is',
                'frame' => '/<frame(.*?)<\/frame>/is',
                'frameset' => '/<frameset(.*?)<\/frameset>/is',
                'object' => '/<object(.*?)<\/object>/is',
                'embed' => '/<embed(.*?)<\/embed>/is',
                'applet' => '/<applet(.*?)<\/applet>/is',
                'meta' => '/<meta(.*?)<\/meta>/is',
                'doctype' => '/<!doctype(.*?)>/is',
                'link' => '/<link(.*?)>/is',
                'body' => '/<body(.*?)<\/body>/is',
                'html' => '/<html(.*?)<\/html>/is',
                'head' => '/<head(.*?)<\/head>/is',
                'onclick' => '/onclick="(.*?)"/is',
                'ondbclick' => '/ondbclick="(.*?)"/is',
                'onchange' => '/onchange="(.*?)"/is',
                'onmouseover' => '/onmouseover="(.*?)"/is',
                'onmouseout' => '/onmouseout="(.*?)"/is',
                'onmouseenter' => '/onmouseenter="(.*?)"/is',
                'onmouseleave' => '/onmouseleave="(.*?)"/is',
                'onmousemove' => '/onmousemove="(.*?)"/is',
                'onkeydown' => '/onkeydown="(.*?)"/is',
                'onload' => '/onload="(.*?)"/is',
                'onunload' => '/onunload="(.*?)"/is',
                'onkeyup' => '/onkeyup="(.*?)"/is',
                'onkeypress' => '/onkeypress="(.*?)"/is',
                'onblur' => '/onblur="(.*?)"/is',
                'oncopy' => '/oncopy="(.*?)"/is',
                'oncut' => '/oncut="(.*?)"/is',
                'onpaste' => '/onpaste="(.*?)"/is',
                'php-tag' => '/<(\?|\%)\=?(php)?/',
                'php-short-tag' => '/(\%|\?)>/',
                'sandbox' => '/sandbox="(.*?)"/is'
            );

            if (!empty($type)) {
                unset($search[$type]);
            }

            $output = preg_replace($search, '', $input);
        }

        return $output;
    }

    /* Kiểm tra dữ liệu nhập vào */
    public function sanitize($input = '', $type = '')
    {
        if (is_array($input)) {
            foreach ($input as $var => $val) {
                $output[$var] = $this->sanitize($val, $type);
            }
        } else {
            $output  = $this->cleanInput($input, $type);
        }

        return $output;
    }

    /* Decode html characters */
    public function decodeHtmlChars($htmlChars)
    {
        return htmlspecialchars_decode($htmlChars ?: '');
    }


    /* Mã hóa mật khẩu admin */
    public function encryptPassword($secret = '', $str = '', $salt = '')
    {
        return md5($secret . $str . $salt);
    }

    /* Kiểm tra phân quyền menu */
    public function checkPermission($com = '', $act = '', $type = '', $array = null, $case = '')
    {
        global $loginAdmin;

        $str = $com;

        if ($act) $str .= '_' . $act;

        if ($case == 'phrase-1') {
            if ($type != '') $str .= '_' . $type;
            if (!in_array($str, $_SESSION[$loginAdmin]['permissions'])) return true;
            else return false;
        } else if ($case == 'phrase-2') {
            $count = 0;

            if ($array) {
                foreach ($array as $key => $value) {
                    if (!empty($value['dropdown'])) {
                        unset($array[$key]);
                    }
                }

                foreach ($array as $key => $value) {
                    if (!in_array($str . "_" . $key, $_SESSION[$loginAdmin]['permissions'])) $count++;
                }

                if ($count == count($array)) return true;
            } else return false;
        }

        return false;
    }

    /* Kiểm tra phân quyền */
    public function checkRole()
    {
        global $config, $loginAdmin;

        if ((!empty($_SESSION[$loginAdmin]['role']) && $_SESSION[$loginAdmin]['role'] == 3) || !empty($config['website']['debug-developer'])) return false;
        else return true;
    }

    /* Lấy tình trạng nhận tin */
    public function getStatusNewsletter($confirm_status = 0, $type = '')
    {
        $loai = '';
        if (!empty(config('type.newsletters'))) {
            foreach (config('type.newsletters.' . $type . '.confirm_status') as $key => $value) {
                if ($key == $confirm_status) {
                    $loai = $value;
                    break;
                }
            }
        }
        if ($loai == '') $loai = "Đang chờ duyệt...";
        return $loai;
    }

    public function getStatuscontact($confirm_status = 0, $type = '')
    {
        $loai = '';
        if (!empty(config('type.contact'))) {
            foreach (config('type.contact.' . $type . '.confirm_status') as $key => $value) {
                if ($key == $confirm_status) {
                    $loai = $value;
                    break;
                }
            }
        }
        if ($loai == '') $loai = "Đang chờ duyệt...";
        return $loai;
    }

    /* Database maintenance */
    public function databaseMaintenance($action = '', $tables = array())
    {
        $result = array();
        $row = array();

        if (!empty($action) && !empty($tables)) {
            foreach ($tables as $k => $v) {
                foreach ($v as $table) {
                    $result = DB::select("$action TABLE $table");
                    if (!empty($result)) {
                        $row[$k]['table'] = $result[0]->Table;
                        $row[$k]['action'] = $result[0]->Op;
                        $row[$k]['type'] = $result[0]->Msg_type;
                        $row[$k]['text'] = $result[0]->Msg_text;
                    }
                }
            }
        }

        return $row;
    }

    /* Format money */
    public function formatMoney($price = 0, $unit = 'đ', $html = false)
    {
        $str = '';

        if ($price) {
            $str .= number_format($price, 0, ',', '.');
            if ($unit != '') {
                if ($html) {
                    $str .= '<span>' . $unit . '</span>';
                } else {
                    $str .= $unit;
                }
            }
        }

        return $str;
    }

    /* Is phone */
    public function isPhone($number)
    {
        $number = trim($number);
        if (preg_match_all('/^(0|84)(2(0[3-9]|1[0-6|8|9]|2[0-2|5-9]|3[2-9]|4[0-9]|5[1|2|4-9]|6[0-3|9]|7[0-7]|8[0-9]|9[0-4|6|7|9])|3[2-9]|5[5|6|8|9]|7[0|6-9]|8[0-6|8|9]|9[0-4|6-9])([0-9]{7})$/m', $number, $matches, PREG_SET_ORDER, 0)) {
            return true;
        } else {
            return false;
        }
    }

    /* Format phone */
    public function formatPhone($number, $dash = ' ')
    {
        if (preg_match('/^(\d{4})(\d{3})(\d{3})$/', $number, $matches) || preg_match('/^(\d{3})(\d{4})(\d{4})$/', $number, $matches)) {
            return $matches[1] . $dash . $matches[2] . $dash . $matches[3];
        }
    }

    /* Parse phone */
    public function parsePhone($number)
    {
        return (!empty($number)) ? preg_replace('/[^0-9]/', '', $number) : '';
    }

    /* Check letters and nums */
    public function isAlphaNum($str)
    {
        if (preg_match('/^[a-z0-9]+$/', $str)) {
            return true;
        } else {
            return false;
        }
    }

    /* Is email */
    public function isEmail($email)
    {
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return true;
        } else {
            return false;
        }
    }

    /* Is match */
    public function isMatch($value1, $value2)
    {
        if ($value1 == $value2) {
            return true;
        } else {
            return false;
        }
    }

    /* Is decimal */
    public function isDecimal($number)
    {
        if (preg_match('/^\d{1,10}(\.\d{1,4})?$/', $number)) {
            return true;
        } else {
            return false;
        }
    }

    /* Is coordinates */
    public function isCoords($str)
    {
        if (preg_match('/^[-+]?([1-8]?\d(\.\d+)?|90(\.0+)?),\s*[-+]?(180(\.0+)?|((1[0-7]\d)|([1-9]?\d))(\.\d+)?)$/', $str)) {
            return true;
        } else {
            return false;
        }
    }

    /* Is url */
    public function isUrl($str)
    {
        if (preg_match('/^(https?:\/\/(?:www\.|(?!www))[a-zA-Z0-9][a-zA-Z0-9-]+[a-zA-Z0-9]\.[^\s]{2,}|www\.[a-zA-Z0-9][a-zA-Z0-9-]+[a-zA-Z0-9]\.[^\s]{2,}|https?:\/\/(?:www\.|(?!www))[a-zA-Z0-9]+\.[^\s]{2,}|www\.[a-zA-Z0-9]+\.[^\s]{2,})/', $str)) {
            return true;
        } else {
            return false;
        }
    }

    /* Is url youtube */
    public function isYoutube($str)
    {
        if (preg_match('/https?:\/\/(?:[a-zA_Z]{2,3}.)?(?:youtube\.com\/watch\?)((?:[\w\d\-\_\=]+&amp;(?:amp;)?)*v(?:&lt;[A-Z]+&gt;)?=([0-9a-zA-Z\-\_]+))/i', $str)) {
            return true;
        } else {
            return false;
        }
    }

    /* Is fanpage */
    public function isFanpage($str)
    {
        if (preg_match('/^(https?:\/\/)?(?:www\.)?facebook\.com\/(?:(?:\w)*#!\/)?(?:pages\/)?(?:[\w\-]*\/)*([\w\-\.]*)/', $str)) {
            return true;
        } else {
            return false;
        }
    }

    /* Is date */
    public function isDate($str)
    {
        if (preg_match('/^([0-2][0-9]|(3)[0-1])(\/)(((0)[0-9])|((1)[0-2]))(\/)\d{4}$/', $str)) {
            return true;
        } else {
            return false;
        }
    }

    /* Is date by format */
    public function isDateByFormat($str, $format = 'd/m/Y')
    {
        $dt = DateTime::createFromFormat($format, $str);
        return $dt && $dt->format($format) == $str;
    }

    /* Is number */
    public function isNumber($numbs)
    {
        if (preg_match('/^[0-9]+$/', $numbs)) {
            return true;
        } else {
            return false;
        }
    }

    /* Check account */
    public function checkAccount($data = '', $type = '', $tbl = '', $id = 0)
    {
        $result = false;
        $row = array();

        if (!empty($data) && !empty($type) && !empty($tbl)) {

            $query = DB::table($tbl)->select('*')
                ->where($type, $data);
            if (!empty($id)) $query->where('id', '<>', $id);
            $row = $query->first();

            if (!empty($row)) {
                $result = true;
            }
        }

        return $result;
    }

    /* Check title */
    public function checkTitle($data = array())
    {

        $result = array();
        foreach (config('app.langs') as $k => $v) {
            if (isset($data['name' . $k])) {
                $title = trim($data['name' . $k]);

                if (empty($title)) {
                    $result[] = 'Tiêu đề (' . $v . ') không được trống';
                }
            }
        }

        return $result;
    }

    /* Check slug */
    // public function checkSlug($data = array())
    // {
    //     $result = 'valid';

    //     if (isset($data['slug'])) {
    //         $slug = trim($data['slug']);

    //         if (!empty($slug)) {
    //             $query = SlugModel::select('*')
    //                 ->where(function ($query) use ($slug) {
    //                     $query->where("slugvi", $slug)->orwhere("slugen", $slug)->orwhere("slugcn", $slug);
    //                 });

    //             if (!empty($data['id'])) $query->where('id_parent', '!=', $data['id']);
    //             $check = $query->first();

    //             if (!empty($check['id'])) {
    //                 $result = 'exist';
    //             }
    //         } else {
    //             $result = 'empty';
    //         }
    //     }
    //     return $result;
    // }


    public function checkSlug($data = array())
    {
        $result = 'valid';

        if (isset($data['slug'])) {
            $slug = trim($data['slug']);

            if (!empty($slug)) {
                $query = SlugModel::select('*')
                    ->where(function ($query) use ($slug) {
                        $first = true;
                        foreach (config('app.slugs') ?? [] as $k => $v) {
                            if ($first) {
                                $query->where("slug" . $k, $slug);
                                $first = false;
                            } else {
                                $query->orWhere("slug" . $k, $slug);
                            }
                        }
                    });

                if (!empty($data['id'])) $query->where('id_parent', '!=', $data['id']);
                $check = $query->first();

                if (!empty($check['id'])) {
                    $result = 'exist';
                }
            } else {
                $result = 'empty';
            }
        }
        return $result;
    }

    /* Check recaptcha */
    public function checkRecaptcha($response = '')
    {
        $result = null;
        $active = !empty(config('app.recaptcha.active')) ? true : false;
        if ($active == true && $response != '') {
            $recaptcha = file_get_contents(config('app.recaptcha.urlapi') . '?secret=' . config('app.recaptcha.secretkey') . '&response=' . $response);
            $recaptcha = json_decode($recaptcha);
            $result['score'] = $recaptcha->score;
            $result['action'] = $recaptcha->action;
        } else if (!$active) {
            $result['test'] = true;
        }
        return $result;
    }

    /* Lấy youtube */
    public function getYoutube($url = '')
    {
        if ($url != '') {
            $parts = parse_url($url);
            if (isset($parts['query'])) {
                parse_str($parts['query'], $qs);
                if (isset($qs['v'])) return $qs['v'];
                else if (isset($qs['vi'])) return $qs['vi'];
            }

            if (isset($parts['path'])) {
                $path = explode('/', trim($parts['path'], '/'));
                return $path[count($path) - 1];
            }
        }

        return false;
    }

    /* Get image */
    public function getImage($data = array())
    {
        global $config;

        /* Defaults */
        $defaults = [
            'class' => 'lazy',
            'id' => '',
            'isLazy' => true,
            'thumbs' => THUMBS,
            'isWatermark' => false,
            'watermark' => (defined('WATERMARK')) ? WATERMARK : '',
            'prefix' => '',
            'size-error' => '',
            'size-src' => '',
            'sizes' => '',
            'url' => '',
            'upload' => '',
            'image' => '',
            'upload-error' => 'assets/images/',
            'image-error' => 'noimage.png',
            'alt' => ''
        ];

        /* Data */
        $info = array_merge($defaults, $data);

        /* Upload - Image */
        if (empty($info['upload']) || empty($info['image'])) {
            $info['upload'] = $info['upload-error'];
            $info['image'] = $info['image-error'];
        }

        /* Size */
        if (!empty($info['sizes'])) {
            $info['size-error'] = $info['size-src'] = $info['sizes'];
        }

        /* Path origin */
        $info['pathOrigin'] = $info['upload'] . $info['image'];

        /* Path src */
        if (!empty($info['url'])) {
            $info['pathSrc'] = $info['url'];
        } else {
            if (!empty($info['size-src'])) {
                $info['pathSize'] = $info['size-src'] . "/" . $info['upload'] . $info['image'];
                $info['pathSrc'] = (!empty($info['isWatermark']) && !empty($info['prefix'])) ? ASSET . $info['watermark'] . "/" . $info['prefix'] . "/" . $info['pathSize'] : ASSET . $info['thumbs'] . "/" . $info['pathSize'];
            } else {
                $info['pathSrc'] = ASSET . $info['pathOrigin'];
            }
        }

        /* Path error */
        $info['pathError'] = ASSET . $info['thumbs'] . "/" . $info['size-error'] . "/" . $info['upload-error'] . $info['image-error'];

        /* Class */
        $info['class'] = (empty($info['isLazy'])) ? str_replace('lazy', '', $info['class']) : $info['class'];
        $info['class'] = (!empty($info['class'])) ? "class='" . $info['class'] . "'" : "";

        /* Id */
        $info['id'] = (!empty($info['id'])) ? "id='" . $info['id'] . "'" : "";

        /* Check to convert Webp */
        $info['hasURL'] = false;

        if (filter_var(str_replace(ASSET, "", $info['pathSrc']), FILTER_VALIDATE_URL)) {
            $info['hasURL'] = true;
        }
        /* Src */
        $info['src'] = (!empty($info['isLazy']) && strpos($info['class'], 'lazy') !== false) ? "data-src='" . $info['pathSrc'] . "'" : "src='" . $info['pathSrc'] . "'";

        /* Image */
        /* onerror=\"this.src='" . $info['pathError'] . "';\" */
        $result = "<img " . $info['class'] . " " . $info['id'] . " onerror=\"this.src='" . $info['pathError'] . "';\" " . $info['src'] . " alt='" . $info['alt'] . "'/>";

        return $result;
    }

    /* Get list gallery */
    public function listsGallery($file = '')
    {
        $result = array();

        if (!empty($file) && !empty($_POST['fileuploader-list-' . $file])) {
            $fileLists = '';
            $fileLists = str_replace('"', '', $_POST['fileuploader-list-' . $file]);
            $fileLists = str_replace('[', '', $fileLists);
            $fileLists = str_replace(']', '', $fileLists);
            $fileLists = str_replace('{', '', $fileLists);
            $fileLists = str_replace('}', '', $fileLists);
            $fileLists = str_replace('0:/', '', $fileLists);
            $fileLists = str_replace('file:', '', $fileLists);
            $result = explode(',', $fileLists);
        }

        return $result;
    }

    public function numberGallery($id = 0, $id_properties = '', $com = '', $type = '')
    {

        $gallery = GalleryModel::selectRaw('count(id) as numb')
            ->where('com', $com)
            ->where('type', $type)
            ->where('type_parent', $type)
            ->where('id_parent', $id)
            ->where('id_properties', $id_properties)
            ->first();

        return $gallery['numb'];
    }


    /* Generate hash */
    public function generateHash()
    {
        if (!$this->hash) {
            $this->hash = $this->stringRandom(10);
        }
        return $this->hash;
    }

    /* Lấy date */
    public function makeDate($time = 0, $dot = '.', $lang = 'vi', $f = false)
    {
        $str = ($lang == 'vi') ? date("d{$dot}m{$dot}Y", $time) : date("m{$dot}d{$dot}Y", $time);

        if ($f == true) {
            $thu['vi'] = array('Chủ nhật', 'Thứ hai', 'Thứ ba', 'Thứ tư', 'Thứ năm', 'Thứ sáu', 'Thứ bảy');
            $thu['en'] = array('Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday');
            $str = $thu[$lang][date('w', $time)] . ', ' . $str;
        }

        return $str;
    }

    /* Alert */
    public function alert($notify = '')
    {
        echo '<script language="javascript">alert("' . $notify . '")</script>';
    }

    /* Delete file */
    public function deleteFile($file = '')
    {

        return @unlink($file);
    }

    /* Transfer */
    public function transfer($msg = '', $page = '', $numb = true)
    {
        global $configBase;

        $basehref = $configBase;
        $showtext = $msg;
        $page_transfer = $page;
        $numb = $numb;

        include("./templates/layout/transfer.php");
        exit();
    }

    /* Redirect */

    public function redirect($url = '', $response = null)
    {
        \LARAVEL\Facade\EventHandler::dispatch(\LARAVEL\Core\Routing\EventHandler::EVENT_FINISH, ['url' => $url, 'response' => $response]);
        header("location:$url", true, $response);
        exit();
    }

    /* Dump */
    public function dump($value = '', $exit = false)
    {
        echo "<pre>";
        print_r($value);
        echo "</pre>";
        if ($exit) exit();
    }


    /* UTF8 convert */
    public function utf8Convert($str = '')
    {
        if ($str != '') {
            $utf8 = array(
                'a' => 'á|à|ả|ã|ạ|ă|ắ|ặ|ằ|ẳ|ẵ|â|ấ|ầ|ẩ|ẫ|ậ|Á|À|Ả|Ã|Ạ|Ă|Ắ|Ặ|Ằ|Ẳ|Ẵ|Â|Ấ|Ầ|Ẩ|Ẫ|Ậ',
                'd' => 'đ|Đ',
                'e' => 'é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ|É|È|Ẻ|Ẽ|Ẹ|Ê|Ế|Ề|Ể|Ễ|Ệ',
                'i' => 'í|ì|ỉ|ĩ|ị|Í|Ì|Ỉ|Ĩ|Ị',
                'o' => 'ó|ò|ỏ|õ|ọ|ô|ố|ồ|ổ|ỗ|ộ|ơ|ớ|ờ|ở|ỡ|ợ|Ó|Ò|Ỏ|Õ|Ọ|Ô|Ố|Ồ|Ổ|Ỗ|Ộ|Ơ|Ớ|Ờ|Ở|Ỡ|Ợ',
                'u' => 'ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ữ|ự|Ú|Ù|Ủ|Ũ|Ụ|Ư|Ứ|Ừ|Ử|Ữ|Ự',
                'y' => 'ý|ỳ|ỷ|ỹ|ỵ|Ý|Ỳ|Ỷ|Ỹ|Ỵ',
                '' => '`|\~|\!|\@|\#|\||\$|\%|\^|\&|\*|\(|\)|\+|\=|\,|\.|\/|\?|\>|\<|\'|\"|\“|\”|\:|\;|_',
            );

            foreach ($utf8 as $ascii => $uni) {
                $str = preg_replace("/($uni)/i", $ascii, $str);
            }
        }

        return $str;
    }

    /* Change title */

    public function changeTitle($text = '')
    {
        if ($text != '') {
            // Giữ nguyên các ký tự tiếng Trung và tiếng Anh
            $text = strtolower($this->utf8Convert($text));
            // Cho phép chữ cái tiếng Anh, số, dấu cách, ký tự Trung Quốc và dấu gạch ngang
            $text = preg_replace("/[^\\p{L}0-9-\s]/u", "", $text);
            $text = preg_replace('/([\s]+)/', '-', $text);
            $text = str_replace(array('%20', ' '), '-', $text);
            // Loại bỏ các dấu gạch ngang thừa
            $text = preg_replace("/-+/", "-", $text);
            $text = trim($text, '-'); // Loại bỏ dấu gạch ngang ở đầu và cuối
        }

        return $text;
    }

    public function changeTitle2($text = '')
    {
        if ($text != '') {
            $text = strtolower($this->utf8Convert($text));
            $text = preg_replace("/[^a-z0-9-\s]/", "", $text);
            $text = preg_replace('/([\s]+)/', '', $text);
            $text = str_replace(array('%20', ' '), '', $text);
            $text = preg_replace("/\-\-\-\-\-/", "", $text);
            $text = preg_replace("/\-\-\-\-/", "", $text);
            $text = preg_replace("/\-\-\-/", "", $text);
            $text = preg_replace("/\-\-/", "", $text);
            $text = '@' . $text . '@';
            $text = preg_replace('/\@\-|\-\@|\@/', '', $text);
        }

        return $text;
    }



    public function nameImg($string = '')
    {
        // Tách chuỗi tên file và phần mở rộng
        $pathInfo = pathinfo($string);

        $filename = $pathInfo['filename']; // Phần tên file
        $extension = $pathInfo['extension']; // Phần mở rộng

        // Xử lý tên file để thay thế các ký tự không hợp lệ
        $filename = $this->changeTitle($filename);

        // Trả về tên file mới
        return $filename . '-' . time() . '.' . $extension;
    }

    public function nameImgCrop($string = '')
    {
        $ext = explode('.', $string);
        $string = explode('-', $ext[0]);
        return $this->changeTitle($string[0]) . '-' . time() . '.' . end($ext);
    }

    /* Lấy IP */
    public function getRealIPAddress()
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }

    /* Lấy getPageURL */
    public function getPageURL()
    {
        $pageURL = 'http';
        if (array_key_exists('HTTPS', $_SERVER) && $_SERVER["HTTPS"] == "on") $pageURL .= "s";
        $pageURL .= "://";
        $pageURL .= $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
        return $pageURL;
    }

    /* Lấy getCurrentPageURL */
    public function getCurrentPageURL()
    {
        $pageURL = 'http';
        if (array_key_exists('HTTPS', $_SERVER) && $_SERVER["HTTPS"] == "on") $pageURL .= "s";
        $pageURL .= "://";
        $pageURL .= $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
        $urlpos = strpos($pageURL, "?p");
        $pageURL = ($urlpos) ? explode("?p=", $pageURL) : explode("&p=", $pageURL);
        return $pageURL[0];
    }

    /* Lấy getCurrentPageURL Cano */
    public function getCurrentPageURL_CANO()
    {
        $pageURL = 'http';
        if (array_key_exists('HTTPS', $_SERVER) && $_SERVER["HTTPS"] == "on") $pageURL .= "s";
        $pageURL .= "://";
        $pageURL .= $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
        $pageURL = str_replace("amp/", "", $pageURL);
        $urlpos = strpos($pageURL, "?p");
        $pageURL = ($urlpos) ? explode("?p=", $pageURL) : explode("&p=", $pageURL);
        $pageURL = explode("?", $pageURL[0]);
        $pageURL = explode("#", $pageURL[0]);
        $pageURL = explode("index", $pageURL[0]);
        return $pageURL[0];
    }

    /* Has file */
    public function hasFile($file)
    {
        if (isset($_FILES[$file])) {
            if ($_FILES[$file]['error'] == 4) {
                return false;
            } else if ($_FILES[$file]['error'] == 0) {
                return true;
            }
        } else {
            return false;
        }
    }

    /* Size file */
    public function sizeFile($file)
    {
        if ($this->hasFile($file)) {
            if ($_FILES[$file]['error'] == 0) {
                return $_FILES[$file]['size'];
            }
        } else {
            return 0;
        }
    }

    /* Check file */
    public function checkFile($file)
    {
        global $config;

        $result = true;

        if ($this->hasFile($file)) {
            if ($this->sizeFile($file) > config('type.max_size')) {
                $result = false;
            }
        }

        return $result;
    }

    /* Check extension file */
    public function checkExtFile($file)
    {
        global $config;

        $result = true;

        if ($this->hasFile($file)) {
            $ext = $this->infoPath($_FILES[$file]["name"], 'extension');

            if (!in_array($ext, config('type.array_video'))) {
                $result = false;
            }
        }

        return $result;
    }

    /* Info path */
    public function infoPath($filename = '', $type = '')
    {
        $result = '';

        if (!empty($filename) && !empty($type)) {
            if ($type == 'extension') {
                $result = pathinfo($filename, PATHINFO_EXTENSION);
            } else if ($type == 'filename') {
                $result = pathinfo($filename, PATHINFO_FILENAME);
            }
        }

        return $result;
    }

    /* Format bytes */
    public function formatBytes($size, $precision = 2)
    {
        $result = array();
        $base = log($size, 1024);
        $suffixes = array('', 'Kb', 'Mb', 'Gb', 'Tb');
        $result['numb'] = round(pow(1024, $base - floor($base)), $precision);
        $result['ext'] = $suffixes[floor($base)];

        return $result;
    }

    /* Copy image */
    public function copyImg($photo = '', $constant = '')
    {
        $str = '';

        if ($photo != '' && $constant != '') {
            $rand = rand(1000, 9999);
            $name = pathinfo($photo, PATHINFO_FILENAME);
            $ext = pathinfo($photo, PATHINFO_EXTENSION);
            $photo_new = $name . '-' . $rand . '.' . $ext;
            $oldpath = upload($constant, $photo, true);
            $newpath = upload($constant, $photo_new, true);

            if (file_exists($oldpath)) {
                if (copy($oldpath, $newpath)) {
                    $str = $photo_new;
                }
            }
        }

        return $str;
    }

    /* Get Img size */
    public function getImgSize($photo = '', $patch = '')
    {
        $array = array();
        if ($photo != '') {
            $x = (file_exists($patch)) ? getimagesize($patch) : null;
            $array = (!empty($x)) ? array("p" => $photo, "w" => $x[0], "h" => $x[1], "m" => $x['mime']) : null;
        }
        return $array;
    }

    /* Upload name */
    public function uploadName($name = '', $ext = '')
    {
        $result = '';

        if ($name != '') {
            $rand = rand(1000, 9999);
            $ten_anh = pathinfo($name, PATHINFO_FILENAME);
            $result = $this->changeTitle($ten_anh) . "-" . $rand . '.' . $ext;
        }

        return $result;
    }

    /* Resize images */
    public function smartResizeImage($file = '', $string = null, $width = 0, $height = 0, $proportional = false, $output = 'file', $delete_original = true, $use_linux_commands = false, $quality = 100, $grayscale = false)
    {
        if ($height <= 0 && $width <= 0) return false;
        if ($file === null && $string === null) return false;
        $info = $file !== null ? getimagesize($file) : getimagesizefromstring($string);
        $image = '';
        $final_width = 0;
        $final_height = 0;
        list($width_old, $height_old) = $info;
        $cropHeight = $cropWidth = 0;
        if ($proportional) {
            if ($width == 0) $factor = $height / $height_old;
            elseif ($height == 0) $factor = $width / $width_old;
            else $factor = min($width / $width_old, $height / $height_old);
            $final_width = round($width_old * $factor);
            $final_height = round($height_old * $factor);
        } else {
            $final_width = ($width <= 0) ? $width_old : $width;
            $final_height = ($height <= 0) ? $height_old : $height;
            $widthX = $width_old / $width;
            $heightX = $height_old / $height;
            $x = min($widthX, $heightX);
            $cropWidth = ($width_old - $width * $x) / 2;
            $cropHeight = ($height_old - $height * $x) / 2;
        }
        switch ($info[2]) {
            case IMAGETYPE_JPEG:
                $file !== null ? $image = imagecreatefromjpeg($file) : $image = imagecreatefromstring($string);
                break;
            case IMAGETYPE_GIF:
                $file !== null ? $image = imagecreatefromgif($file) : $image = imagecreatefromstring($string);
                break;
            case IMAGETYPE_PNG:
                $file !== null ? $image = imagecreatefrompng($file) : $image = imagecreatefromstring($string);
                break;
            default:
                return false;
        }
        if ($grayscale) {
            imagefilter($image, IMG_FILTER_GRAYSCALE);
        }
        $image_resized = imagecreatetruecolor($final_width, $final_height);
        if (($info[2] == IMAGETYPE_GIF) || ($info[2] == IMAGETYPE_PNG)) {
            $transparency = imagecolortransparent($image);
            $palletsize = imagecolorstotal($image);
            if ($transparency >= 0 && $transparency < $palletsize) {
                $transparent_color = imagecolorsforindex($image, $transparency);
                $transparency = imagecolorallocate($image_resized, $transparent_color['red'], $transparent_color['green'], $transparent_color['blue']);
                imagefill($image_resized, 0, 0, $transparency);
                imagecolortransparent($image_resized, $transparency);
            } elseif ($info[2] == IMAGETYPE_PNG) {
                imagealphablending($image_resized, false);
                $color = imagecolorallocatealpha($image_resized, 0, 0, 0, 127);
                imagefill($image_resized, 0, 0, $color);
                imagesavealpha($image_resized, true);
            }
        }
        imagecopyresampled($image_resized, $image, 0, 0, $cropWidth, $cropHeight, $final_width, $final_height, $width_old - 2 * $cropWidth, $height_old - 2 * $cropHeight);
        if ($delete_original) {
            if ($use_linux_commands) exec('rm ' . $file);
            else @unlink($file);
        }
        switch (strtolower($output)) {
            case 'browser':
                $mime = image_type_to_mime_type($info[2]);
                header("Content-type: $mime");
                $output = NULL;
                break;
            case 'file':
                $output = $file;
                break;
            case 'return':
                return $image_resized;
                break;
            default:
                break;
        }
        switch ($info[2]) {
            case IMAGETYPE_GIF:
                imagegif($image_resized, $output);
                break;
            case IMAGETYPE_JPEG:
                imagejpeg($image_resized, $output, $quality);
                break;
            case IMAGETYPE_PNG:
                $quality = 9 - (int)((0.9 * $quality) / 10.0);
                imagepng($image_resized, $output, $quality);
                break;
            default:
                return false;
        }
        return true;
    }

    /* Correct images orientation */
    public function correctImageOrientation($filename)
    {
        ini_set('memory_limit', '1024M');
        if (function_exists('exif_read_data')) {
            $exif = @exif_read_data($filename);
            if ($exif && isset($exif['Orientation'])) {
                $orientation = $exif['Orientation'];
                if ($orientation != 1) {
                    $img = imagecreatefromjpeg($filename);
                    $deg = 0;

                    switch ($orientation) {
                        case 3:
                            $image = imagerotate($img, 180, 0);
                            break;

                        case 6:
                            $image = imagerotate($img, -90, 0);
                            break;

                        case 8:
                            $image = imagerotate($img, 90, 0);
                            break;
                        default:
                            $image = $img;
                    }

                    imagejpeg($image, $filename, 90);
                }
            }
        }
    }

    /* Upload images */


    /* Delete folder */
    public function removeDir($dirname = '')
    {
        if (is_dir($dirname)) $dir_handle = opendir($dirname);
        if (!isset($dir_handle) || $dir_handle == false) return false;
        while ($file = readdir($dir_handle)) {
            if ($file != "." && $file != "..") {
                if (!is_dir($dirname . "/" . $file)) unlink($dirname . "/" . $file);
                else $this->removeDir($dirname . '/' . $file);
            }
        }
        closedir($dir_handle);
        rmdir($dirname);
        return true;
    }

    /* Remove Sub folder */
    public function RemoveEmptySubFolders($path = '')
    {
        $empty = true;

        foreach (glob($path . DIRECTORY_SEPARATOR . "*") as $file) {
            if (is_dir($file)) {
                if (!$this->RemoveEmptySubFolders($file)) $empty = false;
            } else {
                $empty = false;
            }
        }

        if ($empty) {
            if (is_dir($path)) {
                rmdir($path);
            }
        }

        return $empty;
    }

    /* Remove files from dir in x seconds */
    public function RemoveFilesFromDirInXSeconds($dir = '', $seconds = 3600)
    {
        $files = glob(rtrim($dir, '/') . "/*");
        $now = time();

        if ($files) {
            foreach ($files as $file) {
                $filename = basename($file);
                if (is_file($file) && $filename != 'index.txt') {
                    if ($now - filemtime($file) >= $seconds) {
                        unlink($file);
                    }
                } else {
                    $this->RemoveFilesFromDirInXSeconds($file, $seconds);
                }
            }
        }
    }

    /* Remove zero bytes */
    public function removeZeroByte($dir)
    {
        $files = glob(rtrim($dir, '/') . "/*");
        if ($files) {
            foreach ($files as $file) {
                $filename = basename($file);
                if (is_file($file) && $filename != 'index.txt') {
                    if (!filesize($file)) {
                        unlink($file);
                    }
                } else {
                    $this->removeZeroByte($file);
                }
            }
        }
    }

    public function createThumb($width_thumb = 0, $height_thumb = 0, $zoom_crop = '1', $src = '', $watermark = null, $path = THUMBS, $preview = false, $args = array(), $quality = 100)
    {
        $t = 3600 * 24 * 3;
        $this->RemoveFilesFromDirInXSeconds(UPLOAD_TEMP_L, 1);
        if ($watermark != null) {
            $this->RemoveFilesFromDirInXSeconds(WATERMARK . '/' . $path . "/", $t);
            $this->RemoveEmptySubFolders(WATERMARK . '/' . $path . "/");
        } else {
            $this->RemoveFilesFromDirInXSeconds($path . "/", $t);
            $this->RemoveEmptySubFolders($path . "/");
        }
        $src = str_replace("%20", " ", $src);
        if (!file_exists($src)) die("NO IMAGE $src");
        $image_url = $src;
        $origin_x = 0;
        $origin_y = 0;
        $new_width = $width_thumb;
        $new_height = $height_thumb;
        if ($new_width < 10 && $new_height < 10) {
            header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
            die("Width and height larger than 10px");
        }
        if ($new_width > 2000 || $new_height > 2000) {
            header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
            die("Width and height less than 2000px");
        }
        $array = getimagesize($image_url);
        if ($array) list($image_w, $image_h) = $array;
        else die("NO IMAGE $image_url");
        $width = $image_w;
        $height = $image_h;
        if ($new_height && !$new_width) $new_width = $width * ($new_height / $height);
        else if ($new_width && !$new_height) $new_height = $height * ($new_width / $width);
        $image_ext = explode('.', $image_url);
        $image_ext = trim(strtolower(end($image_ext)));
        $image_name = explode('/', $image_url);
        $image_name = trim(strtolower(end($image_name)));
        switch (strtoupper($image_ext)) {
            case 'WEBP':
                $image = imagecreatefromwebp($image_url);
                $func = 'imagejpeg';
                $mime_type = 'webp';
                break;
            case 'JPG':
            case 'JPEG':
                $image = imagecreatefromjpeg($image_url);
                $func = 'imagejpeg';
                $mime_type = 'jpeg';
                break;
            case 'PNG':
                $image = imagecreatefrompng($image_url);
                $func = 'imagepng';
                $mime_type = 'png';
                break;
            case 'GIF':
                $image = imagecreatefromgif($image_url);
                $func = 'imagegif';
                $mime_type = 'png';
                break;
            default:
                die("UNKNOWN IMAGE TYPE: $image_url");
        }
        $_new_width = $new_width;
        $_new_height = $new_height;
        if ($zoom_crop == 3) {
            $final_height = $height * ($new_width / $width);
            if ($final_height > $new_height) $new_width = $width * ($new_height / $height);
            else $new_height = $final_height;
        }
        $canvas = imagecreatetruecolor($new_width, $new_height);
        imagealphablending($canvas, false);
        $color = imagecolorallocatealpha($canvas, 255, 255, 255, 127);
        imagefill($canvas, 0, 0, $color);
        if ($zoom_crop == 2) {
            $final_height = $height * ($new_width / $width);
            if ($final_height > $new_height) {
                $origin_x = $new_width / 2;
                $new_width = $width * ($new_height / $height);
                $origin_x = round($origin_x - ($new_width / 2));
            } else {
                $origin_y = $new_height / 2;
                $new_height = $final_height;
                $origin_y = round($origin_y - ($new_height / 2));
            }
        }
        imagesavealpha($canvas, true);
        if ($zoom_crop > 0) {
            $align = '';
            $src_x = $src_y = 0;
            $src_w = $width;
            $src_h = $height;
            $cmp_x = $width / $new_width;
            $cmp_y = $height / $new_height;
            if ($cmp_x > $cmp_y) {
                $src_w = round($width / $cmp_x * $cmp_y);
                $src_x = round(($width - ($width / $cmp_x * $cmp_y)) / 2);
            } else if ($cmp_y > $cmp_x) {
                $src_h = round($height / $cmp_y * $cmp_x);
                $src_y = round(($height - ($height / $cmp_y * $cmp_x)) / 2);
            }
            if ($align) {
                if (strpos($align, 't') !== false) {
                    $src_y = 0;
                }
                if (strpos($align, 'b') !== false) {
                    $src_y = $height - $src_h;
                }
                if (strpos($align, 'l') !== false) {
                    $src_x = 0;
                }
                if (strpos($align, 'r') !== false) {
                    $src_x = $width - $src_w;
                }
            }
            imagecopyresampled($canvas, $image, $origin_x, $origin_y, $src_x, $src_y, $new_width, $new_height, $src_w, $src_h);
        } else {
            imagecopyresampled($canvas, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
        }
        if ($preview) {
            $watermark = array();
            $watermark['status'] = 'hienthi';
            $options = $args;
            $overlay_url = $args['watermark'];
        }
        $upload_dir = '';
        $folder_old = str_replace($image_name, '', $image_url);
        if (!empty($watermark['status']) && strpos('hienthi', $watermark['status']) !== false) {
            $upload_dir = WATERMARK . '/' . $path . '/' . $width_thumb . 'x' . $height_thumb . 'x' . $zoom_crop . '/' . $folder_old;
        } else {
            if ($watermark != null) $upload_dir = WATERMARK . '/' . $path . '/' . $width_thumb . 'x' . $height_thumb . 'x' . $zoom_crop . '/' . $folder_old;
            else $upload_dir = $path . '/' . $width_thumb . 'x' . $height_thumb . 'x' . $zoom_crop . '/' . $folder_old;
        }
        if (!file_exists($upload_dir)) if (!mkdir($upload_dir, 0777, true)) die('Failed to create folders...');
        if (!empty($watermark['status']) && strpos('hienthi', $watermark['status']) !== false) {
            $options = (isset($options)) ? $options : json_decode($watermark['options'], true)['watermark'];
            $per_scale = $options['per'];
            $per_small_scale = $options['small_per'];
            $max_width_w = $options['max'];
            $min_width_w = $options['min'];
            $opacity = @$options['opacity'];
            $overlay_url = (isset($overlay_url)) ? $overlay_url : UPLOAD_PHOTO_L . $watermark['photo'];
            $overlay_ext = explode('.', $overlay_url);
            $overlay_ext = trim(strtolower(end($overlay_ext)));
            switch (strtoupper($overlay_ext)) {
                case 'JPG':
                case 'JPEG':
                    $overlay_image = imagecreatefromjpeg($overlay_url);
                    break;
                case 'PNG':
                    $overlay_image = imagecreatefrompng($overlay_url);
                    break;
                case 'GIF':
                    $overlay_image = imagecreatefromgif($overlay_url);
                    break;
                default:
                    die("UNKNOWN IMAGE TYPE: $overlay_url");
            }
            //$this->filterOpacity($overlay_image,$opacity);
            $overlay_width = imagesx($overlay_image);
            $overlay_height = imagesy($overlay_image);
            $overlay_padding = 5;
            imagealphablending($canvas, true);
            if (min($_new_width, $_new_height) <= 300) $per_scale = $per_small_scale;
            $oz = max($overlay_width, $overlay_height);
            if ($overlay_width > $overlay_height) {
                $scale = $_new_width / $oz;
            } else {
                $scale = $_new_height / $oz;
            }
            if ($_new_height > $_new_width) {
                $scale = $_new_height / $oz;
            }
            $new_overlay_width = (floor($overlay_width * $scale) - $overlay_padding * 2) / $per_scale;
            $new_overlay_height = (floor($overlay_height * $scale) - $overlay_padding * 2) / $per_scale;
            $scale_w = $new_overlay_width / $new_overlay_height;
            $scale_h = $new_overlay_height / $new_overlay_width;
            $new_overlay_height = $new_overlay_width / $scale_w;
            if ($new_overlay_height > $_new_height) {
                $new_overlay_height = $_new_height / $per_scale;
                $new_overlay_width = $new_overlay_height * $scale_w;
            }
            if ($new_overlay_width > $_new_width) {
                $new_overlay_width = $_new_width / $per_scale;
                $new_overlay_height = $new_overlay_width * $scale_h;
            }
            if (($_new_width / $new_overlay_width) < $per_scale) {
                $new_overlay_width = $_new_width / $per_scale;
                $new_overlay_height = $new_overlay_width * $scale_h;
            }
            if ($_new_height < $_new_width && ($_new_height / $new_overlay_height) < $per_scale) {
                $new_overlay_height = $_new_height / $per_scale;
                $new_overlay_width = $new_overlay_height / $scale_h;
            }
            if ($new_overlay_width > $max_width_w && $new_overlay_width) {
                $new_overlay_width = $max_width_w;
                $new_overlay_height = $new_overlay_width * $scale_h;
            }
            if ($new_overlay_width < $min_width_w && $_new_width <= $min_width_w * 3) {
                $new_overlay_width = $min_width_w;
                $new_overlay_height = $new_overlay_width * $scale_h;
            }
            $new_overlay_width = round($new_overlay_width);
            $new_overlay_height = round($new_overlay_height);
            switch ($options['position']) {
                case 1:
                    $khoancachx = $overlay_padding;
                    $khoancachy = $overlay_padding;
                    break;
                case 2:
                    $khoancachx = abs($_new_width - $new_overlay_width) / 2;
                    $khoancachy = $overlay_padding;
                    break;
                case 3:
                    $khoancachx = abs($_new_width - $new_overlay_width) - $overlay_padding;
                    $khoancachy = $overlay_padding;
                    break;
                case 4:
                    $khoancachx = abs($_new_width - $new_overlay_width) - $overlay_padding;
                    $khoancachy = abs($_new_height - $new_overlay_height) / 2;
                    break;
                case 5:
                    $khoancachx = abs($_new_width - $new_overlay_width) - $overlay_padding;
                    $khoancachy = abs($_new_height - $new_overlay_height) - $overlay_padding;
                    break;
                case 6:
                    $khoancachx = abs($_new_width - $new_overlay_width) / 2;
                    $khoancachy = abs($_new_height - $new_overlay_height) - $overlay_padding;
                    break;
                case 7:
                    $khoancachx = $overlay_padding;
                    $khoancachy = abs($_new_height - $new_overlay_height) - $overlay_padding;
                    break;
                case 8:
                    $khoancachx = $overlay_padding;
                    $khoancachy = abs($_new_height - $new_overlay_height) / 2;
                    break;
                case 9:
                    $khoancachx = abs($_new_width - $new_overlay_width) / 2;
                    $khoancachy = abs($_new_height - $new_overlay_height) / 2;
                    break;
                default:
                    $khoancachx = $overlay_padding;
                    $khoancachy = $overlay_padding;
                    break;
            }
            $overlay_new_image = imagecreatetruecolor($new_overlay_width, $new_overlay_height);
            imagealphablending($overlay_new_image, false);
            imagesavealpha($overlay_new_image, true);
            imagecopyresampled($overlay_new_image, $overlay_image, 0, 0, 0, 0, $new_overlay_width, $new_overlay_height, $overlay_width, $overlay_height);
            imagecopy($canvas, $overlay_new_image, $khoancachx, $khoancachy, 0, 0, $new_overlay_width, $new_overlay_height);
            imagealphablending($canvas, false);
            imagesavealpha($canvas, true);
        }
        if ($preview) {
            $upload_dir = '';
            $this->RemoveEmptySubFolders(WATERMARK . '/' . $path . "/");
        }
        if ($upload_dir) {
            if ($func == 'imagejpeg') $func($canvas, $upload_dir . $image_name, 100);
            else $func($canvas, $upload_dir . $image_name, floor($quality * 0.09));
        }
        header('Content-Type: image/' . $mime_type);
        if ($func == 'imagejpeg') $func($canvas, NULL, 100);
        else $func($canvas, NULL, floor($quality * 0.09));
        imagedestroy($canvas);
    }

    /* String random */
    public function stringRandom($sokytu = 10)
    {
        $str = '';

        if ($sokytu > 0) {
            $chuoi = 'ABCDEFGHIJKLMNOPQRSTUVWXYZWabcdefghijklmnopqrstuvwxyzw0123456789';
            for ($i = 0; $i < $sokytu; $i++) {
                $vitri = mt_rand(0, strlen($chuoi));
                $str = $str . substr($chuoi, $vitri, 1);
            }
        }

        return $str;
    }

    /* Digital random */
    public function digitalRandom($min = 1, $max = 10, $num = 10)
    {
        $result = '';

        if ($num > 0) {
            for ($i = 0; $i < $num; $i++) {
                $result .= rand($min, $max);
            }
        }

        return $result;
    }



    /* Join column */
    public function joinCols($array = null, $column = null)
    {
        $str = '';
        $arrayTemp = array();

        if ($array && $column) {
            foreach ((array)json_decode($array, true) as $k => $v) {

                if (!empty($v[$column])) {
                    $arrayTemp[] = $v[$column];
                }
            }

            if (!empty($arrayTemp)) {
                $arrayTemp = array_unique($arrayTemp);
                $str = implode(",", $arrayTemp);
            }
        }

        return $str;
    }



    /* Product Seen */
    public function product_seen_exists($id = 0)
    {
        (!isset($_SESSION['pro_seen'])) ? $_SESSION['pro_seen'] = array() : "";
        if (!in_array($id, $_SESSION['pro_seen']))
            $_SESSION['pro_seen'][count($_SESSION['pro_seen'])] = $id;
    }
    /* End Product Seen */


    public function getLinkCategory($table = '', $level = '', $type = '', $title_select = '')
    {

        $id_parent = 'id_' . $level;

        if ($level == 'cat') {
            $idlist = (isset(Request()->id_list)) ? htmlspecialchars(Request()->id_list) : 0;
        } else if ($level == 'item') {
            $idlist = (isset(Request()->id_list)) ? htmlspecialchars(Request()->id_list) : 0;
            $idcat = (isset(Request()->id_cat)) ? htmlspecialchars(Request()->id_cat) : 0;
        } else if ($level == 'sub') {
            $idlist = (isset(Request()->id_list)) ? htmlspecialchars(Request()->id_list) : 0;
            $idcat = (isset(Request()->id_cat)) ? htmlspecialchars(Request()->id_cat) : 0;
            $iditem = (isset(Request()->id_item)) ? htmlspecialchars(Request()->id_item) : 0;
        } else if ($level == 'district') {
            $idcity = (isset(Request()->id_city)) ? htmlspecialchars(Request()->id_city) : 0;
        } else if ($level == 'ward') {
            $idcity = (isset(Request()->id_city)) ? htmlspecialchars(Request()->id_city) : 0;
            $iddistrict = (isset(Request()->id_district)) ? htmlspecialchars(Request()->id_district) : 0;
        }


        $query = DB::table($table)->select('namevi', 'id')
            ->where('type', $type);
        if (!empty($idlist)) $query->where('id_list', $idlist);
        if (!empty($idcat)) $query->where('id_cat', $idcat);
        if (!empty($iditem)) $query->where('id_item', $iditem);
        if (!empty($idcity)) $query->where('id_city', $idcity);
        if (!empty($iddistrict)) $query->where('id_district', $iddistrict);
        $rows = $query->get();

        $str = '<select id="' . $id_parent . '" name="' . $id_parent . '" onchange="onchangeCategory($(this))" class="form-control filter-category select2"><option value="0">' . $title_select . '</option>';
        if (!empty($rows)) {
            foreach ((array)json_decode($rows, true) as $v) {
                if (isset(Request()->$id_parent) && ($v["id"] == (int)Request()->$id_parent)) $selected = "selected";
                else $selected = "";
                $str .= '<option value=' . $v["id"] . ' ' . $selected . '>' . $v["namevi"] . '</option>';
            }
        }
        $str .= '</select>';
        return $str;
    }


    public function getAjaxCategory($table = '', $table_child = '', $level = '', $type = '', $title_select = 'Chọn danh mục', $class_select = 'select-category')
    {
        $id_parent = 'id_' . $level;
        $data_level = '';
        $data_type = 'data-type="' . $type . '"';
        $data_table = '';
        $data_child = '';

        if ($level == 'list') {
            $data_level = 'data-level="0"';
            $data_table = 'data-table="' . $table_child . '"';
            $data_child = 'data-child="id_cat"';
        } else if ($level == 'cat') {
            $data_level = 'data-level="1"';
            $data_table = 'data-table="' . $table_child . '"';
            $data_child = 'data-child="id_item"';
            $idlist = (isset(Request()->id_list)) ? htmlspecialchars(Request()->id_list) : 0;
        } else if ($level == 'item') {
            $data_level = 'data-level="2"';
            $data_table = 'data-table="' . $table_child . '"';
            $data_child = 'data-child="id_sub"';

            $idlist = (isset(Request()->id_list)) ? htmlspecialchars(Request()->id_list) : 0;
            $idcat = (isset(Request()->id_cat)) ? htmlspecialchars(Request()->id_cat) : 0;
        } else if ($level == 'sub') {
            $data_level = '';
            $data_type = '';
            $class_select = '';
            $idlist = (isset(Request()->id_list)) ? htmlspecialchars(Request()->id_list) : 0;
            $idcat = (isset(Request()->id_cat)) ? htmlspecialchars(Request()->id_cat) : 0;
            $iditem = (isset(Request()->id_item)) ? htmlspecialchars(Request()->id_item) : 0;
        } else if ($level == 'brand') {
            $data_level = '';
            $data_type = '';
            $class_select = '';
        } else if ($level == 'city') {
            $data_level = 'data-level="0"';
            $data_table = 'data-table="' . $table_child . '"';
            $data_child = 'data-child="id_district"';
        } else if ($level == 'district') {
            $data_level = 'data-level="1"';
            $data_table = 'data-table="' . $table_child . '"';
            $data_child = 'data-child="id_ward"';
            $idcity = (isset(Request()->id_city)) ? htmlspecialchars(Request()->id_city) : 0;
        }

        $query = DB::table($table)->select('namevi', 'id')
            ->where('type', $type);
        if (!empty($idlist)) $query->where('id_list', $idlist);
        if (!empty($idcat)) $query->where('id_cat', $idcat);
        if (!empty($iditem)) $query->where('id_item', $iditem);
        if (!empty($idcity)) $query->where('id_city', $idcity);
        if (!empty($iddistrict)) $query->where('id_district', $iddistrict);
        $rows = $query->get();

        $str = '<select id="' . $id_parent . '" name="data[' . $id_parent . ']" data-url="category" ' . $data_level . ' ' . $data_type . ' ' . $data_table . ' ' . $data_child . ' class="form-control select2 ' . $class_select . '"><option value="0">' . $title_select . '</option>';
        foreach ((array)json_decode($rows, true) as $v) {
            if (request()->$id_parent && ($v["id"] == (int)request()->$id_parent)) $selected = "selected";
            else $selected = "";

            $str .= '<option value=' . $v["id"] . ' ' . $selected . '>' . $v["namevi"] . '</option>';
        }
        $str .= '</select>';

        return $str;
    }

    public function getAjaxCategoryGroup($table = '', $table_child = '', $level = '', $type = '', $title_select = 'Chọn danh mục', $class_select = 'select-category')
    {
        $id_parent = 'id_' . $level;
        $check = !empty(request()->$id_parent) ?  explode(',', request()->$id_parent) : [];
        $data_level = '';
        $data_type = 'data-type="' . $type . '"';
        $data_table = '';
        $data_child = '';
        if ($level == 'list') {
            $data_level = 'data-level="0"';
            $data_table = 'data-table="' . $table_child . '"';
            $data_child = 'data-child="id_cat"';
            $tableParent = '';
            $id_temp = '';
        } else if ($level == 'cat') {
            $data_level = 'data-level="1"';
            $data_table = 'data-table="' . $table_child . '"';
            $data_child = 'data-child="id_item"';
            $tableParent = "product_list";
            $id_temp = "id_list";
            $idlist = (isset(Request()->id_list)) ? Request()->id_list : 0;
        } else if ($level == 'item') {
            $data_level = 'data-level="2"';
            $data_table = 'data-table="' . $table_child . '"';
            $data_child = 'data-child="id_sub"';
            $tableParent = "product_cat";
            $id_temp = "id_cat";

            $idcat = (isset(Request()->id_cat)) ? Request()->id_cat : 0;
        } else if ($level == 'sub') {
            $data_level = '';
            $data_type = '';
            $class_select = '';
            $tableParent = "product_item";
            $id_temp = "id_item";

            $iditem = (isset(Request()->id_item)) ? Request()->id_item : 0;
        } else if ($level == 'brand') {
            $tableParent = '';
            $data_level = '';
            $data_type = '';
            $class_select = '';
            $id_temp = '';
        }


        if (!empty($tableParent)) {
            $query = DB::table($tableParent)->select('namevi', 'id')
                ->where('type', $type);
            if (!empty($idlist)) $query->whereIn('id', explode(',', $idlist));
            if (!empty($idcat)) $query->whereIn('id', explode(',', $idcat));
            if (!empty($iditem)) $query->whereIn('id', explode(',', $iditem));
            $rows = $query->get()->map(function ($v, $k) use ($table, $id_temp) {
                $v->cats = DB::table($table)->select('namevi', 'id')->where($id_temp, $v->id)->get();
                return $v;
            });

            $str = '<select id="' . $id_parent . '" name="data[' . $id_parent . '][]"  data-url="category-group" ' . $data_level . ' ' . $data_type . ' ' . $data_table . ' ' . $data_child . ' class="form-control select2 selectGroup ' . $class_select . '" multiple="multiple">';
            foreach ((array)json_decode($rows, true) as $v) {
                if (!empty($v['cats'])) {
                    $str .= '<optgroup label="' . $v['namevi'] . '">';
                    foreach ($v['cats'] as $value) {
                        if (request()->$id_parent && (in_array($value["id"], $check))) $selected = "selected";
                        else $selected = "";
                        $str .= '<option value=' . $value["id"] . ' ' . $selected . ' >' . $value["namevi"] . '</option>';
                    }
                    $str .= '</optgroup>';
                }
            }
            $str .= '</select>';
        } else {
            $query = DB::table($table)->select('namevi', 'id')
                ->where('type', $type);
            if (!empty($idlist)) $query->whereIn('id_list', [$idlist]);
            $rows = $query->get();

            $str = '<select id="' . $id_parent . '" name="data[' . $id_parent . '][]"  data-url="category-group" ' . $data_level . ' ' . $data_type . ' ' . $data_table . ' ' . $data_child . ' class="form-control select2 selectGroup ' . $class_select . '" multiple="multiple">';
            foreach ((array)json_decode($rows, true) as $v) {
                if (request()->$id_parent && (in_array($v["id"], $check))) $selected = "selected";
                else $selected = "";

                $str .= '<option value=' . $v["id"] . ' ' . $selected . '>' . $v["namevi"] . '</option>';
            }
            $str .= '</select>';
        }

        return $str;
    }

    public function getTags($id = 0, $element = '', $table = '', $type = '')
    {
        if ($id) {
            $temps = DB::table($table)->select('id_tags')
                ->where('id_parent', $id)
                ->where('type', $type)
                ->get();
            $temps = (!empty($temps)) ? $this->joinCols($temps, 'id_tags') : array();
            $temps = (!empty($temps)) ? explode(",", $temps) : array();
        }

        $row_tags = TagsModel::select('namevi', 'id')
            ->where('type', $type)
            ->get();

        $str = '<select id="' . $element . '" name="' . $element . '[]" class="form-control select2" multiple="multiple" >';
        for ($i = 0; $i < count($row_tags); $i++) {
            if (!empty($temps)) {
                if (in_array($row_tags[$i]['id'], $temps)) $selected = 'selected="selected"';
                else $selected = '';
            } else {
                $selected = '';
            }
            $str .= '<option value="' . $row_tags[$i]["id"] . '" ' . $selected . ' /> ' . $row_tags[$i]["namevi"] . '</option>';
        }
        $str .= '</select>';

        return $str;
    }

    public function getSelect($id = 0, $table = '', $type = '', $element = '')
    {
        $temps = (!empty($id)) ? explode(",", $id) : array();
        $row = DB::table($table)->select('namevi', 'id')
            ->where('type', $type)
            ->get();

        $str = '<select id="' . $element . '" name="' . $element . '[]" class="select multiselect select2" multiple="multiple" >';

        foreach ((array)json_decode($row, true) as $v) {
            if (!empty($temps)) {
                if (in_array($v['id'], $temps)) $selected = 'selected="selected"';
                else $selected = '';
            } else {
                $selected = '';
            }
            $str .= '<option value="' . $v["id"] . '" ' . $selected . ' /> ' . $v["namevi"] . '</option>';
        }
        $str .= '</select>';
        return $str;
    }

    public function getProperties($id_properties = '', $idl = 0, $table = '',  $type = '', $element = '')
    {
        $temps = (!empty($id_properties)) ? explode(',', $id_properties) : array();
        $row = DB::table($table)->select('namevi', 'id')
            ->where('type', $type)
            ->where('id_list', $idl)
            ->get();
        $str = '<select name="' . $element . '[' . $idl . '][]" class="form-control select2 ' . $element . '" multiple="multiple"  onchange="cardProperties()" >';

        foreach ((array)json_decode($row, true) as $v) {
            if (!empty($temps)) {
                if (in_array($v['id'], $temps)) $selected = 'selected="selected"';
                else $selected = '';
            } else {
                $selected = '';
            }
            $str .= '<option value="' . $v["id"] . '" ' . $selected . ' /> ' . $v["namevi"] . '</option>';
        }
        $str .= '</select>';
        return $str;
    }

    public function nameProper($array = array())
    {
        $name = '';
        foreach ($array as $k => $v) {
            $row = PropertiesModel::select('namevi')
                ->where('id', $v)
                ->first();
            $name .= $row['namevi'];
            $name .= ($k == count($array) - 1) ? '' : '--';
        }
        return $name;
    }

    public function setting($type = '')
    {
        $row = SettingModel::select('*')
            ->where('type', 'cau-hinh')
            ->first();
        if (!empty($type)) {
            return $row[$type];
        } else {
            return $row;
        }
    }

    public function showName($table = 0, $id = 0, $field = '')
    {
        $row = DB::table($table)->select($field)
            ->where('id', $id)
            ->first();
        if (!empty($row)) {
            return $row->$field;
        } else {
            return 'Chưa cập nhật';
        }
    }

    public function orderStatus($status = 0)
    {
        $row = OrderStatusModel::select('id', 'namevi')
            ->get();

        $str = '<select id="order_status" name="data[order_status]" class="select2 form-select form-select-lg"><option value="0">Chọn tình trạng</option>';
        foreach ($row as $v) {
            if (isset(Request()->order_status) && ($v["id"] == (int)Request()->order_status) || ($v["id"] == $status)) $selected = "selected";
            else $selected = "";

            $str .= '<option value=' . $v["id"] . ' ' . $selected . '>' . $v["namevi"] . '</option>';
        }
        $str .= '</select>';

        return $str;
    }

    function orderPayments()
    {
        $row = NewsModel::select('id', 'namevi')
            ->where('type', 'hinh-thuc-thanh-toan')
            ->get();

        $str = '<select id="order_payment" name="order_payment" class="select2 form-select form-select-lg"><option value="0">Chọn hình thức thanh toán</option>';
        foreach ($row as $v) {
            if (isset(Request()->order_payment) && ($v["id"] == (int)Request()->order_payment)) $selected = "selected";
            else $selected = "";
            $str .= '<option value=' . $v["id"] . ' ' . $selected . '>' . $v["namevi"] . '</option>';
        }
        $str .= '</select>';

        return $str;
    }


    public function getAjaxPlace($table = '', $title_select = 'Chọn danh mục')
    {

        $id_parent = 'id_' . $table;
        $data_level = '';
        $data_table = '';
        $data_child = '';
        $idcity = 0;
        $iddistrict = 0;

        if ($table == 'city') {
            $data_level = 'data-level="0"';
            $data_table = 'data-table="ward"';
            $data_child = 'data-child="id_ward"';
        } else if ($table == 'district') {
            $data_level = 'data-level="1"';
            $data_table = 'data-table="ward"';
            $data_child = 'data-child="id_ward"';
            $idcity = (isset(Request()->id_city)) ? htmlspecialchars(Request()->id_city) : 0;
        } else if ($table == 'ward') {
            $data_level = '';
            $data_table = '';
            $data_child = '';
            $idcity = (isset(Request()->id_city)) ? htmlspecialchars(Request()->id_city) : 0;
            $iddistrict = (isset(Request()->id_district)) ? htmlspecialchars(Request()->id_district) : 0;
        }

        $query = DB::table($table)->select('namevi', 'id')
            ->where('id', '<>', 0);
        if (!empty($idcity)) $query->where('id_city', $idcity);
        if (!empty($iddistrict)) $query->where('id_district', $iddistrict);
        $rows = $query->limit(200)->get();

        $str = '<select id="' . $id_parent . '" name="data[' . $id_parent . ']" ' . $data_level . ' ' . $data_table . ' ' . $data_child . ' class="form-control select2 select-place"><option value="0">' . $title_select . '</option>';
        foreach ((array)json_decode($rows, true) as $v) {
            if (isset(Request()->$id_parent) && ($v["id"] == (int)Request()->$id_parent)) $selected = "selected";
            else $selected = "";

            $str .= '<option value=' . $v["id"] . ' ' . $selected . '>' . $v["namevi"] . '</option>';
        }
        $str .= '</select>';

        return $str;
    }

    public function browser($browser = '', $sum = 0, $number = 0)
    {
        $numb = 0;
        $numb = round(($number / $sum) * 100, 2);
        return match ($browser) {
            'chrome' => [
                'name' => 'Google Chrome',
                'img' => 'chrome',
                'numb' => $numb
            ],
            'opera' => [
                'name' => 'Opera',
                'img' => 'opera',
                'numb' => $numb
            ],
            'opera_mini' => [
                'name' => 'Opera Mini',
                'img' => 'opera_mini',
                'numb' => $numb
            ],
            'edge' => [
                'name' => 'Microsoft Edge',
                'img' => 'edge',
                'numb' => $numb
            ],
            'coc_coc' => [
                'name' => 'Cốc Cốc',
                'img' => 'coc_coc',
                'numb' => $numb
            ],
            'ucbrowser' => [
                'name' => 'UCBrowser',
                'img' => 'ucbrowser',
                'numb' => $numb
            ],
            'vivaldi' => [
                'name' => 'Vivaldi',
                'img' => 'vivaldi',
                'numb' => $numb
            ],
            'firefox' => [
                'name' => 'Mozilla Firefox',
                'img' => 'firefox',
                'numb' => $numb
            ],
            'safari' => [
                'name' => 'Safari',
                'img' => 'safari',
                'numb' => $numb
            ],
            'ie' => [
                'name' => 'IE',
                'img' => 'ie',
                'numb' => $numb
            ],
            'netscape' => [
                'name' => 'Netscape',
                'img' => 'netscape',
                'numb' => $numb
            ],
            'mozilla' => [
                'name' => 'Mozilla',
                'img' => 'mozilla',
                'numb' => $numb
            ],
            default => []
        };
    }

    public function device($device = '', $sum = 0, $number = 0)
    {
        $numb = 0;

        $numb = round(($number / $sum) * 100, 2);
        return match ($device) {
            'desktop' => [
                'name' => 'Desktop',
                'img' => 'desktop',
                'numb' => $numb
            ],
            'phone' => [
                'name' => 'Mobile',
                'img' => 'phone',
                'numb' => $numb
            ],
            'tablet' => [
                'name' => 'Tablet',
                'img' => 'tablet',
                'numb' => $numb
            ],
            default => []
        };
    }
    public function checkShowNews($news)
    {
        if (!empty($news)) {
            foreach ($news as $key => $value) {
                if (empty($value['dropdown'])) {
                    return 1;
                }
            }
        }
        return 0;
    }

    public function contentHtml($content = '')
    {
        $dom = new HTML5DOMDocument();
        $html = '';
        $html = $this->decodeHtmlChars($content);
        $dom->loadHTML($html);
        $links = $dom->getElementsByTagName('a');
        // $links['img'] = $dom->getElementsByTagName('img');
        return $links;
    }

    public function updatetHtml($id = 0, $link = '')
    {
        $dom = new HTML5DOMDocument();
        $html = '';
        $check = LinkModel::select('*')
            ->where('id', $id)
            ->first();

        if (!empty($check)) {
            $row = DB::table($check['table'])->select('*')
                ->where('id', $check['id_parent'])
                ->first();
            $field = $check['field'];
            $href = $check['link'];
            if (!empty($row)) {
                if ($check['type_link'] == 'href') {
                    $html = $this->decodeHtmlChars($row->{$field});
                    $dom->loadHTML($html);
                    $links = $dom->getElementsByTagName('a');
                    foreach ($links as $link) {
                        $text = $link->nodeValue;
                        if (empty($text)) {
                            $text = $link->querySelector('img');
                        }
                        if ($check['content'] == $text && $link) {
                            $link->setAttribute('href', $href);
                        }
                    }
                } else {
                    $html = $this->decodeHtmlChars($row->{$field});
                    $dom->loadHTML($html);
                    $links = $dom->getElementsByTagName('img');
                    foreach ($links as $link) {
                        $text = $link;
                        if ($check['content'] == $text && $link) {
                            $link->setAttribute('src', $href);
                        }
                    }
                }
            }
            $data[$field] = $dom->saveHTML();
            DB::table($check['table'])->where('id', $row->id)->update($data);
        }
    }

    public function updatetContent($id = 0)
    {
        $check = LinkModel::select('*')
            ->where('id', $id)
            ->first();
        if (!empty($check)) {
            $html = $this->decodeHtmlChars($check['content']);
            $new_src = $check['link'];


            $dom = new HTML5DOMDocument();
            $dom->loadHTML($html);

            // Tìm tất cả thẻ img
            $images = $dom->getElementsByTagName('img');

            if ($images->length > 0) {
                $images->item(0)->setAttribute('src', $new_src);
            }
            $html = $dom->saveHTML();
            $dom->loadHTML($html);
            $data['content'] = $dom->getElementsByTagName('img')->item(0);

            LinkModel::where('id', $id)->update($data);
        }
    }


    public function checkWebsiteStatus($url)
    {

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HEADER => true,  // Bao gồm cả phần header trong phản hồi
            CURLOPT_NOBODY => false, // Đảm bảo lấy cả phần body
            CURLOPT_HTTPHEADER => array(),
        ));

        $response = curl_exec($curl);

        // Lấy mã trạng thái HTTP
        $http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        // Đóng phiên cURL
        curl_close($curl);

        if ($http_status) {
            if ($http_status == 200) {
                return '<p class="link-success">' . $http_status . ' OK</p>';
            } else {
                return '<p class="link-error">' . $http_status . ' ERROR</p>';
            }
        } else {
            return '<p class="link-error">' . $http_status . ' ERROR</p>';
        }
    }
}
