<?php


namespace LARAVEL\Controllers\Admin;
use Illuminate\Http\Request;
use LARAVEL\Core\Support\Facades\File;
use LARAVEL\Core\Support\Facades\Flash;
use LARAVEL\Core\Support\Facades\Func;
use LARAVEL\Models\GalleryModel;
use LARAVEL\Models\PropertiesListModel;
use LARAVEL\Models\PropertiesModel;
use LARAVEL\Models\SlugModel;
use LARAVEL\Traits\TraitSave;
use XLSXWriter;


class PropertiesController
{
    private $configType;
    private $upload;
    use TraitSave;
    public function __construct()
    {
        $this->configType = json_decode(json_encode(config('type')))->properties;
    }
    /* List */
    public function manList($com, $act, $type, Request $request)
    {
        if ($request->isMethod('get') && !empty($request->keyword)) {
            $keyword = $request->keyword;
        }

        $query = PropertiesListModel::select('id', 'namevi', 'slugvi', 'status', 'numb')
            ->where('type', $type);
        if (!empty($keyword)) $query->where('namevi', 'like', '%' . $keyword . '%');
        $items = $query->orderBy('numb', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(10);
        return view('properties.list.man', ['items' => $items]);
    }

    public function editList($com, $act, $type, Request $request)
    {
        $id = $request->id;
        $item = '';

        if (!empty($id)) {
            $item = PropertiesListModel::select('*')
                ->where('type', $type)
                ->where('id', $id)
                ->orderBy('numb', 'asc')
                ->first();
        }

        return view('properties.list.add', ['item' => $item]);
    }


    public function saveList($com, $act, $type, Request $request)
    {

        if (!empty($request->csrf_token)) {

            /* Post dữ liệu */
            $message = '';
            $response = array();
            $id = (!empty($request->id)) ? htmlspecialchars($request->id) : 0;
            $data = (!empty($request->data)) ? $request->data : null;
            $id_list = (!empty($request->id_list)) ? $request->id_list : null;
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

                if (!empty($this->configType->$type->categories->list->slug_categories)) {
                    if (!empty($request->slugvi)) $data['slugvi'] = Func::changeTitle(htmlspecialchars($request->slugvi));
                    else $data['slugvi'] = (!empty($data['namevi'])) ? Func::changeTitle($data['namevi']) : '';
                    if (!empty($request->slugen)) $data['slugen'] = Func::changeTitle(htmlspecialchars($request->slugen));
                    else $data['slugen'] = (!empty($data['nameen'])) ? Func::changeTitle($data['nameen']) : '';
                }

                if (!empty($id_list)) {
                    $data['id_list'] = implode(',', $id_list);
                } else {
                    $data['id_list'] = '';
                }

                $data['type'] = $type;
            }



            if (!empty($this->configType->$type->categories->list->slug_categories)) {
                foreach (config('app.slugs') as $k => $v) {
                    $dataSlug = array();
                    $dataSlug['slug'] = $data['slug' . $k];
                    $dataSlug['id'] = $id;
                    $dataSlug['copy'] = false;
                    $checkSlug = Func::checkSlug($dataSlug);

                    if ($checkSlug == 'exist') {
                        $response['messages'][] = 'Đường dẫn đã tồn tại';
                    } else if ($checkSlug == 'empty') {
                        $response['messages'][] = 'Đường dẫn không được trống';
                    }
                    unset($dataSlug);
                }
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
                if (PropertiesListModel::where('id', $id)->where('type', $type)->update($data)) {

                    return transfer('Cập nhật dữ liệu thành công.', true, url('admin', ['com' => $com, 'act' => 'man', 'type' => $type], ['page' => $request->page]));
                } else {
                    return transfer('Cập nhật dữ liệu thất bại.', false, linkReferer());
                }
            } else {
                $data['date_created'] = time();
                $itemSave = PropertiesListModel::create($data);
                if (!empty($itemSave)) {
                    $id_insert = $itemSave->id;

                    response()->redirect(url('admin', ['com' => $com, 'act' => 'man', 'type' => $type], ['page' => $request->page]));
                } else {
                    response()->redirect(url('admin', ['com' => $com, 'act' => 'man', 'type' => $type], ['page' => $request->page]));
                }
            }
        }
    }

