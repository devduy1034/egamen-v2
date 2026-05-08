<?php


namespace LARAVEL\Controllers\Admin;

use Illuminate\Http\Request;
use LARAVEL\Core\Support\Facades\Func;
use LARAVEL\Models\GalleryModel;
use LARAVEL\Core\Support\Facades\File;
use DB;
use LARAVEL\Models\PropertiesListModel;
use LARAVEL\Models\ProductPropertiesModel;
use LARAVEL\Models\NewslettersModel;
use LARAVEL\Models\SlugModel;
use LARAVEL\Models\ProductModel;
use LARAVEL\Models\LinkModel;
use LARAVEL\Traits\TraitSave;


class ApiController
{
    use TraitSave;
    public function __construct() {}
    public function status(Request $request)
    {
        $table = (!empty($request->table)) ? htmlspecialchars($request->table) : '';
        $id = (!empty($request->id)) ? htmlspecialchars($request->id) : 0;
        $attr = (!empty($request->attr)) ? htmlspecialchars($request->attr) : '';
        if ($id) {
            $status_detail = DB::table($table)->select('status')->where('id', $id)->first();
            $status_array = (!empty($status_detail->status)) ? explode(',', $status_detail->status) : array();
            if (array_search($attr, $status_array) !== false) {
                $key = array_search($attr, $status_array);
                unset($status_array[$key]);
            } else {
                array_push($status_array, $attr);
            }
            DB::table($table)->where('id', $id)->update(['status' => implode(',', $status_array)]);
            deleteOldThumbnails();
        }
    }

    public function resetlink(Request $request)
    {
        $link = (!empty($request->link)) ? htmlspecialchars($request->link) : '';
        return Func::checkWebsiteStatus($link);
    }

    public function deletephoto(Request $request)
    {
        $id = (!empty($request->id)) ? htmlspecialchars($request->id) : 0;
        $key = (!empty($request->key)) ? htmlspecialchars($request->key) : '';
        $upload = (!empty($request->upload)) ? htmlspecialchars($request->upload) : '';
        $table = (!empty($request->table)) ? htmlspecialchars($request->table) : '';
        $table = str_replace('-', '_', $table);
        if(!empty($id) && !empty($table)){
            $row = DB::table($table)->select('*')->where('id', $id)->first();
            if (File::exists(upload($upload, $row->$key, true))) File::delete(upload($upload, $row->$key, true));
            $data[$key] = '';
            DB::table($table)->where('id', $id)->update($data);
        }
    }

    public function checklink(Request $request)
    {
        LinkModel::where('id', '<>', 0)->delete();

        foreach (config('type.table') as $table) {
            $data = DB::table($table)->get()->map(function ($data) use ($table) {
                $data->table = $table;
                return $data;
            });
            $row_array[] = $data;
        }

        $combined =  collect($row_array);
        foreach ($combined->flatten() as $row) {
            foreach (config('type.link_content') as $key => $value) {
                foreach (config('app.langs') as $k => $v) {
                    if (isset($row->{$value . $k}) && !empty($row->{$value . $k})) {
                        $dataLink = array();
                        $links = Func::contentHtml($row->{$value . $k});
                        if (!empty($links)) {
                            foreach ($links as $link) {
                                $href = $link->getAttribute('href');
                                $text = $link->nodeValue;
                                if (empty($text)) {
                                    $text = $link->querySelector('img');
                                }
                                $dataLink['id_parent'] = $row->id;
                                $dataLink['type'] = 'url';
                                $dataLink['type_link'] = 'href';
                                $dataLink['type_parent'] = $row->type;
                                $dataLink['namevi'] = $row->namevi;
                                $dataLink['slugvi'] = $row->slugvi;
                                $dataLink['content'] = $text;
                                $dataLink['link'] = $href;
                                $dataLink['table'] = $row->table;
                                $dataLink['field'] = $value . $k;
                                LinkModel::create($dataLink);
                            }
                            // foreach ($links['img'] as $link) {
                            //     $src = $link->getAttribute('src');
                            //     $text = $link;

                            //     $dataLink['id_parent'] = $row->id;
                            //     $dataLink['type'] = 'url';
                            //     $dataLink['type_link'] = 'src';
                            //     $dataLink['type_parent'] = $row->type;
                            //     $dataLink['namevi'] = $row->namevi;
                            //     $dataLink['slugvi'] = $row->slugvi;
                            //     $dataLink['content'] = $text;
                            //     $dataLink['link'] = $src;
                            //     $dataLink['table'] = $row->table;
                            //     $dataLink['field'] = $value . $k;
                            //     LinkModel::create($dataLink);
                            // }
                        }
                        unset($dataLink);
                    }
                }
            }
        };
    }

