<?php


namespace LARAVEL\Controllers\Admin;
use Carbon\Carbon;
use Illuminate\Http\Request;
use LARAVEL\Core\Support\Facades\File;
use LARAVEL\Core\Support\Facades\Flash;
use LARAVEL\Core\Support\Facades\Func;
use LARAVEL\Models\CityModel;
use LARAVEL\Models\DistrictModel;
use LARAVEL\Models\WardModel;
use LARAVEL\Models\SlugModel;
use LARAVEL\Traits\TraitSave;

class PlacesController
{
    use TraitSave;
    private $configType;
    private $upload;

    public function __construct()
    {
        $this->configType = json_decode(json_encode(config('type')))->places;
    }

    /* List */
    public function manList($com, $act, $type, Request $request)
    {
        if ($request->isMethod('get') && !empty($request->keyword)) {
            $keyword = $request->keyword;
        }

        $query = CityModel::select('id', 'namevi',  'status', 'numb')
            ->where('type', $type);
        if (!empty($keyword)) $query->where('namevi', 'like', '%' . $keyword . '%');
        $items = $query
            ->orderBy('numb', 'asc')
            ->orderBy('id', 'desc')
            ->paginate(10);
        return view('places.list.man', ['items' => $items]);
    }

    public function editList($com, $act, $type, Request $request)
    {
        $id = $request->id;
        $item = [];
        if (!empty($id)) {
            $item = CityModel::select('*')
                ->where('type', $type)
                ->where('id', $id)
                ->orderBy('numb', 'asc')
                ->first();
        }
        return view('places.list.add', ['item' => $item]);
    }

