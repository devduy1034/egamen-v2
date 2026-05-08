<?php


namespace LARAVEL\Controllers\Admin;

use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use LARAVEL\Core\Support\Facades\File;
use LARAVEL\Core\Support\Facades\Flash;
use LARAVEL\Core\Support\Facades\Func;
use LARAVEL\Models\GalleryModel;
use LARAVEL\Models\ProductCatModel;
use LARAVEL\Models\ProductItemModel;
use LARAVEL\Models\ProductListModel;
use LARAVEL\Models\ProductBrandModel;
use LARAVEL\Models\ProductModel;
use LARAVEL\Models\ProductPropertiesModel;
use LARAVEL\Models\ProductSubModel;
use LARAVEL\Models\ProductTagsModel;
use LARAVEL\Models\PropertiesModel;
use LARAVEL\Models\PropertiesListModel;
use LARAVEL\Models\SeoModel;
use LARAVEL\Models\SlugModel;
use LARAVEL\Traits\TraitSave;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Helper\Sample;

class ProductController
{
    private $configType;
    private $upload;
    use TraitSave;
    public function __construct()
    {
        $this->configType = json_decode(json_encode(config('type')))->product;
    }
    /* Brand */
    public function manBrand($com, $act, $type, Request $request)
    {
        if ($request->isMethod('get') && !empty($request->keyword)) {
            $keyword = $request->keyword;
        }
        $query = ProductBrandModel::select('id', 'namevi', 'photo', 'descvi', 'slugvi', 'status', 'numb')
            ->where('type', $type);
        if (!empty($keyword)) $query->where('namevi', 'like', '%' . $keyword . '%');
        $items = $query
            ->orderBy('numb', 'asc')
            ->orderBy('id', 'desc')
            ->paginate(10);
        return view('product.brand.man', ['items' => $items]);
    }
    public function editBrand($com, $act, $type, Request $request)
    {
        $id = $request->id;
        $item = [];
        if (!empty($id)) {
            $item = ProductBrandModel::select('*')
                ->where('type', $type)
                ->where('id', $id)
                ->orderBy('numb', 'asc')
                ->first();
        }
        return view('product.brand.add', ['item' => $item]);
    }
    public function saveBrand($com, $act, $type, Request $request)
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

