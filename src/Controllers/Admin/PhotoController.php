<?php


namespace LARAVEL\Controllers\Admin;
use Illuminate\Http\Request;
use LARAVEL\Core\Support\Facades\File;
use LARAVEL\Traits\TraitSave;
use LARAVEL\Models\PhotoModel;
use LARAVEL\Core\Support\Facades\Func;
use LARAVEL\Core\Support\Facades\Flash;
use LARAVEL\Core\Support\Facades\Validator;

class PhotoController
{
    use TraitSave;
    private $configType;
    public function __construct()
    {

        $this->configType = json_decode(json_encode(config('type')))->photo;
    }

    public function manStatic($com, $act, $type, Request $request)
    {
        $item = PhotoModel::select('*')->where('type', $type)->first();
        $options = (!empty($item) && !empty($item['options'])) ? json_decode($item['options'], true) : [];
        return view('photo.static.add', ['item' => $item, 'options' => $options]);
    }

    public function saveStatic($com, $act, $type, Request $request)
    {

        if (!empty($request->csrf_token)) {

            /* Post dữ liệu */
            $message = '';
            $response = array();

            $photo = PhotoModel::select('id')
                ->where('type', $type)
                ->where('com', $com)
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
            }
            if (!empty($request->status)) {
                $status = '';
                foreach ($request->status as $attr_column => $attr_value)
                    if ($attr_value != "")
                        $status .= $attr_column . ',';
                $data['status'] = (!empty($status)) ? rtrim($status, ",") : "";
            } else {
                $data['status'] = "";
            }
            $data['type'] = $type;
            $data['com'] = $com;
            if ($request->has('options'))
                $data['options'] = json_encode($request->input('options'));

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

            if (!empty($photo)) {
                $data['date_updated'] = time();
                if (PhotoModel::where('type', $type)->update($data)) {
                    $id = $photo['id'];
                    /* IMAGE */
                    if (!empty($this->configType->$type->images)) {
                        $file = $request->file('file-photo');
                        $cropFile = $request->{"cropFile-photo"};
                        if (!empty($cropFile)) {
                            $this->insertImgeCrop(PhotoModel::class, $request, $file, $cropFile, $id, 'photo', 'photo');
                        } else {
                            $this->insertImge(PhotoModel::class, $request, $file, $id, 'photo', 'photo');
                        }
                    }
                    return transfer('Cập nhật dữ liệu thành công.', true, url('admin', ['com' => $com, 'act' => 'man', 'type' => $type], ['page' => $request->page]));
                } else {
                    return transfer('Cập nhật dữ liệu thất bại.', false, linkReferer());
                }
            } else {
                $data['date_created'] = time();
                $itemSave = PhotoModel::create($data);
                if (!empty($itemSave)) {
                    $id_insert = $itemSave->id;
                    /* IMAGE */
                    if (!empty($this->configType->$type->images)) {
                        $file = $request->file('file-photo');
                        $cropFile = $request->{"cropFile-photo"};
                        if (!empty($cropFile)) {
                            $this->insertImgeCrop(PhotoModel::class, $request, $file, $cropFile, $id_insert, 'photo', 'photo');
                        } else {
                            $this->insertImge(PhotoModel::class, $request, $file, $id_insert, 'photo', 'photo');
                        }
                    }



                    response()->redirect(url('admin', ['com' => $com, 'act' => 'man', 'type' => $type], ['page' => $request->page]));
                } else {
                    response()->redirect(url('admin', ['com' => $com, 'act' => 'man', 'type' => $type], ['page' => $request->page]));
                }
            }
        }
    }

    public function manAlbum($com, $act, $type, Request $request)
    {
        $keyword = $request->query('keyword') ?? '';
        $query = PhotoModel::select('id', 'namevi', 'photo', 'link', 'status', 'photo', 'link_video', 'numb')->where('type', $type)->where('com', $com);
        if ($keyword)
            $query->where('namevi', 'like', '%' . $keyword . '%');
        $items = $query->orderBy('numb', 'asc')->orderBy('id', 'desc')->paginate(10);
        return view('photo.album.man', ['items' => $items]);
    }

    public function editAlbum($com, $act, $type, Request $request)
    {
        $id = $request->id;
        $item = '';

        if (!empty($id)) {
            $item = PhotoModel::select('*')
                ->where('type', $type)
                ->where('com', $com)
                ->where('id', $id)
                ->orderBy('numb', 'asc')
                ->first();
            return view('photo.album.edit', ['item' => $item]);
        } else {
            return view('photo.album.add', []);
        }
    }

    public function saveAlbum($com, $act, $type, Request $request)
    {

        if (!empty($request->csrf_token)) {

            /* Post dữ liệu */
            $message = '';
            $response = array();
            $id = (!empty($request->id)) ? htmlspecialchars($request->id) : 0;
            $data = (!empty($request->data)) ? $request->data : null;
            $dataMultiTemp = (!empty($request->dataMultiTemp)) ? $request->dataMultiTemp : null;

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
                    foreach ($request->status as $attr_column => $attr_value)
                        if ($attr_value != "")
                            $status .= $attr_column . ',';
                    $data['status'] = (!empty($status)) ? rtrim($status, ",") : "";
                } else {
                    $data['status'] = "";
                }

                $data['type'] = $type;
                $data['com'] = $com;
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

            if ($id) {

                $data['date_updated'] = time();
                if (PhotoModel::where('id', $id)->where('type', $type)->update($data)) {

                    /* IMAGE */
                    if (!empty($this->configType->$type->images)) {
                        $file = $request->file('file-photo');
                        $cropFile = $request->{"cropFile-photo"};
                        if (!empty($cropFile)) {
                            $this->insertImgeCrop(PhotoModel::class, $request, $file, $cropFile, $id, 'photo');
                        } else {
                            $this->insertImge(PhotoModel::class, $request, $file, $id, 'photo');
                        }
                    }

                    return transfer('Cập nhật dữ liệu thành công.', true, url('admin', ['com' => $com, 'act' => 'man', 'type' => $type], ['page' => $request->page]));
                } else {
                    return transfer('Cập nhật dữ liệu thất bại.', true, linkReferer());
                }
            } else {
                $dataMulti = array();
                $errors = false;
                $status = '';
                for ($i = 0; $i < count($dataMultiTemp); $i++) {
                    $dataMulti = $dataMultiTemp[$i];
                    $cropFile = '';
                    if (!empty($dataMulti['namevi']) || !empty($dataMulti['link']) || !empty($request->file('file-' . $i))) {
                        $dataMulti['type'] = $type;
                        $dataMulti['com'] = $com;

                        if (!empty($dataMulti['status'])) {
                            $status_Multi = $dataMulti['status'];

                            foreach ($status_Multi as $attr_column => $attr_value)
                                if ($attr_value != "")
                                    $status .= $attr_column . ',';
                            $dataMulti['status'] = (!empty($status)) ? rtrim($status, ",") : "";
                        } else {
                            $dataMulti['status'] = "";
                        }

                        $itemSave = PhotoModel::create($dataMulti);
                        if (!empty($itemSave)) {
                            $id_insert = $itemSave->id;
                            $file = (!empty($request->file('file-' . $i))) ? $request->file('file-' . $i) : null;
                            $cropFile = $request->{"cropFile-$i"};

                            if (!empty($cropFile)) {
                                $this->insertImgeCrop(PhotoModel::class, $request, $file, $cropFile, $id_insert, 'photo', 'photo');
                            } else {
                                $this->insertImge(PhotoModel::class, $request, $file, $id_insert, 'photo', 'photo');
                            }
                            unset($dataMulti);
                        } else {
                            $errors = true;
                        }
                    }
                }
                if (!empty($errors)) {
                    return transfer('Cập nhật dữ liệu thất bại.', false, linkReferer());
                } else {
                    response()->redirect(url('admin', ['com' => $com, 'act' => 'man', 'type' => $type], ['page' => $request->page]));
                }
            }
        }
    }


    public function deleteAlbum($com, $act, $type, Request $request)
    {
        if (!empty($request->id)) {
            $id = $request->id;
            $row = PhotoModel::select('id', 'photo')
                ->where('id', $id)
                ->first();
            if (!empty($row)) {
                if (File::exists(upload('photo', $row['photo'], true))) {
                    File::delete(upload('photo', $row['photo'], true));
                }
                PhotoModel::where('id', $id)->delete();
            }
        } elseif (!empty($request->listid)) {
            $listid = explode(",", $request->listid);

            for ($i = 0; $i < count($listid); $i++) {
                $id = htmlspecialchars($listid[$i]);
                $row = PhotoModel::select('id', 'photo')
                    ->where('id', $id)
                    ->first();
                if (!empty($row)) {
                    if (File::exists(upload('photo', $row['photo'], true))) {
                        File::delete(upload('photo', $row['photo'], true));
                    }
                    PhotoModel::where('id', $id)->delete();
                }
            }
        }
        response()->redirect(url('admin', ['com' => $com, 'act' => 'man', 'type' => $type], ['page' => $request->page]));
    }
}