    public function readmail(Request $request)
    {
        $check = (!empty($request->check)) ? htmlspecialchars($request->check) : '';
        $id = (!empty($request->id)) ? htmlspecialchars($request->id) : 0;

        if (!empty($id)) {
            if (!empty($check)) {
                $data['confirm_status'] = 1;
            } else {
                $data['confirm_status'] = 2;
            }
            NewslettersModel::where('id', $id)->update($data);

            $row = NewslettersModel::select('id')
                ->where('confirm_status', 1)
                ->count();
        }

        echo $row;
    }


    public function slug(Request $request)
    {
        $dataSlug = array();
        $dataSlug['slug'] = (!empty($request->slug)) ? trim(htmlspecialchars($request->slug)) : '';
        $dataSlug['id'] = (!empty($request->id)) ? htmlspecialchars($request->id) : 0;
        $dataSlug['copy'] = (!empty($request->copy)) ? htmlspecialchars($request->copy) : 0;
        echo (Func::checkSlug($dataSlug) == 'exist') ? 0 : 1;
    }
    public function copy(Request $request)
    {

        $dataCopy = array();
        $dataSlug = array();
        $table = (!empty($request->table)) ? trim(htmlspecialchars($request->table)) : '';
        $id = (!empty($request->id)) ? trim(htmlspecialchars($request->id)) : 0;
        $com = (!empty($request->com)) ? trim(htmlspecialchars($request->com)) : '';
        $type = (!empty($request->type)) ? trim(htmlspecialchars($request->type)) : '';
        $row = DB::table($table)->select('*')->where('id', $id)->first();
        $check = DB::table($table)->select('id')->orderBy('id', 'desc')->first();
        $slugcopy = SlugModel::select('id', 'controller')->where('id_parent', $id)->orderBy('id', 'desc')->first();


        if (!empty($row)) {
            deleteOldThumbnails();
            $row = get_object_vars($row);
            $check = get_object_vars($check);

            foreach (config('app.langs') as $k => $v) {
                $dataCopy['name' . $k] = $row['name' . $k] . '-' . $check['id'] + 1;
                $dataCopy['slug' . $k] = $row['slug' . $k] . '-' . $check['id'] + 1;
                if (!empty($row['desc' . $k])) {
                    $dataCopy['desc' . $k] = $row['desc' . $k];
                }
            }
            if (!empty($row['id_list'])) {
                $dataCopy['id_list'] = $row['id_list'];
            }
            if (!empty($row['id_cat'])) {
                $dataCopy['id_cat'] = $row['id_cat'];
            }
            if (!empty($row['id_item'])) {
                $dataCopy['id_item'] = $row['id_item'];
            }
            if (!empty($row['id_sub'])) {
                $dataCopy['id_sub'] = $row['id_sub'];
            }

            if (!empty($row['photo'])) {
                $dataCopy['photo'] = Func::copyImg($row['photo'], $table);
            }

            $dataCopy['date_created'] = time();
            $dataCopy['type'] = $row['type'];
            $dataCopy['numb'] = $row['numb'];

            $checkCopy = DB::table($table)->insert($dataCopy);

            if (!empty($checkCopy)) {
                $rowSlug = DB::table($table)->select('*')->orderBy('id', 'desc')->first();
                $rowSlug = get_object_vars($rowSlug);
                $id = $rowSlug['id'];
                $act = 'save';
                foreach (config('app.langs') as $k => $v) {
                    $dataSlug['slug' . $k] = $rowSlug['slug' . $k];
                }
                $this->insertSlug($com, $act, $type, $id, $dataSlug, $slugcopy['controller']);
            }
        }
    }