    public function deleteList($com, $act, $type, Request $request)
    {
        if (!empty($request->id)) {
            $id = $request->id;

            PropertiesListModel::where('id', $id)->delete();
            SlugModel::where('id_parent', $id)->where('com', $com)->where('type', $type)->delete();
        } elseif (!empty($request->listid)) {
            $listid = explode(",", $request->listid);

            for ($i = 0; $i < count($listid); $i++) {
                $id = htmlspecialchars($listid[$i]);
                PropertiesListModel::where('id', $id)->delete();
                SlugModel::where('id_parent', $id)->where('com', $com)->where('type', $type)->delete();
            }
        }
        response()->redirect(url('admin', ['com' => $com, 'act' => 'man', 'type' => $type], ['page' => $request->page]));
    }



    /* Man */

    public function man($com, $act, $type, Request $request)
    {
        if ($request->isMethod('get') && !empty($request->keyword)) {
            $keyword = $request->keyword;
        }

        if ($request->isMethod('get') && !empty($request->id_list)) {
            $id_list = $request->id_list;
        }


        $query = PropertiesModel::select('*')
            ->where('type', $type);
        if (!empty($keyword)) $query->where('namevi', 'like', '%' . $keyword . '%');
        if (!empty($id_list)) $query->where('id_list', '=', $id_list);

        $items = $query->orderBy('numb', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('properties.man.man', ['items' => $items]);
    }

    public function edit($com, $act, $type, Request $request)
    {
        $id = $request->id;
        $item = '';
        $gallery = '';

        if (!empty($id)) {
            $item = PropertiesModel::select('*')
                ->where('type', $type)
                ->where('id', $id)
                ->orderBy('numb', 'asc')
                ->first();
        }

        if (!empty($this->configType->$type->gallery)) {
            $gallery = GalleryModel::select('*')
                ->where('com', $com)
                ->where('type', $type)
                ->where('type_parent', $type)
                ->where('id_parent', $id)
                ->orderBy('numb', 'asc')
                ->get();
        }

        return view('properties.man.add', ['item' => $item, 'gallery' => $gallery]);
    }

    public function save($com, $act, $type, Request $request)
    {

        if (!empty($request->csrf_token)) {

            /* Post dữ liệu */
            $message = '';
            $response = array();
            $id = (!empty($request->id)) ? htmlspecialchars($request->id) : 0;
            $data = (!empty($request->data)) ? $request->data : null;
            $dataTags = (!empty($_POST['dataTags'])) ? $_POST['dataTags'] : null;
            $buildSchema = (!empty($_POST['build-schema'])) ? true : false;
            $dataSchema = (!empty($_POST['dataSchema'])) ? $_POST['dataSchema'] : array();
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

                if (!empty($this->configType->$type->slug)) {
                    if (!empty($request->slugvi)) $data['slugvi'] = Func::changeTitle(htmlspecialchars($request->slugvi));
                    else $data['slugvi'] = (!empty($data['namevi'])) ? Func::changeTitle($data['namevi']) : '';
                    if (!empty($request->slugen)) $data['slugen'] = Func::changeTitle(htmlspecialchars($request->slugen));
                    else $data['slugen'] = (!empty($data['nameen'])) ? Func::changeTitle($data['nameen']) : '';
                }


                $data['type'] = $type;
            }



            if (!empty($this->configType->$type->slug)) {
                foreach (config('app.slugs') as $k => $v) {
                    $dataSlug = array();
                    $dataSlug['slug'] = $data['slug' . $k];
                    $dataSlug['id'] = $id;
                    $dataSlug['copy'] = false;
                    $checkSlug = Func::checkSlug($dataSlug);

                    if ($checkSlug == 'exist') {
                        $response['messages'][] = 'Đường dẫn đã tồn tại';
                    } else if ($checkSlug == 'empty') {
                        $response['messages'][] = 'Đường dẫn không được trống';
                    }
                    unset($dataSlug);
                }
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

            if ($id) {
                $data['date_updated'] = time();
                if (PropertiesModel::where('id', $id)->where('type', $type)->update($data)) {



                    /* IMAGE */
                    if (!empty($this->configType->$type->images)) {
                        $file = $request->file('file');
                        $this->insertImge(PropertiesModel::class, $request, $file, $id, 'properties');
                    }

                    /* ALBUM */
                    if (!empty($this->configType->$type->gallery)) {
                        $files = (!empty($request->file('files'))) ? $request->file('files') : null;
                        $this->insertImges(GalleryModel::class, $request, $files, $id, $com, $type, $type, 'properties');
                    }

                    return transfer('Cập nhật dữ liệu thành công.', true, url('admin', ['com' => $com, 'act' => 'man', 'type' => $type], ['page' => $request->page]));
                } else {
                    return transfer('Cập nhật dữ liệu thất bại.', true, linkReferer());
                }
            } else {
                $data['date_created'] = time();
                $itemSave = PropertiesModel::create($data);
                if (!empty($itemSave)) {
                    $id_insert = $itemSave->id;

                    if (!empty($this->configType->$type->tags)) {
                        $this->insertTags(propertiesTagsModel::class, $request, $dataTags, $id_insert);
                    }

                    /* IMAGE */
                    if (!empty($this->configType->$type->images)) {
                        $file = $request->file('file');
                        $this->insertImge(PropertiesModel::class, $request, $file, $id_insert, 'properties');
                    }

                    /* ALBUM */
                    if (!empty($this->configType->$type->gallery)) {
                        $files = (!empty($request->file('files'))) ? $request->file('files') : null;
                        $this->insertImges(GalleryModel::class, $request, $files, $id_insert, $com, $type, $type, 'properties');
                    }


                    response()->redirect(url('admin', ['com' => $com, 'act' => 'man', 'type' => $type], ['page' => $request->page]));
                } else {
                    response()->redirect(url('admin', ['com' => $com, 'act' => 'man', 'type' => $type], ['page' => $request->page]));
                }
            }
        }
    }

    public function delete($com, $act, $type, Request $request)
    {
        if (!empty($request->id)) {
            $id = $request->id;
            $row = PropertiesModel::select('id', 'photo')
                ->where('id', $id)
                ->first();

            if (!empty($row)) {
                if (File::exists(upload('properties', $row['photo'], true))) {
                    File::delete(upload('properties', $row['photo'], true));
                }
                PropertiesModel::where('id', $id)->delete();
            }

            SlugModel::where('id_parent', $id)->where('com', $com)->where('type', $type)->delete();
        } elseif (!empty($request->listid)) {
            $listid = explode(",", $request->listid);

            for ($i = 0; $i < count($listid); $i++) {
                $id = htmlspecialchars($listid[$i]);
                $row = PropertiesModel::select('id', 'photo')
                    ->where('id', $id)
                    ->first();
                $rowGallery = GalleryModel::select('id', 'photo')->where('id_parent', $id)->where('com', $com)->where('type', $type)->get();
                if (!empty($row)) {
                    if (File::exists(upload('properties', $row['photo'], true))) {
                        File::delete(upload('properties', $row['photo'], true));
                    }
                    PropertiesModel::where('id', $id)->delete();
                }

                SlugModel::where('id_parent', $id)->where('com', $com)->where('type', $type)->delete();
            }
        }
        response()->redirect(url('admin', ['com' => $com, 'act' => 'man', 'type' => $type], ['page' => $request->page]));
    }
}