    public function saveList($com, $act, $type, Request $request)
    {

        if (!empty($request->csrf_token)) {

            /* Post dữ liệu */
            $message = '';
            $response = array();
            $id = (!empty($request->id)) ? htmlspecialchars($request->id) : 0;
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

                if (!empty($this->configType->$type->categories->list->slug_categories)) {
                    if (!empty($request->slug)) $data['slugvi'] = Func::changeTitle(htmlspecialchars($request->slug));
                    else $data['slugvi'] = (!empty($data['namevi'])) ? Func::changeTitle($data['namevi']) : '';
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

            if ($id) {
                $data['date_updated'] = time();
                if (CityModel::where('id', $id)->where('type', $type)->update($data)) {
                    return transfer('Cập nhật dữ liệu thành công.', true, url('admin', ['com' => $com, 'act' => 'man', 'type' => $type],['page'=>$request->page]));
                } else {
                    return transfer('Cập nhật dữ liệu thất bại.', true, linkReferer());
                }
            } else {
                $data['date_created'] = time();
                $itemSave = CityModel::create($data);
                if (!empty($itemSave)) {
                    $id_insert = $itemSave->id;
                    response()->redirect(url('admin', ['com' => $com, 'act' => 'man', 'type' => $type],['page'=>$request->page]));
                } else {
                    response()->redirect(url('admin', ['com' => $com, 'act' => 'man', 'type' => $type],['page'=>$request->page]));
                }
            }
        }
    }

    public function deleteList($com, $act, $type, Request $request)
    {
        if (!empty($request->id)) {
            $id = $request->id;

            $row = CityModel::select('id')
                ->where('id', $id)
                ->first();
            if (!empty($row)) {
                CityModel::where('id', $id)->delete();
            }
        } elseif (!empty($request->listid)) {
            $listid = explode(",", $request->listid);

            for ($i = 0; $i < count($listid); $i++) {
                $id = htmlspecialchars($listid[$i]);
                $row = CityModel::select('id')
                    ->where('id', $id)
                    ->first();
                if (!empty($row)) {
                    CityModel::where('id', $id)->delete();
                }
            }
        }
        response()->redirect(url('admin', ['com' => $com, 'act' => 'man', 'type' => $type],['page'=>$request->page]));
    }

    /* Cat */

    public function manCat($com, $act, $type, Request $request)
    {

        if ($request->isMethod('get') && !empty($request->keyword)) {
            $keyword = $request->keyword;
        }

        if ($request->isMethod('get') && !empty($request->id_city)) {
            $id_city = $request->id_city;
        }

        $query = DistrictModel::select('id', 'id_city', 'namevi',  'status', 'numb')
            ->where('type', $type);
        if (!empty($keyword)) $query->where('namevi', 'like', '%' . $keyword . '%');
        if (!empty($id_city)) $query->where('id_city', '=', $id_city);
        $items = $query
            ->orderBy('numb', 'asc')
            ->orderBy('id', 'desc')
            ->paginate(10);
        return view('places.cat.man', ['items' => $items]);
    }

    public function editCat($com, $act, $type, Request $request)
    {
        $id = $request->id;
        $item = [];
        if (!empty($id)) {
            $item = DistrictModel::select('*')
                ->where('type', $type)
                ->where('id', $id)
                ->orderBy('numb', 'asc')
                ->first();
        }
        return view('places.cat.add', ['item' => $item]);
    }

    public function saveCat($com, $act, $type, Request $request)
    {

        if (!empty($request->csrf_token)) {

            /* Post dữ liệu */
            $message = '';
            $response = array();
            $id = (!empty($request->id)) ? htmlspecialchars($request->id) : 0;
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

                if (!empty($this->configType->$type->categories->cat->slug_categories)) {
                    if (!empty($request->slug)) $data['slugvi'] = Func::changeTitle(htmlspecialchars($request->slug));
                    else $data['slugvi'] = (!empty($data['namevi'])) ? Func::changeTitle($data['namevi']) : '';
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

            if ($id) {
                $data['date_updated'] = time();
                if (DistrictModel::where('id', $id)->where('type', $type)->update($data)) {
                    return transfer('Cập nhật dữ liệu thành công.', true, url('admin', ['com' => $com, 'act' => 'man', 'type' => $type],['page'=>$request->page]));
                } else {
                    return transfer('Cập nhật dữ liệu thất bại.', true, linkReferer());
                }
            } else {
                $data['date_created'] = time();
                $itemSave = DistrictModel::create($data);
                if (!empty($itemSave)) {
                    $id_insert = $itemSave->id;
                    response()->redirect(url('admin', ['com' => $com, 'act' => 'man', 'type' => $type],['page'=>$request->page]));
                } else {
                    response()->redirect(url('admin', ['com' => $com, 'act' => 'man', 'type' => $type],['page'=>$request->page]));
                }
            }
        }
    }

    public function deleteCat($com, $act, $type, Request $request)
    {
        if (!empty($request->id)) {
            $id = $request->id;
            $row = DistrictModel::select('id')
                ->where('id', $id)
                ->first();
            if (!empty($row)) {
                DistrictModel::where('id', $id)->delete();
            }
        } elseif (!empty($request->listid)) {
            $listid = explode(",", $request->listid);

            for ($i = 0; $i < count($listid); $i++) {
                $id = htmlspecialchars($listid[$i]);
                $row = DistrictModel::select('id')
                    ->where('id', $id)
                    ->first();
                if (!empty($row)) {
                    DistrictModel::where('id', $id)->delete();
                }
            }
        }
        response()->redirect(url('admin', ['com' => $com, 'act' => 'man', 'type' => $type],['page'=>$request->page]));
    }

    /* Item */

    public function manItem($com, $act, $type, Request $request)
    {

        if ($request->isMethod('get') && !empty($request->keyword)) {
            $keyword = $request->keyword;
        }

        if ($request->isMethod('get') && !empty($request->id_city)) {
            $id_city = $request->id_city;
        }

        $query = WardModel::select('id', 'id_city', 'namevi',  'status', 'numb')
            ->where('type', $type);
        if (!empty($keyword)) $query->where('namevi', 'like', '%' . $keyword . '%');
        if (!empty($id_city)) $query->where('id_city', '=', $id_city);
        $items = $query
            ->orderBy('numb', 'asc')
            ->orderBy('id', 'desc')
            ->paginate(10);
        return view('places.item.man', ['items' => $items]);
    }

    public function editItem($com, $act, $type, Request $request)
    {
        $id = $request->id;
        $item = [];

        if (!empty($id)) {
            $item = WardModel::select('*')
                ->where('type', $type)
                ->where('id', $id)
                ->orderBy('numb', 'asc')
                ->first();
        }

        return view('places.item.add', ['item' => $item]);
    }

    public function saveItem($com, $act, $type, Request $request)
    {

        if (!empty($request->csrf_token)) {

            /* Post dữ liệu */
            $message = '';
            $response = array();
            $id = (!empty($request->id)) ? htmlspecialchars($request->id) : 0;
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

                if (!empty($this->configType->$type->categories->item->slug_categories)) {
                    if (!empty($request->slug)) $data['slugvi'] = Func::changeTitle(htmlspecialchars($request->slug));
                    else $data['slugvi'] = (!empty($data['namevi'])) ? Func::changeTitle($data['namevi']) : '';
                }

                $data['id_district'] = (int) ($data['id_district'] ?? 0);
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

            if ($id) {
                $data['date_updated'] = time();
                if (WardModel::where('id', $id)->where('type', $type)->update($data)) {
                    return transfer('Cập nhật dữ liệu thành công.', true, url('admin', ['com' => $com, 'act' => 'man', 'type' => $type],['page'=>$request->page]));
                } else {
                    return transfer('Cập nhật dữ liệu thất bại.', true, linkReferer());
                }
            } else {
                $data['date_created'] = time();
                $itemSave = WardModel::create($data);
                if (!empty($itemSave)) {
                    $id_insert = $itemSave->id;
                    response()->redirect(url('admin', ['com' => $com, 'act' => 'man', 'type' => $type],['page'=>$request->page]));
                } else {
                    response()->redirect(url('admin', ['com' => $com, 'act' => 'man', 'type' => $type],['page'=>$request->page]));
                }
            }
        }
    }

    public function deleteItem($com, $act, $type, Request $request)
    {
        if (!empty($request->id)) {
            $id = $request->id;
            $row = WardModel::select('id')
                ->where('id', $id)
                ->first();
            if (!empty($row)) {
                WardModel::where('id', $id)->delete();
            }
        } elseif (!empty($request->listid)) {
            $listid = explode(",", $request->listid);

            for ($i = 0; $i < count($listid); $i++) {
                $id = htmlspecialchars($listid[$i]);
                $row = WardModel::select('id')
                    ->where('id', $id)
                    ->first();
                if (!empty($row)) {
                    WardModel::where('id', $id)->delete();
                }
            }
        }
        response()->redirect(url('admin', ['com' => $com, 'act' => 'man', 'type' => $type],['page'=>$request->page]));
    }
}
