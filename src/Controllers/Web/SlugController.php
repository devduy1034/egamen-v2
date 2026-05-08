<?php


namespace LARAVEL\Controllers\Web;
use Illuminate\Http\Request;
use LARAVEL\Controllers\Controller;
use LARAVEL\Models\SlugModel;
use LARAVEL\Core\Routing\LARAVELRouter;

class SlugController extends Controller
{
    public function handle($slug, Request $request)
    {
        if (!empty($slug)) {
            $query = SlugModel::select('*')->where(function ($query) use ($slug) {
                $query->where($this->sluglang, $slug);
            });
            $check = $query->first();
            if (!empty($check)) {
                $query_date = $check['model']::select('id', 'status')->where('id', $check['id_parent'])->whereRaw("FIND_IN_SET(?,status)", ['hienthi']);
                if (empty(Request()->preview)) $query_date->datePublish();
                $checkDate = $query_date->first();
            }
            if (!empty($check) && !empty($checkDate) && !empty($check->getStatus($check['model'])->first()->id)) {
                $method = !empty(explode('-', $check['com'])[1]) ? explode('-', $check['com'])[1] : 'detail';
                $controllerClass = $this->resolveSlugControllerClass((string) ($check['controller'] ?? ''), (string) ($check['com'] ?? ''));
                $controller = new ($controllerClass);
                return $controller->$method($slug, $request);
            } else {
                LARAVELRouter::response()->httpCode(404);
                view('error.notfound', []);
            }
        }
    }

    protected function resolveSlugControllerClass(string $controllerClass, string $com): string
    {
        $controllerClass = trim($controllerClass);

        if ($controllerClass !== '' && class_exists($controllerClass)) {
            return $controllerClass;
        }

        if (in_array($com, ['product', 'product-list', 'product-cat', 'product-item', 'product-sub'], true)) {
            return '\LARAVEL\Controllers\Web\ProductController';
        }

        return $controllerClass;
    }
}