    public function filer(Request $request)
    {

        $cmd = (!empty($request->cmd)) ? trim(htmlspecialchars($request->cmd)) : '';
        $id = (!empty($request->id)) ? trim(htmlspecialchars($request->id)) : 0;
        $com = (!empty($request->com)) ? trim(htmlspecialchars($request->com)) : '';
        $type = (!empty($request->type)) ? trim(htmlspecialchars($request->type)) : '';
        $id_parent = (!empty($request->id_parent)) ? trim(htmlspecialchars($request->id_parent)) : 0;
        $folder = (!empty($request->folder)) ? trim(htmlspecialchars($request->folder)) : '';
        $info = (!empty($request->info)) ? trim(htmlspecialchars($request->info)) : '';
        $value = (!empty($request->value)) ? trim(htmlspecialchars($request->value)) : '';
        $listid = (!empty($request->listid)) ? $request->listid : array();

        if ($cmd == 'delete' && $id > 0) {

            $row = GalleryModel::select('photo')->where('id', $id)->first();
            if (!empty($row)) {
                if (File::exists(upload($folder, $row['photo'], true))) {
                    File::delete(upload($folder, $row['photo'], true));
                }
            }
            GalleryModel::where('id', $id)->delete();
        } else if ($cmd == 'edit' && $id > 0) {
            $gallery = array();
            $gallery[$info] = $value;
            GalleryModel::where('id', $id)->update($gallery);
        } else if ($cmd == 'delete-all' && $listid != '') {

            $listid = explode(",", $listid);

            for ($i = 0; $i < count($listid); $i++) {
                $row = GalleryModel::select('id', 'photo')->where('id', $listid[$i])->first();
                if (!empty($row)) {
                    if (File::exists(upload($folder, $row['photo'], true))) {
                        File::delete(upload($folder, $row['photo'], true));
                    }
                    GalleryModel::where('id', $row['id'])->delete();
                }
            }
        } else if ($cmd == 'updateNumb') {
            if ($id_parent) {
                $row = GalleryModel::select('id', 'numb')
                    ->where('id_parent', $id_parent)
                    ->where('com', $com)
                    ->where('type', $type)
                    ->where('type_parent', $type)
                    ->orderBy('numb', 'asc')
                    ->orderBy('id', 'asc')
                    ->get();
            }
          
            if ($listid) {
                for ($i = 0; $i < count($listid); $i++) {
                    $arrId[] = $listid[$i];
                    $arrNumb[] = $row[$i]['numb'];
                    $data['numb'] = $row[$i]['numb'];
                    GalleryModel::where('id', $listid[$i])->update($data);
                }
                $data = array('id' => $arrId, 'numb' => $arrNumb);
                echo json_encode($data);
            }
            
        }
    }

    public function category(Request $request)
    {

        if (!empty($request->id)) {
            $level = (!empty($request->level)) ? htmlspecialchars($request->level) : 0;
            $table = (!empty($request->table)) ? htmlspecialchars($request->table) : '';
            $id = (!empty($request->id)) ? htmlspecialchars($request->id) : 0;
            $type = (!empty($request->type)) ? htmlspecialchars($request->type) : '';
            $row = null;

            switch ($level) {
                case '0':
                    if ($table == 'district') {
                        $id_temp = "id_city";
                    } else {
                        $id_temp = "id_list";
                    }
                    break;

                case '1':
                    $id_temp = "id_cat";
                    break;

                case '2':
                    $id_temp = "id_item";
                    break;

                default:
                    echo 'error ajax';
                    exit();
                    break;
            }

            $row = DB::table($table)->select('namevi', 'id')
                ->where([$id_temp => $id, 'type' => $type])
                ->get();

            $str = '<option value="0">Chọn danh mục</option>';
            if (!empty($row)) {
                foreach ((array)json_decode($row, true) as $v) {
                    $str .= '<option value=' . $v["id"] . '>' . $v["namevi"] . '</option>';
                }
            }
        } else {
            $str = '<option value="0">Chọn danh mục</option>';
        }

        echo $str;
    }