                if (!empty($this->configType->$type->brand->slug_brand)) {
                    if (!empty($request->slugvi)) $data['slugvi'] = Func::changeTitle(htmlspecialchars($request->slugvi));
                    else $data['slugvi'] = (!empty($data['namevi'])) ? Func::changeTitle($data['namevi']) : '';
                    if (!empty($request->slugen)) $data['slugen'] = Func::changeTitle(htmlspecialchars($request->slugen));
                    else $data['slugen'] = (!empty($data['nameen'])) ? Func::changeTitle($data['nameen']) : '';
                }
                $data['type'] = $type;
            }
            if (!empty($this->configType->$type->brand->seo_brand)) {
                $dataSeo = $this->getSeo($request);
            }
            if (!empty($this->configType->$type->brand->slug_brand)) {
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
                $response['status'] = 'danger';
                $message = base64_encode(json_encode($response));
                Flash::set('message', $message);
                response()->redirect(linkReferer());
            }

            if ($id) {
                $data['date_updated'] = time();
                if (ProductBrandModel::where('id', $id)->where('type', $type)->update($data)) {

                    /* IMAGE */
                    if (!empty($this->configType->$type->brand->images)) {
                        foreach ($this->configType->$type->categories->list->images as $key => $photo) {
                            $file = $request->file('file-' . $key);
                            $cropFile = $request->{"cropFile-$key"};
                            if (!empty($cropFile)) {
                                $this->insertImgeCrop(ProductBrandModel::class, $request, $file, $cropFile, $id, 'product', $key);
                            } else {
                                $this->insertImge(ProductBrandModel::class, $request, $file, $id, 'product', $key);
                            }
                        }
                    }

                    if (!empty($this->configType->$type->brand->seo_brand)) {
                        $this->insertSeo($com, $act, $type, $id, $dataSeo);
                    }
                    if (!empty($this->configType->$type->brand->slug_brand)) {
                        foreach (config('app.slugs') as $k => $v) {
                            $dataSlug['slug' . $k] = $data['slug' . $k];
                            $dataSlug['name' . $k] = $data['name' . $k];
                        }
                        $this->insertSlug($com, $act, $type, $id, $dataSlug);
                    }
                    return $this->linkRequest($com, 'man', $type, $id, $request);
                } else {
                    return transfer('Cập nhật dữ liệu thất bại.', false, linkReferer());
                }
            } else {
                $data['date_created'] = time();
                $itemSave = ProductBrandModel::create($data);
                if (!empty($itemSave)) {
                    $id_insert = $itemSave->id;

                    /* IMAGE */
                    if (!empty($this->configType->$type->brand->images)) {
                        foreach ($this->configType->$type->brand->images as $key => $photo) {
                            $file = $request->file('file-' . $key);
                            $cropFile = $request->{"cropFile-$key"};
                            if (!empty($cropFile)) {
                                $this->insertImgeCrop(ProductBrandModel::class, $request, $file, $cropFile, $id_insert, 'product', $key);
                            } else {
                                $this->insertImge(ProductBrandModel::class, $request, $file, $id_insert, 'product', $key);
                            }
                        }
                    }

                    if (!empty($this->configType->$type->brand->seo_brand)) {
                        $this->insertSeo($com, $act, $type, $id_insert, $dataSeo);
                    }
                    if (!empty($this->configType->$type->brand->slug_brand)) {
                        foreach (config('app.slugs') as $k => $v) {
                            $dataSlug['slug' . $k] = $data['slug' . $k];
                            $dataSlug['name' . $k] = $data['name' . $k];
                        }
                        $this->insertSlug($com, $act, $type, $id_insert, $dataSlug);
                    }
                    return $this->linkRequest($com, 'man', $type, $id_insert, $request);
                } else {
                    response()->redirect(url('admin', ['com' => $com, 'act' => 'man', 'type' => $type], ['page' => $request->page]));
                }
            }
        }
    }

    public function deleteBrand($com, $act, $type, Request $request)
    {
        if (!empty($request->id)) {
            $id = $request->id;
            $row = ProductBrandModel::select('id', 'photo')->where('id', $id)->first();
            if (!empty($row)) {
                if (File::exists(upload('product', $row['photo'], true))) File::delete(upload('product', $row['photo'], true));
                ProductBrandModel::where('id', $id)->delete();
            }
            SlugModel::where('id_parent', $id)->where('com', $com)->where('type', $type)->delete();
            SeoModel::where('id_parent', $id)->where('com', $com)->where('type', $type)->delete();
        } elseif (!empty($request->listid)) {
            $listid = explode(",", $request->listid);
            for ($i = 0; $i < count($listid); $i++) {
                $id = htmlspecialchars($listid[$i]);
                $row = ProductBrandModel::select('id', 'photo')->where('id', $id)->first();
                if (!empty($row)) {
                    if (File::exists(upload('product', $row['photo'], true))) File::delete(upload('product', $row['photo'], true));
                    ProductBrandModel::where('id', $id)->delete();
                }
                SlugModel::where('id_parent', $id)->where('com', $com)->where('type', $type)->delete();
                SeoModel::where('id_parent', $id)->where('com', $com)->where('type', $type)->delete();
            }
        }
        response()->redirect(url('admin', ['com' => $com, 'act' => 'man', 'type' => $type], ['page' => $request->page]));
    }
    /* List */
    public function manList($com, $act, $type, Request $request)
    {
        if ($request->isMethod('get') && !empty($request->keyword)) {
            $keyword = $request->keyword;
        }
        $query = ProductListModel::select('id', 'namevi', 'photo', 'descvi', 'slugvi', 'status', 'numb')
            ->where('type', $type);
        if (!empty($keyword)) $query->where('namevi', 'like', '%' . $keyword . '%');
        $items = $query
            ->orderBy('numb', 'asc')
            ->orderBy('id', 'desc')
            ->paginate(10);
        return view('product.list.man', ['items' => $items]);
    }

    public function editList($com, $act, $type, Request $request)
    {
        $id = $request->id;
        $item = [];
        $gallery = [];

        if (!empty($id)) {
            $item = ProductListModel::select('*')
                ->where('type', $type)
                ->where('id', $id)
                ->orderBy('numb', 'asc')
                ->first();
        }

        if (!empty($this->configType->$type->categories->list->gallery_categories)) {
            $gallery = GalleryModel::select('*')
                ->where('com', $com)
                ->where('type', $type)
                ->where('type_parent', $type)
                ->where('id_parent', $id)
                ->orderBy('numb', 'asc')
                ->get();
        }

        return view('product.list.add', ['item' => $item, 'gallery' => $gallery]);
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
                    foreach (config('app.slugs') as $k => $v) {
                        if (!empty($request->{'slug' . $k})) $data['slug' . $k] = Func::changeTitle(htmlspecialchars($request->{'slug' . $k}));
                        else $data['slug' . $k] = (!empty($data['name' . $k])) ? Func::changeTitle($data['name' . $k]) : '';
                    }
                }


                $data['type'] = $type;
            }

            /* Post seo */
            if (!empty($this->configType->$type->categories->list->seo_categories)) {
                $dataSeo = $this->getSeo($request);
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
                if (ProductListModel::where('id', $id)->where('type', $type)->update($data)) {

                    /* IMAGE */
                    if (!empty($this->configType->$type->categories->list->images)) {
                        foreach ($this->configType->$type->categories->list->images as $key => $photo) {
                            $file = $request->file('file-' . $key);
                            $cropFile = $request->{"cropFile-$key"};
                            if (!empty($cropFile)) {
                                $this->insertImgeCrop(ProductListModel::class, $request, $file, $cropFile, $id, 'product', $key);
                            } else {
                                $this->insertImge(ProductListModel::class, $request, $file, $id, 'product', $key);
                            }
                        }
                    }

                    /* ALBUM */
                    if (!empty($this->configType->$type->categories->list->gallery_categories)) {
                        $files = (!empty($request->file('files'))) ? $request->file('files') : null;
                        $this->insertImges(GalleryModel::class, $request, $files, $id, $com, $type, $type, 'product');
                    }

                    /* SEO */
                    if (!empty($this->configType->$type->categories->list->seo_categories)) {
                        $this->insertSeo($com, $act, $type, $id, $dataSeo);
                    }

                    /* SLUG */
                    if (!empty($this->configType->$type->categories->list->slug_categories)) {
                        foreach (config('app.slugs') as $k => $v) {
                            $dataSlug['slug' . $k] = $data['slug' . $k];
                            $dataSlug['name' . $k] = $data['name' . $k];
                        }
                        $this->insertSlug($com, $act, $type, $id, $dataSlug);
                    }
                    return $this->linkRequest($com, $act, $type, $id, $request);
                } else {
                    return transfer('Cập nhật dữ liệu thất bại.', false, linkReferer());
                }
            } else {
                $data['date_created'] = time();
                $itemSave = ProductListModel::create($data);
                if (!empty($itemSave)) {
                    $id_insert = $itemSave->id;

                    /* IMAGE */
                    if (!empty($this->configType->$type->categories->list->images)) {
                        foreach ($this->configType->$type->categories->list->images as $key => $photo) {
                            $file = $request->file('file-' . $key);
                            $cropFile = $request->{"cropFile-$key"};
                            if (!empty($cropFile)) {
                                $this->insertImgeCrop(ProductListModel::class, $request, $file, $cropFile, $id_insert, 'product', $key);
                            } else {
                                $this->insertImge(ProductListModel::class, $request, $file, $id_insert, 'product', $key);
                            }
                        }
                    }

                    /* ALBUM */
                    if (!empty($this->configType->$type->categories->list->gallery_categories)) {
                        $files = (!empty($request->file('files'))) ? $request->file('files') : null;
                        $this->insertImges(GalleryModel::class, $request, $files, $id_insert, $com, $type, $type, 'product');
                    }

                    /* SEO */
                    if (!empty($this->configType->$type->categories->list->seo_categories)) {
                        $this->insertSeo($com, $act, $type, $id_insert, $dataSeo);
                    }

                    /* SLUG */
                    if (!empty($this->configType->$type->categories->list->slug_categories)) {
                        foreach (config('app.slugs') as $k => $v) {
                            $dataSlug['slug' . $k] = $data['slug' . $k];
                            $dataSlug['name' . $k] = $data['name' . $k];
                        }
                        $this->insertSlug($com, $act, $type, $id_insert, $dataSlug);
                    }
                    return $this->linkRequest($com, $act, $type, $id_insert, $request);
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

            $row = ProductListModel::select('id', 'photo')
                ->where('id', $id)
                ->first();
            $rowGallery = GalleryModel::select('id', 'photo')->where('id_parent', $id)->where('com', $com)->where('type', $type)->get();
            if (!empty($row)) {
                if (File::exists(upload('product', $row['photo'], true))) {
                    File::delete(upload('product', $row['photo'], true));
                }
                ProductListModel::where('id', $id)->delete();
            }
            if (!empty($rowGallery)) {
                foreach ($rowGallery as $v) {
                    if (File::exists(upload('product', $v['photo'], true))) {
                        File::delete(upload('product', $v['photo'], true));
                    }
                }
                GalleryModel::where('id_parent', $id)->where('com', $com)->where('type', $type)->delete();
            }

            SlugModel::where('id_parent', $id)->where('com', $com)->where('type', $type)->delete();
            SeoModel::where('id_parent', $id)->where('com', $com)->where('type', $type)->delete();
        } elseif (!empty($request->listid)) {
            $listid = explode(",", $request->listid);

            for ($i = 0; $i < count($listid); $i++) {
                $id = htmlspecialchars($listid[$i]);

                $row = ProductListModel::select('id', 'photo')
                    ->where('id', $id)
                    ->first();
                $rowGallery = GalleryModel::select('id', 'photo')->where('id_parent', $id)->where('com', $com)->where('type', $type)->get();
                if (!empty($row)) {
                    if (File::exists(upload('product', $row['photo'], true))) {
                        File::delete(upload('product', $row['photo'], true));
                    }
                    ProductListModel::where('id', $id)->delete();
                }
                if (!empty($rowGallery)) {
                    foreach ($rowGallery as $v) {
                        if (File::exists(upload('product', $v['photo'], true))) {
                            File::delete(upload('product', $v['photo'], true));
                        }
                    }
                    GalleryModel::where('id_parent', $id)->where('com', $com)->where('type', $type)->delete();
                }


                SlugModel::where('id_parent', $id)->where('com', $com)->where('type', $type)->delete();
                SeoModel::where('id_parent', $id)->where('com', $com)->where('type', $type)->delete();
            }
        }
        response()->redirect(url('admin', ['com' => $com, 'act' => 'man', 'type' => $type], ['page' => $request->page]));
    }

    /* Cat */

    public function manCat($com, $act, $type, Request $request)
    {

        if ($request->isMethod('get') && !empty($request->keyword)) {
            $keyword = $request->keyword;
        }

        if ($request->isMethod('get') && !empty($request->id_list)) {
            $id_list = $request->id_list;
        }

        $query = ProductCatModel::select('id', 'id_list', 'namevi', 'photo', 'descvi', 'slugvi', 'status', 'numb')
            ->where('type', $type);
        if (!empty($keyword)) $query->where('namevi', 'like', '%' . $keyword . '%');
        if (!empty($id_list)) $query->where('id_list', '=', $id_list);
        $items = $query
            ->orderBy('numb', 'asc')
            ->orderBy('id', 'desc')
            ->paginate(10);
        return view('product.cat.man', ['items' => $items]);
    }

    public function editCat($com, $act, $type, Request $request)
    {
        $id = $request->id;
        $item = [];
        $gallery = [];

        if (!empty($id)) {
            $item = ProductCatModel::select('*')
                ->where('type', $type)
                ->where('id', $id)
                ->orderBy('numb', 'asc')
                ->first();
        }

        if (!empty($this->configType->$type->categories->cat->gallery_categories)) {
            $gallery = GalleryModel::select('*')
                ->where('com', $com)
                ->where('type', $type)
                ->where('type_parent', $type)
                ->where('id_parent', $id)
                ->orderBy('numb', 'asc')
                ->get();
        }

        return view('product.cat.add', ['item' => $item, 'gallery' => $gallery]);
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
                    foreach (config('app.slugs') as $k => $v) {
                        if (!empty($request->{'slug' . $k})) $data['slug' . $k] = Func::changeTitle(htmlspecialchars($request->{'slug' . $k}));
                        else $data['slug' . $k] = (!empty($data['name' . $k])) ? Func::changeTitle($data['name' . $k]) : '';
                    }
                }

                $data['type'] = $type;
            }

            /* Post seo */
            if (!empty($this->configType->$type->categories->cat->seo_categories)) {
                $dataSeo = $this->getSeo($request);
            }

            if (!empty($this->configType->$type->categories->cat->slug_categories)) {
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
                if (ProductCatModel::where('id', $id)->where('type', $type)->update($data)) {

                    /* IMAGE */
                    if (!empty($this->configType->$type->categories->cat->images)) {
                        foreach ($this->configType->$type->categories->cat->images as $key => $photo) {
                            $file = $request->file('file-' . $key);
                            $cropFile = $request->{"cropFile-$key"};
                            if (!empty($cropFile)) {
                                $this->insertImgeCrop(ProductCatModel::class, $request, $file, $cropFile, $id, 'product', $key);
                            } else {
                                $this->insertImge(ProductCatModel::class, $request, $file, $id, 'product', $key);
                            }
                        }
                    }

                    /* ALBUM */
                    if (!empty($this->configType->$type->categories->cat->gallery_categories)) {
                        $files = (!empty($request->file('files'))) ? $request->file('files') : null;
                        $this->insertImges(GalleryModel::class, $request, $files, $id, $com, $type, $type, 'product');
                    }

                    /* SEO */
                    if (!empty($this->configType->$type->categories->cat->seo_categories)) {
                        $this->insertSeo($com, $act, $type, $id, $dataSeo);
                    }

                    /* SLUG */
                    if (!empty($this->configType->$type->categories->cat->slug_categories)) {
                        foreach (config('app.slugs') as $k => $v) {
                            $dataSlug['slug' . $k] = $data['slug' . $k];
                            $dataSlug['name' . $k] = $data['name' . $k];
                        }
                        $this->insertSlug($com, $act, $type, $id, $dataSlug);
                    }
                    return $this->linkRequest($com, $act, $type, $id, $request);
                } else {
                    return transfer('Cập nhật dữ liệu thất bại.', true, linkReferer());
                }
            } else {
                $data['date_created'] = time();
                $itemSave = ProductCatModel::create($data);
                if (!empty($itemSave)) {
                    $id_insert = $itemSave->id;

                    /* IMAGE */
                    if (!empty($this->configType->$type->categories->cat->images)) {
                        foreach ($this->configType->$type->categories->cat->images as $key => $photo) {

                            $file = $request->file('file-' . $key);
                            $cropFile = $request->{"cropFile-$key"};
                            if (!empty($cropFile)) {
                                $this->insertImgeCrop(ProductCatModel::class, $request, $file, $cropFile, $id_insert, 'product', $key);
                            } else {
                                $this->insertImge(ProductCatModel::class, $request, $file, $id_insert, 'product', $key);
                            }
                        }
                    }

                    /* ALBUM */
                    if (!empty($this->configType->$type->categories->cat->gallery_categories)) {
                        $files = (!empty($request->file('files'))) ? $request->file('files') : null;
                        $this->insertImges(GalleryModel::class, $request, $files, $id_insert, $com, $type, $type, 'product');
                    }

                    /* SEO */
                    if (!empty($this->configType->$type->categories->cat->seo_categories)) {
                        $this->insertSeo($com, $act, $type, $id_insert, $dataSeo);
                    }

                    /* SLUG */
                    if (!empty($this->configType->$type->categories->cat->slug_categories)) {
                        foreach (config('app.slugs') as $k => $v) {
                            $dataSlug['slug' . $k] = $data['slug' . $k];
                            $dataSlug['name' . $k] = $data['name' . $k];
                        }
                        $this->insertSlug($com, $act, $type, $id_insert, $dataSlug);
                    }
                    return $this->linkRequest($com, $act, $type, $id_insert, $request);
                } else {
                    response()->redirect(url('admin', ['com' => $com, 'act' => 'man', 'type' => $type], ['page' => $request->page]));
                }
            }
        }
    }

    public function deleteCat($com, $act, $type, Request $request)
    {
        $listid = $request->listid ? explode(",", $request->listid) : [$request->id];
        if (!empty($request->id)) {
            $id = $request->id;
            $row = ProductCatModel::select('id', 'photo')
                ->where('id', $id)
                ->first();
            $rowGallery = GalleryModel::select('id', 'photo')->where('id_parent', $id)->where('com', $com)->where('type', $type)->get();
            if (!empty($row)) {
                if (File::exists(upload('product', $row['photo'], true))) {
                    File::delete(upload('product', $row['photo'], true));
                }
                ProductCatModel::where('id', $id)->delete();
            }
            if (!empty($rowGallery)) {
                foreach ($rowGallery as $v) {
                    if (File::exists(upload('product', $v['photo'], true))) {
                        File::delete(upload('product', $v['photo'], true));
                    }
                }
                GalleryModel::where('id_parent', $id)->where('com', $com)->where('type', $type)->delete();
            }

            SlugModel::where('id_parent', $id)->where('com', $com)->where('type', $type)->delete();
            SeoModel::where('id_parent', $id)->where('com', $com)->where('type', $type)->delete();
        } elseif (!empty($request->listid)) {
            $listid = explode(",", $request->listid);

            for ($i = 0; $i < count($listid); $i++) {
                $id = htmlspecialchars($listid[$i]);
                $row = ProductCatModel::select('id', 'photo')
                    ->where('id', $id)
                    ->first();
                $rowGallery = GalleryModel::select('id', 'photo')->where('id_parent', $id)->where('com', $com)->where('type', $type)->get();
                if (!empty($row)) {
                    if (File::exists(upload('product', $row['photo'], true))) {
                        File::delete(upload('product', $row['photo'], true));
                    }
                    ProductCatModel::where('id', $id)->delete();
                }
                if (!empty($rowGallery)) {
                    foreach ($rowGallery as $v) {
                        if (File::exists(upload('product', $v['photo'], true))) {
                            File::delete(upload('product', $v['photo'], true));
                        }
                    }
                    GalleryModel::where('id_parent', $id)->where('com', $com)->where('type', $type)->delete();
                }
                SlugModel::where('id_parent', $id)->where('com', $com)->where('type', $type)->delete();
                SeoModel::where('id_parent', $id)->where('com', $com)->where('type', $type)->delete();
            }
        }
        response()->redirect(url('admin', ['com' => $com, 'act' => 'man', 'type' => $type], ['page' => $request->page]));
    }

    /* Item */

    public function manItem($com, $act, $type, Request $request)
    {

        if ($request->isMethod('get') && !empty($request->keyword)) {
            $keyword = $request->keyword;
        }

        if ($request->isMethod('get') && !empty($request->id_list)) {
            $id_list = $request->id_list;
        }

        if ($request->isMethod('get') && !empty($request->id_cat)) {
            $id_cat = $request->id_cat;
        }

        $query = ProductItemModel::select('id', 'id_list', 'id_cat', 'namevi', 'photo', 'descvi', 'slugvi', 'status', 'numb')
            ->where('type', $type);
        if (!empty($keyword)) $query->where('namevi', 'like', '%' . $keyword . '%');
        if (!empty($id_list)) $query->where('id_list', '=', $id_list);
        if (!empty($id_cat)) $query->where('id_cat', '=', $id_cat);
        $items = $query
            ->orderBy('numb', 'asc')
            ->orderBy('id', 'desc')
            ->paginate(10);
        return view('product.item.man', ['items' => $items]);
    }

    public function editItem($com, $act, $type, Request $request)
    {
        $id = $request->id;
        $item = [];
        $gallery = [];

        if (!empty($id)) {
            $item = ProductItemModel::select('*')
                ->where('type', $type)
                ->where('id', $id)
                ->orderBy('numb', 'asc')
                ->first();
        }

        if (!empty($this->configType->$type->categories->cat->gallery_categories)) {
            $gallery = GalleryModel::select('*')
                ->where('com', $com)
                ->where('type', $type)
                ->where('type_parent', $type)
                ->where('id_parent', $id)
                ->orderBy('numb', 'asc')
                ->get();
        }

        return view('product.item.add', ['item' => $item, 'gallery' => $gallery]);
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
                    foreach (config('app.slugs') as $k => $v) {
                        if (!empty($request->{'slug' . $k})) $data['slug' . $k] = Func::changeTitle(htmlspecialchars($request->{'slug' . $k}));
                        else $data['slug' . $k] = (!empty($data['name' . $k])) ? Func::changeTitle($data['name' . $k]) : '';
                    }
                }

                $data['type'] = $type;
            }

            /* Post seo */
            if (!empty($this->configType->$type->categories->item->seo_categories)) {
                $dataSeo = $this->getSeo($request);
            }

            if (!empty($this->configType->$type->categories->item->slug_categories)) {
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
                if (ProductItemModel::where('id', $id)->where('type', $type)->update($data)) {

                    /* IMAGE */
                    if (!empty($this->configType->$type->categories->item->images)) {
                        foreach ($this->configType->$type->categories->item->images as $key => $photo) {

                            $file = $request->file('file-' . $key);
                            $cropFile = $request->{"cropFile-$key"};
                            if (!empty($cropFile)) {
                                $this->insertImgeCrop(ProductItemModel::class, $request, $file, $cropFile, $id, 'product', $key);
                            } else {
                                $this->insertImge(ProductItemModel::class, $request, $file, $id, 'product', $key);
                            }
                        }
                    }

                    /* ALBUM */
                    if (!empty($this->configType->$type->categories->item->gallery_categories)) {
                        $files = (!empty($request->file('files'))) ? $request->file('files') : null;
                        $this->insertImges(GalleryModel::class, $request, $files, $id, $com, $type, $type, 'product');
                    }

                    /* SEO */
                    if (!empty($this->configType->$type->categories->item->seo_categories)) {
                        $this->insertSeo($com, $act, $type, $id, $dataSeo);
                    }

                    /* SLUG */
                    if (!empty($this->configType->$type->categories->item->slug_categories)) {
                        foreach (config('app.slugs') as $k => $v) {
                            $dataSlug['slug' . $k] = $data['slug' . $k];
                            $dataSlug['name' . $k] = $data['name' . $k];
                        }
                        $this->insertSlug($com, $act, $type, $id, $dataSlug);
                    }
                    return $this->linkRequest($com, $act, $type, $id, $request);
                } else {
                    return transfer('Cập nhật dữ liệu thất bại.', false, linkReferer());
                }
            } else {
                $data['date_created'] = time();
                $itemSave = ProductItemModel::create($data);
                if (!empty($itemSave)) {
                    $id_insert = $itemSave->id;

                    /* IMAGE */
                    if (!empty($this->configType->$type->categories->item->images)) {
                        foreach ($this->configType->$type->categories->item->images as $key => $photo) {

                            $file = $request->file('file-' . $key);
                            $cropFile = $request->{"cropFile-$key"};
                            if (!empty($cropFile)) {
                                $this->insertImgeCrop(ProductItemModel::class, $request, $file, $cropFile, $id_insert, 'product', $key);
                            } else {
                                $this->insertImge(ProductItemModel::class, $request, $file, $id_insert, 'product', $key);
                            }
                        }
                    }

                    /* ALBUM */
                    if (!empty($this->configType->$type->categories->item->gallery_categories)) {
                        $files = (!empty($request->file('files'))) ? $request->file('files') : null;
                        $this->insertImges(GalleryModel::class, $request, $files, $id_insert, $com, $type, $type, 'product');
                    }

                    /* SEO */
                    if (!empty($this->configType->$type->categories->item->seo_categories)) {
                        $this->insertSeo($com, $act, $type, $id_insert, $dataSeo);
                    }

                    /* SLUG */
                    if (!empty($this->configType->$type->categories->item->slug_categories)) {
                        foreach (config('app.slugs') as $k => $v) {
                            $dataSlug['slug' . $k] = $data['slug' . $k];
                            $dataSlug['name' . $k] = $data['name' . $k];
                        }
                        $this->insertSlug($com, $act, $type, $id_insert, $dataSlug);
                    }
                    return $this->linkRequest($com, $act, $type, $id_insert, $request);
                } else {
                    response()->redirect(url('admin', ['com' => $com, 'act' => 'man', 'type' => $type], ['page' => $request->page]));
                }
            }
        }
    }

    public function deleteItem($com, $act, $type, Request $request)
    {
        if (!empty($request->id)) {
            $id = $request->id;

            $row = ProductItemModel::select('id', 'photo')
                ->where('id', $id)
                ->first();
            $rowGallery = GalleryModel::select('id', 'photo')->where('id_parent', $id)->where('com', $com)->where('type', $type)->get();
            if (!empty($row)) {
                if (File::exists(upload('product', $row['photo'], true))) {
                    File::delete(upload('product', $row['photo'], true));
                }
                ProductItemModel::where('id', $id)->delete();
            }
            if (!empty($rowGallery)) {
                foreach ($rowGallery as $v) {
                    if (File::exists(upload('product', $v['photo'], true))) {
                        File::delete(upload('product', $v['photo'], true));
                    }
                }
                GalleryModel::where('id_parent', $id)->where('com', $com)->where('type', $type)->delete();
            }

            SlugModel::where('id_parent', $id)->where('com', $com)->where('type', $type)->delete();
            SeoModel::where('id_parent', $id)->where('com', $com)->where('type', $type)->delete();
        } elseif (!empty($request->listid)) {
            $listid = explode(",", $request->listid);

            for ($i = 0; $i < count($listid); $i++) {
                $id = htmlspecialchars($listid[$i]);
                $row = ProductItemModel::select('id', 'photo')
                    ->where('id', $id)
                    ->first();
                $rowGallery = GalleryModel::select('id', 'photo')->where('id_parent', $id)->where('com', $com)->where('type', $type)->get();
                if (!empty($row)) {
                    if (File::exists(upload('product', $row['photo'], true))) {
                        File::delete(upload('product', $row['photo'], true));
                    }
                    ProductItemModel::where('id', $id)->delete();
                }
                if (!empty($rowGallery)) {
                    foreach ($rowGallery as $v) {
                        if (File::exists(upload('product', $v['photo'], true))) {
                            File::delete(upload('product', $v['photo'], true));
                        }
                    }
                    GalleryModel::where('id_parent', $id)->where('com', $com)->where('type', $type)->delete();
                }
                SlugModel::where('id_parent', $id)->where('com', $com)->where('type', $type)->delete();
                SeoModel::where('id_parent', $id)->where('com', $com)->where('type', $type)->delete();
            }
        }
        response()->redirect(url('admin', ['com' => $com, 'act' => 'man', 'type' => $type], ['page' => $request->page]));
    }

    /* Sub */

    public function manSub($com, $act, $type, Request $request)
    {

        if ($request->isMethod('get') && !empty($request->keyword)) {
            $keyword = $request->keyword;
        }

        if ($request->isMethod('get') && !empty($request->id_list)) {
            $id_list = $request->id_list;
        }

        if ($request->isMethod('get') && !empty($request->id_cat)) {
            $id_cat = $request->id_cat;
        }

        if ($request->isMethod('get') && !empty($request->id_item)) {
            $id_item = $request->id_item;
        }

        $query = ProductSubModel::select('id', 'id_list', 'id_cat', 'id_item', 'namevi', 'photo', 'descvi', 'slugvi', 'status', 'numb')
            ->where('type', $type);
        if (!empty($keyword)) $query->where('namevi', 'like', '%' . $keyword . '%');
        if (!empty($id_list)) $query->where('id_list', '=', $id_list);
        if (!empty($id_cat)) $query->where('id_cat', '=', $id_cat);
        if (!empty($id_item)) $query->where('id_item', '=', $id_item);
        $items = $query
            ->orderBy('numb', 'asc')
            ->orderBy('id', 'desc')
            ->paginate(10);
        return view('product.sub.man', ['items' => $items]);
    }

    public function editSub($com, $act, $type, Request $request)
    {
        $id = $request->id;
        $item = [];
        $gallery = [];

        if (!empty($id)) {
            $item = ProductSubModel::select('*')
                ->where('type', $type)
                ->where('id', $id)
                ->orderBy('numb', 'asc')
                ->first();
        }

        if (!empty($this->configType->$type->categories->sub->gallery_categories)) {
            $gallery = GalleryModel::select('*')
                ->where('com', $com)
                ->where('type', $type)
                ->where('type_parent', $type)
                ->where('id_parent', $id)
                ->orderBy('numb', 'asc')
                ->get();
        }

        return view('product.sub.add', ['item' => $item, 'gallery' => $gallery]);
    }

    public function saveSub($com, $act, $type, Request $request)
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

                if (!empty($this->configType->$type->categories->sub->slug_categories)) {
                    foreach (config('app.slugs') as $k => $v) {
                        if (!empty($request->{'slug' . $k})) $data['slug' . $k] = Func::changeTitle(htmlspecialchars($request->{'slug' . $k}));
                        else $data['slug' . $k] = (!empty($data['name' . $k])) ? Func::changeTitle($data['name' . $k]) : '';
                    }
                }

                $data['type'] = $type;
            }

            /* Post seo */
            if (!empty($this->configType->$type->categories->sub->seo_categories)) {
                $dataSeo = $this->getSeo($request);
            }

            if (!empty($this->configType->$type->categories->sub->slug_categories)) {
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
                if (ProductSubModel::where('id', $id)->where('type', $type)->update($data)) {

                    /* IMAGE */
                    if (!empty($this->configType->$type->categories->sub->images)) {
                        foreach ($this->configType->$type->categories->sub->images as $key => $photo) {
                            $file = $request->file('file-' . $key);
                            $cropFile = $request->{"cropFile-$key"};
                            if (!empty($cropFile)) {
                                $this->insertImgeCrop(ProductSubModel::class, $request, $file, $cropFile, $id, 'product', $key);
                            } else {
                                $this->insertImge(ProductSubModel::class, $request, $file, $id, 'product', $key);
                            }
                        }
                    }

                    /* ALBUM */
                    if (!empty($this->configType->$type->categories->sub->gallery_categories)) {
                        $files = (!empty($request->file('files'))) ? $request->file('files') : null;
                        $this->insertImges(GalleryModel::class, $request, $files, $id, $com, $type, $type, 'product');
                    }

                    /* SEO */
                    if (!empty($this->configType->$type->categories->sub->seo_categories)) {
                        $this->insertSeo($com, $act, $type, $id, $dataSeo);
                    }

                    /* SLUG */
                    if (!empty($this->configType->$type->categories->sub->slug_categories)) {
                        foreach (config('app.slugs') as $k => $v) {
                            $dataSlug['slug' . $k] = $data['slug' . $k];
                            $dataSlug['name' . $k] = $data['name' . $k];
                        }
                        $this->insertSlug($com, $act, $type, $id, $dataSlug);
                    }
                    return $this->linkRequest($com, $act, $type, $id, $request);
                } else {
                    return transfer('Cập nhật dữ liệu thất bại.', true, linkReferer());
                }
            } else {
                $data['date_created'] = time();
                $itemSave = ProductSubModel::create($data);
                if (!empty($itemSave)) {
                    $id_insert = $itemSave->id;

                    /* IMAGE */
                    if (!empty($this->configType->$type->categories->sub->images)) {
                        foreach ($this->configType->$type->categories->sub->images as $key => $photo) {
                            $file = $request->file('file-' . $key);
                            $cropFile = $request->{"cropFile-$key"};
                            if (!empty($cropFile)) {
                                $this->insertImgeCrop(ProductSubModel::class, $request, $file, $cropFile, $id_insert, 'product', $key);
                            } else {
                                $this->insertImge(ProductSubModel::class, $request, $file, $id_insert, 'product', $key);
                            }
                        }
                    }

                    /* ALBUM */
                    if (!empty($this->configType->$type->categories->sub->gallery_categories)) {
                        $files = (!empty($request->file('files'))) ? $request->file('files') : null;
                        $this->insertImges(GalleryModel::class, $request, $files, $id_insert, $com, $type, $type, 'product');
                    }

                    /* SEO */
                    if (!empty($this->configType->$type->categories->sub->seo_categories)) {
                        $this->insertSeo($com, $act, $type, $id_insert, $dataSeo);
                    }

                    /* SLUG */
                    if (!empty($this->configType->$type->categories->sub->slug_categories)) {
                        foreach (config('app.slugs') as $k => $v) {
                            $dataSlug['slug' . $k] = $data['slug' . $k];
                            $dataSlug['name' . $k] = $data['name' . $k];
                        }
                        $this->insertSlug($com, $act, $type, $id_insert, $dataSlug);
                    }
                    return $this->linkRequest($com, $act, $type, $id_insert, $request);
                } else {
                    response()->redirect(url('admin', ['com' => $com, 'act' => 'man', 'type' => $type], ['page' => $request->page]));
                }
            }
        }
    }

    public function deleteSub($com, $act, $type, Request $request)
    {
        if (!empty($request->id)) {
            $id = $request->id;

            $row = ProductSubModel::select('id', 'photo')
                ->where('id', $id)
                ->first();
            $rowGallery = GalleryModel::select('id', 'photo')->where('id_parent', $id)->where('com', $com)->where('type', $type)->get();
            if (!empty($row)) {
                if (File::exists(upload('product', $row['photo'], true))) {
                    File::delete(upload('product', $row['photo'], true));
                }
                ProductSubModel::where('id', $id)->delete();
            }
            if (!empty($rowGallery)) {
                foreach ($rowGallery as $v) {
                    if (File::exists(upload('product', $v['photo'], true))) {
                        File::delete(upload('product', $v['photo'], true));
                    }
                }
                GalleryModel::where('id_parent', $id)->where('com', $com)->where('type', $type)->delete();
            }

            SlugModel::where('id_parent', $id)->where('com', $com)->where('type', $type)->delete();
            SeoModel::where('id_parent', $id)->where('com', $com)->where('type', $type)->delete();
        } elseif (!empty($request->listid)) {
            $listid = explode(",", $request->listid);

            for ($i = 0; $i < count($listid); $i++) {
                $id = htmlspecialchars($listid[$i]);

                $row = ProductSubModel::select('id', 'photo')
                    ->where('id', $id)
                    ->first();
                $rowGallery = GalleryModel::select('id', 'photo')->where('id_parent', $id)->where('com', $com)->where('type', $type)->get();
                if (!empty($row)) {
                    if (File::exists(upload('product', $row['photo'], true))) {
                        File::delete(upload('product', $row['photo'], true));
                    }
                    ProductSubModel::where('id', $id)->delete();
                }
                if (!empty($rowGallery)) {
                    foreach ($rowGallery as $v) {
                        if (File::exists(upload('product', $v['photo'], true))) {
                            File::delete(upload('product', $v['photo'], true));
                        }
                    }
                    GalleryModel::where('id_parent', $id)->where('com', $com)->where('type', $type)->delete();
                }

                SlugModel::where('id_parent', $id)->where('com', $com)->where('type', $type)->delete();
                SeoModel::where('id_parent', $id)->where('com', $com)->where('type', $type)->delete();
            }
        }
        response()->redirect(url('admin', ['com' => $com, 'act' => 'man', 'type' => $type], ['page' => $request->page]));
    }
    /* Sub */
    public function man($com, $act, $type, Request $request)
    {
        if ($request->isMethod('get') && !empty($request->keyword)) {
            $keyword = $request->keyword;
        }

        if ($request->isMethod('get') && !empty($request->id_list)) {
            $id_list = $request->id_list;
        }

        if ($request->isMethod('get') && !empty($request->id_cat)) {
            $id_cat = $request->id_cat;
        }

        if ($request->isMethod('get') && !empty($request->id_item)) {
            $id_item = $request->id_item;
        }

        if ($request->isMethod('get') && !empty($request->id_sub)) {
            $id_sub = $request->id_sub;
        }
        $query = ProductModel::select('*')
            ->where('type', $type);
        if (!empty($keyword)) $query->where('name' . config('app.lang_default'), 'like', '%' . $keyword . '%');
        if (!empty($id_list)) $query->where('id_list', '=', $id_list);
        if (!empty($id_cat)) $query->where('id_cat', '=', $id_cat);
        if (!empty($id_item)) $query->where('id_item', '=', $id_item);
        if (!empty($id_sub)) $query->where('id_sub', '=', $id_sub);
        $items = $query
            ->orderBy('numb', 'asc')
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('product.man.man', ['items' => $items]);
    }

    public function edit($com, $act, $type, Request $request)
    {
        $id = $request->query('id') ?? '';
        $item = [];
        $gallery = [];
        $propertieslist = [];
        $propertiescard = [];
        if (!empty($id)) {
            $item = ProductModel::select('*')->where('id', $id)->orderBy('numb', 'asc')->first();
        }
        if (!empty($this->configType->$type->gallery) && $item) {
            $gallery = $item->getPhotos('product')->orderBy('numb', 'asc')->get();
        }

        if (!empty($this->configType->$type->properties)) {
            $query = PropertiesListModel::select('*')->where('type', $type);
            if (!empty(config('type.categoriesProperties')) && !empty($item['id_list'])) $query->whereRaw("FIND_IN_SET(?,id_list)", [$item['id_list']]);
            $propertieslist = $query->orderBy('numb', 'asc')->get();

            $propertiescard = ProductPropertiesModel::select('*')->where('id_parent', $id)->orderBy('id', 'asc')->get();
        }

        return view('product.man.add', compact('item', 'gallery', 'propertieslist', 'propertiescard'));
    }

    public function save($com, $act, $type, Request $request)
    {
        if (!empty($request->csrf_token)) {
            /* Post dữ liệu */
            $message = '';
            $response = array();
            $id = (!empty($request->id)) ? htmlspecialchars($request->id) : 0;
            $data = (!empty($request->data)) ? $request->data : null;
            $dataTags = (!empty($request->input('dataTags'))) ? $request->input('dataTags') : null;
            $dataSuggest = (!empty($request->input('dataSuggest'))) ? $request->input('dataSuggest') : null;
            $buildSchema = !empty($request->has('build-schema'));
            $dataSchema = (!empty($request->input('dataSchema'))) ? $request->input('dataSchema') : null;
            $dataProperties = (!empty($request->input('properties'))) ? $request->input('properties') : null;
            
            if ($data) {
                foreach ($data as $column => $value) {
                    if (strpos($column, 'content') !== false || strpos($column, 'desc') !== false) {
                        $data[$column] = htmlspecialchars(Func::sanitize($value, 'iframe'));
                    } else {
                        $data[$column] = $value;
                    }
                }

                if (!empty($this->configType->$type->group)) {
                    $data['id_list'] = !empty($data['id_list']) ? implode(",", $data['id_list']) : '';
                    $data['id_cat'] = !empty($data['id_cat']) ? implode(",", $data['id_cat']) : '';
                    $data['id_item'] = !empty($data['id_item']) ? implode(",", $data['id_item']) : '';
                    $data['id_sub'] = !empty($data['id_sub']) ? implode(",", $data['id_sub']) : '';
                }

                $data['date_publish'] = (!empty($data['date_publish'])) ? Carbon::createFromFormat('d/m/Y H:i', $data['date_publish'])->toDateTimeString() : Carbon::now()->toDateTimeString();

                if (!empty($request->status)) {
                    $status = '';
                    foreach ($request->status as $attr_column => $attr_value) if ($attr_value != "") $status .= $attr_column . ',';
                    $data['status'] = (!empty($status)) ? rtrim($status, ",") : "";
                } else {
                    $data['status'] = "";
                }

                if (!empty($this->configType->$type->slug)) {
                    foreach (config('app.slugs') as $k => $v) {
                        if (!empty($request->{'slug' . $k})) $data['slug' . $k] = Func::changeTitle(htmlspecialchars($request->{'slug' . $k}));
                        else $data['slug' . $k] = (!empty($data['name' . $k])) ? Func::changeTitle($data['name' . $k]) : '';
                    }
                }

                $data['regular_price'] = (!empty($data['regular_price']) && $data['regular_price'] != '') ? str_replace(",", "", $data['regular_price']) : 0;
                $data['sale_price'] = (!empty($data['sale_price']) && $data['sale_price'] != '') ? str_replace(",", "", $data['sale_price']) : 0;
                $data['discount'] = (!empty($data['discount']) && $data['discount'] != '') ? $data['discount'] : 0;
                $data['type'] = $type;
            }


            if (!empty($dataProperties)) {
                $values = [];
                $key = [];
                foreach ($dataProperties as $k => $array) {
                    $values = array_merge($values, $array);
                    $key[] = $k;
                }
                $data['properties'] = implode(",", $values);
                $data['list_properties'] = implode(",", $key);
            } else {
                $data['properties'] = "";
                $data['list_properties'] = "";
            }


            if (!empty($this->configType->$type->seo)) {
                $dataSeo = $this->getSeo($request);
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
                if (ProductModel::where('id', $id)->where('type', $type)->update($data)) {

                    if (!empty($this->configType->$type->tags)) {
                        $this->insertTags(ProductTagsModel::class, $request, $dataTags, $id, $type);
                    }



                    /* IMAGE */
                    if (!empty($this->configType->$type->images)) {
                        foreach ($this->configType->$type->images as $key => $photo) {
                            $file = $request->file('file-' . $key);
                            $cropFile = $request->{"cropFile-$key"};

                            if (!empty($cropFile)) {
                                $this->insertImgeCrop(ProductModel::class, $request, $file, $cropFile, $id, 'product', $key);
                            } else {
                                $this->insertImge(ProductModel::class, $request, $file, $id, 'product', $key);
                            }
                        }
                    }

                    /* ALBUM */
                    if (!empty($this->configType->$type->gallery)) {
                        $files = (!empty($request->file('files'))) ? $request->file('files') : null;

                        $this->insertImges(GalleryModel::class, $request, $files, $id, $com, $type, $type, 'product');
                    }

                    /* SEO */
                    if (!empty($this->configType->$type->seo)) {
                        $this->insertSeo($com, $act, $type, $id, $dataSeo);
                    }

                    /* Schema */
                    if (!empty($this->configType->$type->schema)) {
                        $this->insertSchema(ProductModel::class, ProductListModel::class, $com, $act, $type, $id, $data, $dataSeo, $buildSchema, $dataSchema);
                    }

                    /* SLUG */
                    if (!empty($this->configType->$type->slug)) {
                        foreach (config('app.slugs') as $k => $v) {
                            $dataSlug['slug' . $k] = $data['slug' . $k];
                            $dataSlug['name' . $k] = $data['name' . $k];
                        }
                        $this->insertSlug($com, $act, $type, $id, $dataSlug);
                    }

                    /* properties */
                    if (!empty($this->configType->$type->properties)) {
                        $propertiescard = (!empty($request->propertiescard)) ? $request->propertiescard : null;
                        $this->insertProperties(ProductPropertiesModel::class,  $propertiescard, $id, $type);
                    }
                    return $this->linkRequest($com, 'man', $type, $id, $request);
                } else {
                    return transfer('Cập nhật dữ liệu thất bại.', true, linkReferer());
                }
            } else {
                $data['date_created'] = time();
                $itemSave = ProductModel::create($data);
                if (!empty($itemSave)) {
                    $id_insert = $itemSave->id;

                    if (!empty($this->configType->$type->tags)) {
                        $this->insertTags(ProductTagsModel::class, $request, $dataTags, $id_insert, $type);
                    }


                    /* IMAGE */
                    if (!empty($this->configType->$type->images)) {
                        foreach ($this->configType->$type->images as $key => $photo) {
                            $file = $request->file('file-' . $key);
                            $cropFile = $request->{"cropFile-$key"};
                            if (!empty($cropFile)) {
                                $this->insertImgeCrop(ProductModel::class, $request, $file, $cropFile, $id_insert, 'product', $key);
                            } else {
                                $this->insertImge(ProductModel::class, $request, $file, $id_insert, 'product', $key);
                            }
                        }
                    }

                    /* ALBUM */
                    if (!empty($this->configType->$type->gallery)) {
                        $files = (!empty($request->file('files'))) ? $request->file('files') : null;
                        $this->insertImges(GalleryModel::class, $request, $files, $id_insert, $com, $type, $type, 'product');
                    }

                    /* SEO */
                    if (!empty($this->configType->$type->seo)) {
                        $this->insertSeo($com, $act, $type, $id_insert, $dataSeo);
                    }

                    /* Schema */
                    if (!empty($this->configType->$type->schema)) {
                        $this->insertSchema(ProductModel::class, ProductListModel::class, $com, $act, $type, $id_insert, $data, $dataSeo, $buildSchema, $dataSchema);
                    }

                    /* SLUG */
                    if (!empty($this->configType->$type->slug)) {
                        foreach (config('app.slugs') as $k => $v) {
                            $dataSlug['slug' . $k] = $data['slug' . $k];
                            $dataSlug['name' . $k] = $data['name' . $k];
                        }
                        $this->insertSlug($com, $act, $type, $id_insert, $dataSlug);
                    }

                    /* properties */
                    if (!empty($this->configType->$type->properties)) {
                        $propertiescard = (!empty($request->propertiescard)) ? $request->propertiescard : null;
                        $this->insertProperties(ProductPropertiesModel::class,  $propertiescard, $id_insert, $type);
                    }
                    return $this->linkRequest($com, $act, $type, $id_insert, $request);
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
            $row = ProductModel::select('id', 'photo')
                ->where('id', $id)
                ->first();
            $rowGallery = GalleryModel::select('id', 'photo')->where('id_parent', $id)->where('com', $com)->where('type', $type)->get();
            if (!empty($row)) {
                if (File::exists(upload('product', $row['photo'], true))) {
                    File::delete(upload('product', $row['photo'], true));
                }
                ProductModel::where('id', $id)->delete();
            }
            if (!empty($rowGallery)) {
                foreach ($rowGallery as $v) {
                    if (File::exists(upload('product', $v['photo'], true))) {
                        File::delete(upload('product', $v['photo'], true));
                    }
                }
                GalleryModel::where('id_parent', $id)->where('com', $com)->where('type', $type)->delete();
            }
            SlugModel::where('id_parent', $id)->where('com', $com)->where('type', $type)->delete();
            SeoModel::where('id_parent', $id)->where('com', $com)->where('type', $type)->delete();
        } elseif (!empty($request->listid)) {
            $listid = explode(",", $request->listid);

            for ($i = 0; $i < count($listid); $i++) {
                $id = htmlspecialchars($listid[$i]);
                $row = ProductModel::select('id', 'photo')
                    ->where('id', $id)
                    ->first();
                $rowGallery = GalleryModel::select('id', 'photo')->where('id_parent', $id)->where('com', $com)->where('type', $type)->get();
                if (!empty($row)) {
                    if (File::exists(upload('product', $row['photo'], true))) {
                        File::delete(upload('product', $row['photo'], true));
                    }
                    ProductModel::where('id', $id)->delete();
                }
                if (!empty($rowGallery)) {
                    foreach ($rowGallery as $v) {
                        if (File::exists(upload('product', $v['photo'], true))) {
                            File::delete(upload('product', $v['photo'], true));
                        }
                    }
                    GalleryModel::where('id_parent', $id)->where('com', $com)->where('type', $type)->delete();
                }
                SlugModel::where('id_parent', $id)->where('com', $com)->where('type', $type)->delete();
                SeoModel::where('id_parent', $id)->where('com', $com)->where('type', $type)->delete();
            }
        }
        response()->redirect(url('admin', ['com' => $com, 'act' => 'man', 'type' => $type], ['page' => $request->page]));
    }

    /* Photo */
    public function manPhoto($com, $act, $type, Request $request)
    {
        $id = $request->id_product;
        $row = ProductModel::select('id', 'properties', 'list_properties')
            ->where('type', $type)
            ->where('id', $id)
            ->first();

        $arrayID = explode(',', $row['properties']);
        $id_product = $row['id'];
        $items = PropertiesListModel::whereRaw("FIND_IN_SET(?,status)", ['photo'])->get()->map(fn($v) =>  $v->getProperties()->whereIn('id',  $arrayID)->get());
        $arrays = array();

        foreach ($items as $key => $list) {
            foreach ($list as $k => $value) {
                $arrays[$value['id_list']][] = $value['id'];
            }
        }

        $combined_array = array();

        if (!empty($arrays)) {
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

        return view('product.photo.man', ['items' => $combined_array, 'id_product' => $id_product]);
    }

    public function editPhoto($com, $act, $type, Request $request)
    {
        $id_product = $request->id_product;
        $id_properties = $request->id_properties;

        $gallery = GalleryModel::select('*')
            ->where('com', $com)
            ->where('type', $type)
            ->where('type_parent', $type)
            ->where('id_parent', $id_product)
            ->where('id_properties', $id_properties)
            ->orderBy('numb', 'asc')
            ->get();

        return view('product.photo.add', ['gallery' => $gallery]);
    }

    public function savePhoto($com, $act, $type, Request $request)
    {

        if (!empty($request->csrf_token)) {

            /* Post dữ liệu */
            $id_product = (!empty($request->id_product)) ? htmlspecialchars($request->id_product) : 0;
            $id_properties = (!empty($request->id_properties)) ? htmlspecialchars($request->id_properties) : '';

            if (!empty($id_properties) && !empty($id_product)) {
                $files = (!empty($request->file('files'))) ? $request->file('files') : null;
                if (!empty($files)) {
                    $this->insertImgesProperties(GalleryModel::class, $request, $files, $id_product, $id_properties, $com, $type, $type, 'product');
                    return transfer('Cập nhật dữ liệu thành công.', true, linkReferer());
                } else {
                    return transfer('Cập nhật dữ liệu thất bại.', false, linkReferer());
                }
            }
        }
    }



    /* Album */
    public function manAlbum($com, $act, $type, Request $request)
    {
        $id_parent = (!empty($request->id_parent)) ? htmlspecialchars($request->id_parent) : 0;
        $gallery = (!empty($request->gallery)) ? htmlspecialchars($request->gallery) : $type;

        $items = GalleryModel::select('*')
            ->where('type', $gallery)
            ->where('type_parent', $type)
            ->where('id_parent', $id_parent)
            ->paginate(10);

        return view('product.album.man', ['items' => $items, 'gallery' => $gallery, 'id_parent' => $id_parent]);
    }


    public function editAlbum($com, $act, $type, Request $request)
    {
        $id_parent = (!empty($request->id_parent)) ? htmlspecialchars($request->id_parent) : 0;
        $id = (!empty($request->id)) ? htmlspecialchars($request->id) : 0;
        $gallery = (!empty($request->gallery)) ? htmlspecialchars($request->gallery) : $type;
        if (!empty($id)) {
            $item = GalleryModel::select('*')
                ->where('type', $gallery)
                ->where('type_parent', $type)
                ->where('id_parent', $id_parent)
                ->where('id', $id)
                ->orderBy('numb', 'asc')
                ->first();

            return view('product.album.edit', ['item' => $item, 'gallery' => $gallery, 'id_parent' => $id_parent]);
        } else {
            return view('product.album.add', ['gallery' => $gallery, 'id_parent' => $id_parent]);
        }
    }


    public function saveAlbum($com, $act, $type, Request $request)
    {
        if (!empty($request->csrf_token)) {

            /* Post dữ liệu */
            $id_parent = (!empty($request->id_parent)) ? htmlspecialchars($request->id_parent) : 0;
            $id = (!empty($request->id)) ? htmlspecialchars($request->id) : 0;
            $gallery = (!empty($request->gallery)) ? htmlspecialchars($request->gallery) : $type;
            $data = (!empty($request->data)) ? $request->data : null;
            $dataMultiTemp = (!empty($request->dataMultiTemp)) ? $request->dataMultiTemp : null;

            if ($id) {
                if ($data) {
                    foreach ($data as $column => $value) {
                        $data[$column] = htmlspecialchars(Func::sanitize($value));
                    }

                    if (!empty($request->status)) {
                        $status = '';
                        foreach ($request->status as $attr_column => $attr_value) if ($attr_value != "") $status .= $attr_column . ',';
                        $data['status'] = (!empty($status)) ? rtrim($status, ",") : "";
                    } else {
                        $data['status'] = "";
                    }
                }
                if (GalleryModel::where('id', $id)->where('type', $type)->update($data)) {
                    $file = $request->file('file');
                    $this->insertImge(GalleryModel::class, $request, $file, $id, 'product');
                    return transfer('Cập nhật dữ liệu thành công.', true, url('admin', ['com' => $com, 'act' => 'man', 'type' => $type], ['gallery' => $gallery, 'id_parent' => $id_parent, 'page' => $request->page]));
                }
            } else {
                $dataMulti = array();
                $errors = false;
                for ($i = 0; $i < count($dataMultiTemp); $i++) {
                    $dataMulti = $dataMultiTemp[$i];
                    if (!empty($request->file('file' . $i))) {
                        $dataMulti['type'] = $gallery;
                        $dataMulti['type_parent'] = $type;
                        $dataMulti['id_parent'] = $id_parent;
                        $dataMulti['com'] = 'product';
                        $dataMulti['status'] = implode(",", array_keys($dataMulti['status']));
                        $itemSave = GalleryModel::create($dataMulti);
                        if (!empty($itemSave)) {
                            $id_insert = $itemSave->id;
                            $file = (!empty($request->file('file' . $i))) ? $request->file('file' . $i) : null;
                            $this->insertImge(GalleryModel::class, $request, $file, $id_insert, 'product');
                            unset($dataMulti);
                        } else {
                            $errors = true;
                        }
                    }
                }
                if (!empty($errors)) {
                    return transfer('Cập nhật dữ liệu thất bại.', false, linkReferer());
                } else {
                    response()->redirect(url('admin', ['com' => $com, 'act' => 'man', 'type' => $type], ['gallery' => $gallery, 'id_parent' => $id_parent, 'page' => $request->page]));
                }
            }
        }
    }


    public function deleteAlbum($com, $act, $type, Request $request)
    {
        /* Post dữ liệu */
        if (!empty($request->id)) {
            $id = $request->id;
            $row = GalleryModel::select('id', 'photo')
                ->where('id', $id)
                ->first();
            if (!empty($row)) {
                if (File::exists(upload('product', $row['photo'], true))) {
                    File::delete(upload('product', $row['photo'], true));
                }
                GalleryModel::where('id', $id)->delete();
            }
        } elseif (!empty($request->listid)) {
            $listid = explode(",", $request->listid);

            for ($i = 0; $i < count($listid); $i++) {
                $id = htmlspecialchars($listid[$i]);
                $row = GalleryModel::select('id', 'photo')
                    ->where('id', $id)
                    ->first();
                if (!empty($row)) {
                    if (File::exists(upload('product', $row['photo'], true))) {
                        File::delete(upload('product', $row['photo'], true));
                    }
                    GalleryModel::where('id', $id)->delete();
                }
            }
        }
        return transfer('Xóa dữ liệu thành công.', true, linkReferer());
    }

    /* Excel */

    public function manExport($com, $act, $type, Request $request)
    {
        $rows = ProductModel::select('*')
            ->where('type', $type)
            ->orderBy('numb', 'asc')
            ->get();

        $array_columns = array(
            'numb' => 'STT',
            'code' => 'Mã sản phẩm',
            'namevi' => "Tên sản phẩm",
            'regular_price' => "Giá",
            'descvi' => "Mô tả"
        );
        $array_width = [5, 10, 20, 20, 50];
        // Tạo một đối tượng spreadsheet mới
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Thêm đoạn văn bản vào 3 dòng đầu tiên
        $sheet->setCellValue('A1', 'Công ty ABC');
        $sheet->setCellValue('A2', 'Danh sách sản phẩm');
        $sheet->setCellValue('A3', 'Ngày xuất: ' . date('Y-m-d'));

        // Gộp các ô cho đoạn văn bản
        $sheet->mergeCells('A1:E1');
        $sheet->mergeCells('A2:E2');
        $sheet->mergeCells('A3:E3');

        // Áp dụng kiểu dáng cho đoạn văn bản
        $textStyle = [
            'font' => [
                'bold' => true,
                'size' => 14,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ];
        $sheet->getStyle('A1:E1')->applyFromArray($textStyle);
        $sheet->getStyle('A2:E2')->applyFromArray($textStyle);
        $sheet->getStyle('A3:E3')->applyFromArray($textStyle);

        // Tiêu đề
        $rowIndex = 4;
        $colIndex = 'A';
        foreach ($array_columns as  $cellValue) {
            $sheet->setCellValue($colIndex . $rowIndex, $cellValue);
            $colIndex++;
        }

        // Điền dữ liệu vào sheet bắt đầu từ dòng thứ tư
        $rowIndex = 5;
        $num = 1;
        foreach ($rows as $product) {
            $colIndex = 'A';
            foreach ($array_columns as $k => $cellValue) {
                if ($k == 'numb') {
                    $sheet->setCellValue($colIndex . $rowIndex, $num);
                } else {
                    $sheet->setCellValue($colIndex . $rowIndex, $product[$k]);
                }
                $colIndex++;
            }
            $num++;
            $rowIndex++;
        }

        // Áp dụng kiểu dáng cho tiêu đề
        $headerStyle = [
            'font' => [
                'bold' => true,
                'color' => ['argb' => 'FFFFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF4CAF50'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => 'FF000000'],
                ],
            ],
        ];
        $sheet->getStyle('A4:E4')->applyFromArray($headerStyle);

        // Áp dụng kiểu dáng cho dữ liệu
        $dataStyle = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => 'FF000000'],
                ],
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrap' => true
            ],
        ];

        $sheet->getStyle('A5:E' . ($rowIndex - 1))->applyFromArray($dataStyle)->getAlignment()->setWrapText(true);

        // Thiết lập độ rộng cột

        foreach (range('A', 'E') as $k => $columnID) {
            $sheet->getColumnDimension($columnID)->setWidth($array_width[$k]);
        }

        // Thiết lập header để tải xuống file Excel
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="danh_sach_san_pham.xlsx"');
        header('Cache-Control: max-age=0');

        // Tạo đối tượng writer để ghi file Excel
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');

        exit;
    }

    public function manImport($com, $act, $type, Request $request)
    {
        $file = $request->file('file-excel');
        $file_type = $file->getClientMimeType();
        if ($file_type == "application/vnd.ms-excel" || $file_type == "application/x-ms-excel" || $file_type == "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet") {
            $inputFileName = time() . $file->getClientOriginalName();
            $file->storeAs('file', $inputFileName);
            $helper = new Sample();
            //$helper->log('Loading file ' . pathinfo($inputFileName, PATHINFO_BASENAME) . ' using IOFactory to identify the format');
            $spreadsheet = IOFactory::load('upload/file/' . $inputFileName);
            $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

            foreach ($sheetData as $k => $row) {
                $data = array();
                if ($k > 4) {
                    $data['code'] = $row['B'];
                    $data['namevi'] = $row['C'];
                    $data['regular_price'] = $row['D'];
                    $data['descvi'] = $row['E'];

                    $slugvi  = Func::changeTitle($data['namevi']);
                    $row = ProductModel::select('id')
                        ->where('slugvi', $slugvi)
                        ->first();

                    if (!empty($row)) {
                        ProductModel::where('id', $row['id'])->update($data);
                    } else {
                        $data['type'] = $type;
                        $data['status'] = 'hienthi';
                        $data['date_created'] = time();
                        ProductModel::create($data);
                    }
                }
            }
            if (File::exists(upload('file', $inputFileName, true))) {
                File::delete(upload('file', $inputFileName, true));
            }
            return transfer('Import dữ liệu thành công.', true, linkReferer());
        } else {
            return transfer('File dữ liệu không đúng.', false, linkReferer());
        }
    }
}
