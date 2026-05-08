<?php


namespace LARAVEL\Controllers\Admin;
use Illuminate\Http\Request;
use LARAVEL\Core\Support\Facades\File;
use LARAVEL\Models\SeoPageModel;
use LARAVEL\Core\Support\Facades\Func;
use LARAVEL\Core\Support\Facades\Flash;
use LARAVEL\Core\Support\Facades\Validator;
use LARAVEL\Traits\TraitSave;

class SeopageController
{
    private $configType;
    use TraitSave;
    public function __construct()
    {
        $this->configType = json_decode(json_encode(config('type')))->seo->page;
    }

    public function man($com, $act, $type, Request $request)
    {

        $item = SeoPageModel::select('*')
            ->where('type', $type)
            ->first();

        return view('seopage.man.add', ['item' => $item]);
    }

    public function save($com, $act, $type, Request $request)
    {

        if (!empty($request->csrf_token)) {

            /* Post dữ liệu */
            $message = '';
            $response = array();

            $seopage = SeoPageModel::select('id')
                ->where('type', $type)
                ->first();

            $data = (!empty($request->data)) ? $request->data : null;

            if ($data) {
                foreach ($data as $column => $value) {
                    if (strpos($column, 'content') !== false || strpos($column, 'desc') !== false) {
                        $data[$column] = htmlspecialchars(Func::sanitize($value, 'iframe'));
                    } else {
                        $data[$column] = $value;
                    }
                }

                $data['type'] = $type;
            }


            if (!empty($response)) {

                /* Flash data */
                if (!empty($data)) {
                    foreach ($data as $k => $v) {
                        if (!empty($v)) {
                            Flash::set($k, $v);
                        }
                    }
                }
                /* Errors */
                $response['status'] = 'danger';
                $message = base64_encode(json_encode($response));
                Flash::set('message', $message);
                response()->redirect(linkReferer());
            }

            if (!empty($seopage)) {
                $data['date_updated'] = time();
                if (SeoPageModel::where('id', $seopage['id'])->update($data)) {
                    $id = $seopage['id'];
                    $file = $request->file('file-');
                    $cropFile = $request->{"cropFile-"};
                    if (!empty($cropFile)) {
                        $this->insertImgeCrop(SeoPageModel::class, $request, $file, $cropFile, $id, 'seopage', 'photo');
                    } else {
                        $this->insertImge(SeoPageModel::class, $request, $file, $id, 'seopage', 'photo');
                    }

                    return transfer('Cập nhật dữ liệu thành công.', true, url('admin', ['com' => $com, 'act' => 'man', 'type' => $type]));
                } else {
                    return transfer('Cập nhật dữ liệu thất bại.', true, linkReferer());
                }
            } else {
                $data['date_created'] = time();
                $itemSave = SeoPageModel::create($data);
                if (!empty($itemSave)) {
                    $id_insert = $itemSave->id;
                    $file = $request->file('file-');
                    $cropFile = $request->{"cropFile-"};
                    if (!empty($cropFile)) {
                        $this->insertImgeCrop(SeoPageModel::class, $request, $file, $cropFile, $id_insert, 'seopage', 'photo');
                    } else {
                        $this->insertImge(SeoPageModel::class, $request, $file, $id_insert, 'seopage', 'photo');
                    }
                    response()->redirect(url('admin', ['com' => $com, 'act' => 'man', 'type' => $type]));
                } else {
                    response()->redirect(url('admin', ['com' => $com, 'act' => 'man', 'type' => $type]));
                }
            }
        }
    }
    /* private function */
}