    public function categoryGroup(Request $request)
    {

        if (!empty($request->id)) {
            $level = (!empty($request->level)) ? htmlspecialchars($request->level) : 0;
            $table = (!empty($request->table)) ? htmlspecialchars($request->table) : '';
            $id = (!empty($request->id)) ? $request->id : array();

            $type = (!empty($request->type)) ? htmlspecialchars($request->type) : '';
            $row = null;

            switch ($level) {
                case '0':
                    $id_temp = "id_list";
                    $tableParent = 'product_list';
                    break;

                case '1':
                    $id_temp = "id_cat";
                    $tableParent = 'product_cat';
                    break;

                case '2':
                    $id_temp = "id_item";
                    $tableParent = 'product_item';
                    break;

                default:
                    echo 'error ajax';
                    exit();
                    break;
            }

            $row = DB::table($tableParent)->select('namevi', 'id')
                ->whereIN('id', $id)
                ->get()->map(function ($v, $k) use ($table, $id_temp) {
                    $v->cats = DB::table($table)->select('namevi', 'id')->where($id_temp, $v->id)->get();
                    return $v;
                });

            $str = '';

            if (!empty($row)) {
                $row = (array)json_decode($row, true);
                foreach ($row as $v) {
                    if (!empty($v['cats'])) {
                        $str .= '<optgroup label="' . $v['namevi'] . '">';
                        foreach ($v['cats'] as $value) {
                            $str .= '<option value=' . $value["id"] . '>' . $value["namevi"] . '</option>';
                        }
                        $str .= '</optgroup>';
                    }
                }
            }
        } else {
            $str = '<option value="0">Chọn danh mục</option>';
        }

        echo $str;
    }
    public function database(Request $request)
    {
        $action = (!empty($request->action)) ? htmlspecialchars($request->action) : '';
        $result = array();
        $tables = array();

        if ($action) {

            $tables = DB::select('SHOW TABLES');

            if (!empty($tables)) {
                $result = Func::databaseMaintenance($action, $tables);
            }
        }

        echo json_encode($result);
    }

    public function numb(Request $request)
    {

        $table = (!empty($request->table)) ? htmlspecialchars($request->table) : '';
        $id = (!empty($request->id)) ? htmlspecialchars($request->id) : 0;
        $value = (!empty($request->value)) ? htmlspecialchars($request->value) : '';
        if ($id) {
            $data['numb'] = $value;
            DB::table($table)->where('id', $id)->update($data);
        }
    }

    public function place(Request $request)
    {
        if (!empty($request->id)) {
            $level = (!empty($request->level)) ? htmlspecialchars($request->level) : 0;
            $table = (!empty($request->table)) ? htmlspecialchars($request->table) : '';
            $id = (!empty($request->id)) ? htmlspecialchars($request->id) : 0;
            $row = null;

            switch ($level) {
                case '0':
                    $id_temp = "id_city";
                    break;

                case '1':
                    $id_temp = "id_district";
                    break;

                default:
                    echo 'error ajax';
                    exit();
                    break;
            }

            if ($id) {
                $row = DB::table($table)->select('namevi', 'id')
                    ->where([$id_temp => $id])
                    ->get();
            }

            $str = '<option value="0">Chọn danh mục</option>';
            if (!empty($row)) {
                foreach ((array)json_decode($row, true) as $v) {
                    $str .= '<option value=' . $v["id"] . '>' . $v["namevi"] . '</option>';
                }
            }
        } else {
            $str = '<option value="0">Chọn danh mục</option>';
        }

        echo $str;
    }

