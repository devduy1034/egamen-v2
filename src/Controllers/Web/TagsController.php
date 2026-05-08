<?php


namespace LARAVEL\Controllers\Web;
use LARAVEL\Core\Support\Facades\View;
use Illuminate\Http\Request;
use LARAVEL\Controllers\Controller;
use LARAVEL\Core\Support\Facades\Seo;
use LARAVEL\Models\TagsModel;
use LARAVEL\Models\ProductModel;
use LARAVEL\Models\ProductTagsModel;
use LARAVEL\Core\Support\Facades\BreadCrumbs;
use Func;

class TagsController extends Controller
{
    public function detail($slug)
    {
        $rowTags = TagsModel::select('name'. $this->lang, 'desc'. $this->lang, 'type', 'id')
            ->where('slug'. $this->lang, $slug)
            ->first();

        $this->type =  $rowTags->type;
        $titleMain = $rowTags['name' . $this->lang];
        BreadCrumbs::setBreadcrumb(list: $rowTags);
        $seoPage = $rowTags->getSeo('tags', 'save')->first();
        $seoPage['type'] = $this->infoSeo('tags', $this->type, 'type', 'index');
        Seo::setSeoData($seoPage, 'tags', 'seo');
        if ($rowTags['type'] == 'san-pham') {
            $product = $rowTags->products()->paginate(12);
            return view('product.product', compact('product', 'titleMain'));
        } else {
            $news = $rowTags->news()->paginate(12);
            return view('news.news', compact('news', 'titleMain'));
        }
    }
}