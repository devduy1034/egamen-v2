<?php


namespace LARAVEL\Controllers\Admin;

use Illuminate\Http\Request;
use LARAVEL\Core\Support\Facades\Flash;
use LARAVEL\Core\Support\Facades\Func;
use LARAVEL\Models\GalleryModel;
use LARAVEL\Models\StaticModel;
use LARAVEL\Traits\TraitSave;

class StaticController
{
    use TraitSave;
    private $configType;
    public function __construct()
    {
        $this->configType = json_decode(json_encode(config('type')))->static;
    }

    public function man($com, $act, $type, Request $request)
    {
        $item = StaticModel::select('*')
            ->where('type', $type)
            ->first();

        if (!empty($item) && !empty($this->configType->$type->gallery)) {
            $gallery = GalleryModel::select('*')
                ->where('com', $com)
                ->where('type', $type)
                ->where('type_parent', $type)
                ->where('id_parent', $item['id'])
                ->orderBy('numb', 'asc')
                ->get();
        }

        return view('static.man.add', ['item' => $item, 'gallery' => $gallery ?? []]);
    }

    public function save($com, $act, $type, Request $request)
    {

        if (!empty($request->csrf_token)) {

            /* Post dữ liệu */
            $message = '';
            $response = array();

            $static = StaticModel::select('id')
                ->where('type', $type)
                ->first();

            $data = (!empty($request->data)) ? $request->data : null;

            if ($data) {
                foreach ($data as $column => $value) {
                    if (strpos($column, 'content') !== false || strpos($column, 'desc') !== false) {
                        $data[$column] = htmlspecialchars(Func::sanitize($value, 'iframe'));
                    } else {
                        $data[$column] = htmlspecialchars(Func::sanitize($value));
                    }
                }

                if (!empty($request->status)) {
                    $status = '';
                    foreach ($request->status as $attr_column => $attr_value) if ($attr_value != "") $status .= $attr_column . ',';
                    $data['status'] = (!empty($status)) ? rtrim($status, ",") : "";
                } else {
                    $data['status'] = "";
                }

                $data['type'] = $type;
            }

            /* Post seo */
            if (!empty($this->configType->$type->seo)) {
                $dataSeo = $this->getSeo($request);
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

                if (!empty($dataSeo)) {
                    foreach ($dataSeo as $k => $v) {
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

            if (!empty($static)) {
                $data['date_updated'] = time();
                if (StaticModel::where('type', $type)->update($data)) {
                    $id = $static['id'];

                    /* IMAGE */
                    if (!empty($this->configType->$type->images)) {
                        foreach ($this->configType->$type->images as $key => $photo) {
                            $file = $request->file('file-' . $key);
                            $cropFile = $request->{"cropFile-$key"};
                            if(!empty($cropFile)){
                                $this->insertImgeCrop(StaticModel::class, $request, $file, $cropFile, $id, 'news', $key);
                            }else{
                                $this->insertImge(StaticModel::class, $request, $file, $id, 'news', $key);
                            }
                        }
                    }

                    if (!empty($this->configType->$type->gallery)) {
                        $files = (!empty($request->file('files'))) ? $request->file('files') : null;
                        $this->insertImges(GalleryModel::class, $request, $files, $id, $com, $type, $type, 'news');
                    }

                    if (!empty($this->configType->$type->seo)) {
                        $this->insertSeo($com, $act, $type, $id, $dataSeo);
                    }
                    return transfer('Cập nhật dữ liệu thành công.', true, url('admin', ['com' => $com, 'act' => 'man', 'type' => $type]));
                } else {
                    return transfer('Cập nhật dữ liệu thất bại.', true, linkReferer());
                }
            } else {
                $data['date_created'] = time();
                $itemSave = StaticModel::create($data);
                if (!empty($itemSave)) {
                    $id_insert = $itemSave->id;

                    /* IMAGE */
                    if (!empty($this->configType->$type->images)) {
                        foreach ($this->configType->$type->images as $key => $photo) {
                            $file = $request->file('file-' . $key);
                            $cropFile = $request->{"cropFile-$key"};
                            if(!empty($cropFile)){
                                $this->insertImgeCrop(StaticModel::class, $request, $file, $cropFile, $id_insert, 'news', $key);
                            }else{
                                $this->insertImge(StaticModel::class, $request, $file, $id_insert, 'news', $key);
                            }
                        }
                    }

                    /* ALBUM */
                    if (!empty($this->configType->$type->gallery)) {
                        $files = (!empty($request->file('files'))) ? $request->file('files') : null;
                        $this->insertImges(GalleryModel::class, $request, $files, $id_insert, $com, $type, $type, 'news');
                    }

                    /* SEO */
                    if (!empty($this->configType->$type->seo)) {
                        $this->insertSeo($com, $act, $type, $id_insert, $dataSeo);
                    }
                    response()->redirect(url('admin', ['com' => $com, 'act' => 'man', 'type' => $type]));
                } else {
                    response()->redirect(url('admin', ['com' => $com, 'act' => 'man', 'type' => $type]));
                }
            }
        }
    }
}