    public function propertiesList(Request $request)
    {
        $id = (!empty($request->id)) ? $request->id : 0;
        $propertieslist = PropertiesListModel::select('id', 'namevi')
            ->where('type', 'san-pham')
            ->whereRaw("FIND_IN_SET(?,status)", ['hienthi'])
            ->whereRaw("FIND_IN_SET(?,id_list)", $id)
            ->get();
        view('component.propertiesList', ['propertieslist' => $propertieslist]);
    }

    public function properties(Request $request)
    {
        $arrays = (!empty($request->properties)) ? $request->properties : '';

        if (!empty($arrays)) {
            foreach ($arrays as $key => $value) {
                if (empty(Func::checkCart($key))) {
                    unset($arrays[$key]);
                }
            }
        }

        $id_product = (!empty($request->id)) ? $request->id : 0;
        $data = (!empty($request->data)) ? $request->data : '';
        $type = (!empty($data['type'])) ? $data['type'] : 'san-pham';
        $gallery = [];
        if (!empty($id_product)) {
            $gallery = GalleryModel::select('id', 'photo')
                ->where('id_parent', $id_product)
                ->where('type_parent', $type)
                ->orderBy('numb', 'asc')
                ->get();
        }
        $regular_price = 0;
        $sale_price = 0;
        $number = 0;
        $status = '';
        if (!empty($arrays)) {
            $combined_array = array();
            function combine_arrays($arrays, &$combined_array, $current_array = array())
            {
                if (count($arrays) == 0) {
                    $combined_array[] = $current_array;
                } else {
                    $current_options = array_shift($arrays);
                    foreach ($current_options as $option) {
                        $temp_array = $current_array;
                        $temp_array[] = $option;
                        combine_arrays($arrays, $combined_array, $temp_array);
                    }
                }
            }
            combine_arrays($arrays, $combined_array);
        }

        if (!empty($combined_array)) {
            foreach ($combined_array as $key => $v) {

                $namevi = Func::nameProper($v);

                $id_properties = implode(',', $v);

                $code = md5($id_properties);


                if (!empty($id_product)) {
                    $price = ProductPropertiesModel::select('id', 'regular_price', 'sale_price', 'number', 'status')
                        ->where('id_parent', $id_product)
                        ->where('id_properties', $id_properties)
                        ->where('namevi', $namevi)
                        ->first();
                }
                if (!empty($price)) {
                    $regular_price = $price['regular_price'];
                    $sale_price = $price['sale_price'];
                    $number = $price['number'];
                    $status = $price['status'];
                } else {
                    $regular_price = $data['regular_price'];
                    $sale_price = $data['sale_price'];
                    $number = $key + 1;
                    $status = '';
                }

                $propertiescard['id_properties'] = $id_properties;
                $propertiescard['namevi'] = $namevi;
                $propertiescard['regular_price'] = $regular_price;
                $propertiescard['sale_price'] = $sale_price;
                $propertiescard['number'] = $number;
                $propertiescard['status'] = $status;

                view('component.propertiesCard', ['value' => $propertiescard, 'key' => $key, 'code' => $code, 'gallery' => $gallery]);
            }
        }
    }

    public function productGallery(Request $request)
    {
        $id_product = (!empty($request->id)) ? (int)$request->id : 0;
        $type = (!empty($request->type)) ? $request->type : 'san-pham';
        $items = [];

        if (!empty($id_product)) {
            $items = GalleryModel::select('id', 'photo')
                ->where('id_parent', $id_product)
                ->where('type_parent', $type)
                ->orderBy('numb', 'asc')
                ->get()
                ->map(function ($item) {
                    $photo = $item->photo ?? '';
                    $thumb = !empty($photo) ? assets_photo('product','70x70x1',$photo,'thumbs') : '';
                    $full = !empty($photo) ? upload('product', $photo) : '';
                    if (empty($thumb) && !empty($full)) {
                        $thumb = $full;
                    }
                    return [
                        'id' => $item->id,
                        'photo' => $photo,
                        'thumb' => $thumb,
                        'full' => $full
                    ];
                })
                ->toArray();
        }

        echo json_encode($items);
        exit;
    }
}
