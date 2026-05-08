<?php


namespace LARAVEL\Controllers\Web;

use Carbon\Carbon;
use Illuminate\Http\Request;
use LARAVEL\Controllers\Controller;
use LARAVEL\Models\PhotoModel;
use LARAVEL\Models\NewsModel;
use LARAVEL\Models\ProductModel;
use LARAVEL\Models\ProductListModel;
use LARAVEL\Models\ProductCatModel;
use LARAVEL\Models\SettingModel;
use LARAVEL\Models\StaticModel;
use LARAVEL\Models\TagsModel;
use LARAVEL\Models\VoucherModel;
use LARAVEL\Core\Support\Facades\View;
use LARAVEL\Core\Support\Facades\Email;
use LARAVEL\Core\Support\Facades\Func;
use LARAVEL\Models\NewslettersModel;


class HomeController extends Controller
{
    public function index(Request $request)
    {
        $home = remember('home', 86400, function () {

            $productNew = ProductModel::select('name' . $this->lang, 'desc' . $this->lang, 'slug' . $this->lang,  'regular_price', 'sale_price', 'discount', 'id', 'photo', 'type')
                ->with(['getPhotos' => function ($query) {
                    $query->where('type', 'san-pham')->orderBy('numb', 'asc');
                }])
                ->where('type', 'san-pham')
                ->whereRaw("FIND_IN_SET(?,status)", ['hienthi'])
                ->whereRaw("FIND_IN_SET(?,status)", ['moi'])
                ->orderBy('numb', 'asc')
                ->orderBy('id', 'desc')
                ->get();

            $tags = TagsModel::select('name' . $this->lang, 'slug' . $this->lang,  'id', 'type')
                ->where('type', 'san-pham')
                ->whereRaw("FIND_IN_SET(?,status)", ['hienthi'])
                ->orderBy('numb', 'asc')
                ->orderBy('id', 'desc')
                ->get();

            $about = staticModel::select('name' . $this->lang, 'desc' . $this->lang, 'slug' . $this->lang,  'id', 'type', 'photo')
                ->where('type', 'gioi-thieu')
                ->whereRaw("FIND_IN_SET(?,status)", ['hienthi'])
                ->first();

            $slider =  PhotoModel::select('name' . $this->lang, 'photo', 'link', 'type')
                ->where('type', 'slide')
                ->whereRaw("FIND_IN_SET(?,status)", ['hienthi'])
                ->orderBy('numb', 'asc')
                ->orderBy('id', 'desc')
                ->get();

            $partner =  PhotoModel::select('name' . $this->lang, 'photo', 'link', 'type')
                ->where('type', 'doi-tac')
                ->whereRaw("FIND_IN_SET(?,status)", ['hienthi'])
                ->orderBy('numb', 'asc')
                ->orderBy('id', 'desc')
                ->get();

            $criteria =  PhotoModel::select('name' . $this->lang, 'photo', 'desc' . $this->lang, 'type')
                ->where('type', 'tieu-chi')
                ->whereRaw("FIND_IN_SET(?,status)", ['hienthi'])
                ->orderBy('numb', 'asc')
                ->orderBy('id', 'desc')
                ->get();

            $article =  NewsModel::select('name' . $this->lang, 'photo', 'desc' . $this->lang, 'id', 'slug' . $this->lang,  'type', 'status', 'link', 'type', 'created_at', 'updated_at')
                ->whereIn('type', ['tin-tuc', 'video'])
                ->whereRaw("FIND_IN_SET(?, status)", ['hienthi'])
                ->orderBy('numb', 'asc')
                ->orderBy('id', 'desc')
                ->get();

            $blog = $article?->filter(function ($item) {
                return $item->type === 'tin-tuc' && in_array('noibat', explode(',', $item->status));
            });


            $productList = ProductListModel::select('name' . $this->lang, 'desc' . $this->lang, 'slug' . $this->lang,  'id', 'photo', 'type')
                ->where('type', 'san-pham')
                ->whereRaw("FIND_IN_SET(?,status)", ['hienthi'])
                ->whereRaw("FIND_IN_SET(?,status)", ['noibat'])
                ->with([
                    'getCategoryCats' => function ($query) {
                        $query->whereRaw("FIND_IN_SET(?,status)", ['hienthi']);
                        $query->whereRaw("FIND_IN_SET(?,status)", ['noibat']);
                    }
                ])
                ->orderBy('numb', 'asc')
                ->orderBy('id', 'desc')
                ->get();

            $productcat = ProductCatModel::select('name' . $this->lang, 'desc' . $this->lang, 'slug' . $this->lang,  'id', 'photo', 'type')
                ->where('type', 'san-pham')
                ->whereRaw("FIND_IN_SET(?,status)", ['hienthi'])
                ->whereRaw("FIND_IN_SET(?,status)", ['noibat1'])
                ->orderBy('numb', 'asc')
                ->orderBy('id', 'desc')
                ->get();

            $video =  PhotoModel::select('name' . $this->lang, 'link', 'type')
                ->where('type', 'video')
                ->whereRaw("FIND_IN_SET(?,status)", ['hienthi'])
                ->orderBy('numb', 'asc')
                ->orderBy('id', 'desc')
                ->first();

            return [
                'productcat' => $productcat,
                'criteria' => $criteria,
                'productNew' => $productNew,
                'tags' => $tags,
                'slider' => $slider,
                'blog' => $blog,
                'video' => $video,
                'productList' => $productList,
                'about' => $about,
                'partner' => $partner
            ];
        });

        $now = Carbon::now();
        $home['vouchers'] = VoucherModel::select('*')
            ->whereRaw("FIND_IN_SET(?,status)", ['hienthi'])
            ->where(function ($query) use ($now) {
                $query->whereNull('start_at')->orWhere('start_at', '<=', $now);
            })
            ->where(function ($query) use ($now) {
                $query->whereNull('end_at')->orWhere('end_at', '>=', $now);
            })
            ->where(function ($query) {
                $query->whereNull('usage_limit_total')->orWhereRaw('used_count < usage_limit_total');
            })
            ->orderBy('id', 'desc')
            ->limit(12)
            ->get();

        /* SEO */
        $titleMain = SettingModel::pluck('namevi')->first();
        $this->seoPage($titleMain);
        return View::share('com', 'trang-chu')->view('home.index', $home);
    }
    public function letter(Request $request)
    {

        $responseCaptcha = $_POST['recaptcha_response_newsletter'];
        $resultCaptcha = Func::checkRecaptcha($responseCaptcha);
        $scoreCaptcha = (!empty($resultCaptcha['score'])) ? $resultCaptcha['score'] : 0;
        $actionCaptcha = (!empty($resultCaptcha['action'])) ? $resultCaptcha['action'] : '';
        $testCaptcha = (!empty($resultCaptcha['test'])) ? $resultCaptcha['test'] : false;

        if (($scoreCaptcha >= 0.5 && $actionCaptcha == 'newsletter') || $testCaptcha == true) {
            $data['fullname'] = $request->input('fullname') ?? '';
            $data['phone'] = $request->input('phone') ?? '';
            $data['email'] = $request->input('email') ?? '';
            $data['address'] = $request->input('address') ?? '';
            $data['content'] = $request->input('content') ?? '';
            $data['date_created'] = Carbon::now()->timestamp;
            $data['confirm_status'] = 1;
            $data['type'] = 'dang-ky-nhan-tin';
            $data['subject'] = "Đăng ký nhận tin";
            if (NewslettersModel::create($data)) {
                transfer(__('web.dangkynhantinthanhcong'), 1, PeceeRequest()->getHeader('http_referer'));
            } else {
                transfer(__('web.dangkynhantinthatbai'), 0, PeceeRequest()->getHeader('http_referer'));
            }
        } else {
            return transfer(__('web.dangkynhantinthatbai'), 0, PeceeRequest()->getHeader('http_referer'));
        }
    }
    public function ajaxProduct(Request $request)
    {
        $lang = $this->lang;
        $id_cat = $request->get('id_cat') ?? 0;
        $id_list = $request->get('id_list') ?? 0;
        $type = $request->get('type') ?? 0;
        $status = $request->get('status') ?? 'noibat';
        $other = $request->get('other') ?? 1;
        $section = $request->get('section') ?? '';
        $template = $request->get('template') ?? '';
        $slug = $request->get('slug') ?? '';
        $paginate = $request->get('paginate') ?? 8;
        $page = $request->get('page') ?? 1;
        $view = 'ajax.' . 'homeProduct' . $template;

        $query = ProductModel::select('name' . $lang, 'photo', 'icon', 'desc' . $lang, $this->sluglang, 'regular_price', 'sale_price', 'discount', 'id', 'type', 'properties', 'id_cat', 'id_list')
            ->with(['getPhotos' => function ($query) {
                $query->where('type', 'san-pham')->orderBy('numb', 'asc');
            }])
            ->with([
                'getCategoryCat' => function ($query) use ($status) {
                    $query->whereRaw("FIND_IN_SET(?,status)", ['hienthi']);
                    $query->whereRaw("FIND_IN_SET(?,status)", [$status]);
                }
            ])
            ->where('type', $type)
            ->whereRaw("FIND_IN_SET(?,status)", ['hienthi'])
            ->whereRaw("FIND_IN_SET(?,status)", [$status])
            ->when($id_list, function ($query, $id_list) {
                $query->where('id_list', $id_list);
            })
            ->when($id_cat, function ($query, $id_cat) {
                $query->where('id_cat', $id_cat);
            });

        $sort = $request->get('sort');
        if (!empty($sort)) {
            switch ($sort) {
                case "1":
                    $query->orderBy('id', 'desc');
                    break;
                case "2":
                    $query->orderBy('id', 'asc');
                    break;
                case "3":
                    $query->orderByRaw('CASE WHEN sale_price > 0 THEN sale_price ELSE regular_price END DESC');
                    break;
                case "4":
                    $query->orderByRaw('CASE WHEN sale_price > 0 THEN sale_price ELSE regular_price END ASC');
                    break;
                default:
                    $query->orderBy('numb', 'asc')->orderBy('id', 'desc');
                    break;
            }
        } else {
            $query->orderBy('numb', 'asc')->orderBy('id', 'desc');
        }
        if (!empty($template)) {
            $productAjax = $query->get();

            $data = ['productAjax' => $productAjax, 'id' => $id_list, 'paginate' => $paginate];
        } else {
            if ($other == 1) {
                $productAjax = $query->paginate($paginate);
                $data = ['productAjax' => $productAjax, 'other' => $other];
            } elseif ($other == 2) {
                $productAjax = $query->limit($paginate)->get();
                $data = ['productAjax' => $productAjax, 'other' => $other, 'slug' => $slug];
            } elseif ($other == 3) {
                $productAjax = $query->paginate($paginate, ['*'], 'page', $page);
                $currentPage = $productAjax->currentPage();
                $lastPage = $productAjax->lastPage();
                $data = [
                    'productAjax' => $productAjax,
                    'other' => $other,
                    'currentPage' => $currentPage,
                    'lastPage' => $lastPage,
                    'section' => $section
                ];
            } else {
                $productAjax = $query->paginate($paginate);
                $data = ['productAjax' => $productAjax, 'other' => $other];
            }
        }

        return view($view, $data);
    }
}
