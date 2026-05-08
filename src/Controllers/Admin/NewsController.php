<?php


namespace LARAVEL\Controllers\Admin;
use Carbon\Carbon;
use Illuminate\Http\Request;
use LARAVEL\Core\Support\Facades\File;
use LARAVEL\Core\Support\Facades\Flash;
use LARAVEL\Core\Support\Facades\Func;
use LARAVEL\Models\GalleryModel;
use LARAVEL\Models\NewsCatModel;
use LARAVEL\Models\NewsItemModel;
use LARAVEL\Models\NewsListModel;
use LARAVEL\Models\NewsModel;
use LARAVEL\Models\NewsSubModel;
use LARAVEL\Models\NewsTagsModel;
use LARAVEL\Models\SeoModel;
use LARAVEL\Models\SlugModel;
use LARAVEL\Traits\TraitSave;

class NewsController
{
    use TraitSave;
    private $configType;
    private $upload;

    public function __construct()
    {
        $this->configType = json_decode(json_encode(config('type')))->news;
    }

    /* List */
    public function manList($com, $act, $type, Request $request)
    {

        if ($request->isMethod('get') && !empty($request->keyword)) {
            $keyword = $request->keyword;
        }

        $query = NewsListModel::select('id', 'namevi', 'photo', 'descvi', 'slugvi', 'status', 'numb')
            ->where('type', $type);
        if (!empty($keyword)) $query->where('namevi', 'like', '%' . $keyword . '%');
        $items = $query
            ->orderBy('numb', 'asc')
            ->orderBy('id', 'desc')
            ->paginate(10);
        return view('news.list.man', ['items' => $items]);
    }

    public function editList($com, $act, $type, Request $request)
    {
        $id = $request->id;
        $item = [];
        $gallery = [];

        if (!empty($id)) {
            $item = NewsListModel::select('*')
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

        return view('news.list.add', ['item' => $item, 'gallery' => $gallery]);
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
                        if (!empty($request->{'slug'.$k})) $data['slug'.$k] = Func::changeTitle(htmlspecialchars($request->{'slug'.$k}));
                        else $data['slug'.$k] = (!empty($data['name'.$k])) ? Func::changeTitle($data['name'.$k]) : '';
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
                if (NewsListModel::where('id', $id)->where('type', $type)->update($data)) {

                    /* IMAGE */
                    if (!empty($this->configType->$type->categories->list->images)) {
                        foreach ($this->configType->$type->categories->list->images as $key => $photo) {
                            $file = $request->file('file-' . $key);
                            $cropFile = $request->{"cropFile-$key"};
                            if (!empty($cropFile)) {
                                $this->insertImgeCrop(NewsListModel::class, $request, $file, $cropFile, $id, 'news', $key);
                            } else {
                                $this->insertImge(NewsListModel::class, $request, $file, $id, 'news', $key);
                            }
                        }
                    }

                    /* ALBUM */
                    if (!empty($this->configType->$type->categories->list->gallery_categories)) {
                        $files = (!empty($request->file('files'))) ? $request->file('files') : null;
                        $this->insertImges(GalleryModel::class, $request, $files, $id, $com, $type, $type);
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
                    return transfer('Cập nhật dữ liệu thất bại.', true, linkReferer());
                }
            } else {
                $data['date_created'] = time();
                $itemSave = NewsListModel::create($data);
                if (!empty($itemSave)) {
                    $id_insert = $itemSave->id;

                    /* IMAGE */
                    if (!empty($this->configType->$type->categories->list->images)) {
                        foreach ($this->configType->$type->categories->list->images as $key => $photo) {
                            $file = $request->file('file-' . $key);
                            $cropFile = $request->{"cropFile-$key"};
                            if (!empty($cropFile)) {
                                $this->insertImgeCrop(NewsListModel::class, $request, $file, $cropFile, $id_insert, 'news', $key);
                            } else {
                                $this->insertImge(NewsListModel::class, $request, $file, $id_insert, 'news', $key);
                            }
                        }
                    }

                    /* ALBUM */
                    if (!empty($this->configType->$type->categories->list->gallery_categories)) {
                        $files = (!empty($request->file('files'))) ? $request->file('files') : null;
                        $this->insertImges(GalleryModel::class, $request, $files, $id_insert, $com, $type, $type);
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

            $row = NewsListModel::select('id', 'photo')
                ->where('id', $id)
                ->first();
            $rowGallery = GalleryModel::select('id', 'photo')->where('id_parent', $id)->where('com', $com)->where('type', $type)->get();
            if (!empty($row)) {
                if (File::exists(upload('news', $row['photo'], true))) {
                    File::delete(upload('news', $row['photo'], true));
                }
                NewsListModel::where('id', $id)->delete();
            }
            if (!empty($rowGallery)) {
                foreach ($rowGallery as $v) {
                    if (File::exists(upload('news', $v['photo'], true))) {
                        File::delete(upload('news', $v['photo'], true));
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
                $row = NewsListModel::select('id', 'photo')
                    ->where('id', $id)
                    ->first();
                $rowGallery = GalleryModel::select('id', 'photo')->where('id_parent', $id)->where('com', $com)->where('type', $type)->get();
                if (!empty($row)) {
                    if (File::exists(upload('news', $row['photo'], true))) {
                        File::delete(upload('news', $row['photo'], true));
                    }
                    NewsListModel::where('id', $id)->delete();
                }
                if (!empty($rowGallery)) {
                    foreach ($rowGallery as $v) {
                        if (File::exists(upload('news', $v['photo'], true))) {
                            File::delete(upload('news', $v['photo'], true));
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

        $query = NewsCatModel::select('id', 'id_list', 'namevi', 'photo', 'descvi', 'slugvi', 'status', 'numb')
            ->where('type', $type);
        if (!empty($keyword)) $query->where('namevi', 'like', '%' . $keyword . '%');
        if (!empty($id_list)) $query->where('id_list', '=', $id_list);
        $items = $query
            ->orderBy('numb', 'asc')
            ->orderBy('id', 'desc')
            ->paginate(10);
        return view('news.cat.man', ['items' => $items]);
    }

    public function editCat($com, $act, $type, Request $request)
    {
        $id = $request->id;
        $item = [];
        $gallery = [];

        if (!empty($id)) {
            $item = NewsCatModel::select('*')
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

        return view('news.cat.add', ['item' => $item, 'gallery' => $gallery]);
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
                        if (!empty($request->{'slug'.$k})) $data['slug'.$k] = Func::changeTitle(htmlspecialchars($request->{'slug'.$k}));
                        else $data['slug'.$k] = (!empty($data['name'.$k])) ? Func::changeTitle($data['name'.$k]) : '';
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
                if (NewsCatModel::where('id', $id)->where('type', $type)->update($data)) {

                    /* IMAGE */
                    if (!empty($this->configType->$type->categories->cat->images)) {
                        foreach ($this->configType->$type->categories->cat->images as $key => $photo) {

                            $file = $request->file('file-' . $key);
                            $cropFile = $request->{"cropFile-$key"};
                            if (!empty($cropFile)) {
                                $this->insertImgeCrop(NewsCatModel::class, $request, $file, $cropFile, $id, 'news', $key);
                            } else {
                                $this->insertImge(NewsCatModel::class, $request, $file, $id, 'news', $key);
                            }
                        }
                    }

                    /* ALBUM */
                    if (!empty($this->configType->$type->categories->cat->gallery_categories)) {
                        $files = (!empty($request->file('files'))) ? $request->file('files') : null;
                        $this->insertImges(GalleryModel::class, $request, $files, $id, $com, $type, $type);
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
                $itemSave = NewsCatModel::create($data);
                if (!empty($itemSave)) {
                    $id_insert = $itemSave->id;

                    /* IMAGE */
                    if (!empty($this->configType->$type->categories->cat->images)) {
                        foreach ($this->configType->$type->categories->cat->images as $key => $photo) {
                            $file = $request->file('file-' . $key);
                            $cropFile = $request->{"cropFile-$key"};
                            if (!empty($cropFile)) {
                                $this->insertImgeCrop(NewsCatModel::class, $request, $file, $cropFile, $id_insert, 'news', $key);
                            } else {
                                $this->insertImge(NewsCatModel::class, $request, $file, $id_insert, 'news', $key);
                            }
                        }
                    }

                    /* ALBUM */
                    if (!empty($this->configType->$type->categories->cat->gallery_categories)) {
                        $files = (!empty($request->file('files'))) ? $request->file('files') : null;
                        $this->insertImges(GalleryModel::class, $request, $files, $id_insert, $com, $type, $type);
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
        if (!empty($request->id)) {
            $id = $request->id;


            $row = NewsCatModel::select('id', 'photo')
                ->where('id', $id)
                ->first();
            $rowGallery = GalleryModel::select('id', 'photo')->where('id_parent', $id)->where('com', $com)->where('type', $type)->get();
            if (!empty($row)) {
                if (File::exists(upload('news', $row['photo'], true))) {
                    File::delete(upload('news', $row['photo'], true));
                }
                NewsCatModel::where('id', $id)->delete();
            }
            if (!empty($rowGallery)) {
                foreach ($rowGallery as $v) {
                    if (File::exists(upload('news', $v['photo'], true))) {
                        File::delete(upload('news', $v['photo'], true));
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
                $row = NewsCatModel::select('id', 'photo')
                    ->where('id', $id)
                    ->first();
                $rowGallery = GalleryModel::select('id', 'photo')->where('id_parent', $id)->where('com', $com)->where('type', $type)->get();
                if (!empty($row)) {
                    if (File::exists(upload('news', $row['photo'], true))) {
                        File::delete(upload('news', $row['photo'], true));
                    }
                    NewsCatModel::where('id', $id)->delete();
                }
                if (!empty($rowGallery)) {
                    foreach ($rowGallery as $v) {
                        if (File::exists(upload('news', $v['photo'], true))) {
                            File::delete(upload('news', $v['photo'], true));
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

        $query = NewsItemModel::select('id', 'id_list', 'id_cat', 'namevi', 'photo', 'descvi', 'slugvi', 'status', 'numb')
            ->where('type', $type);
        if (!empty($keyword)) $query->where('namevi', 'like', '%' . $keyword . '%');
        if (!empty($id_list)) $query->where('id_list', '=', $id_list);
        if (!empty($id_cat)) $query->where('id_cat', '=', $id_cat);
        $items = $query
            ->orderBy('numb', 'asc')
            ->orderBy('id', 'desc')
            ->paginate(10);
        return view('news.item.man', ['items' => $items]);
    }

    public function editItem($com, $act, $type, Request $request)
    {
        $id = $request->id;
        $item = [];
        $gallery = [];

        if (!empty($id)) {
            $item = NewsItemModel::select('*')
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

        return view('news.item.add', ['item' => $item, 'gallery' => $gallery]);
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
                        if (!empty($request->{'slug'.$k})) $data['slug'.$k] = Func::changeTitle(htmlspecialchars($request->{'slug'.$k}));
                        else $data['slug'.$k] = (!empty($data['name'.$k])) ? Func::changeTitle($data['name'.$k]) : '';
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
                if (NewsItemModel::where('id', $id)->where('type', $type)->update($data)) {

                    /* IMAGE */
                    if (!empty($this->configType->$type->categories->item->images)) {
                        foreach ($this->configType->$type->categories->item->images as $key => $photo) {
                            $file = $request->file('file-' . $key);
                            $cropFile = $request->{"cropFile-$key"};
                            if (!empty($cropFile)) {
                                $this->insertImgeCrop(NewsItemModel::class, $request, $file, $cropFile, $id, 'news', $key);
                            } else {
                                $this->insertImge(NewsItemModel::class, $request, $file, $id, 'news', $key);
                            }
                        }
                    }

                    /* ALBUM */
                    if (!empty($this->configType->$type->categories->item->gallery_categories)) {
                        $files = (!empty($request->file('files'))) ? $request->file('files') : null;
                        $this->insertImges(GalleryModel::class, $request, $files, $id, $com, $type, $type);
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
                    return transfer('Cập nhật dữ liệu thất bại.', true, linkReferer());
                }
            } else {
                $data['date_created'] = time();
                $itemSave = NewsItemModel::create($data);
                if (!empty($itemSave)) {
                    $id_insert = $itemSave->id;

                    /* IMAGE */
                    if (!empty($this->configType->$type->categories->item->images)) {
                        foreach ($this->configType->$type->categories->item->images as $key => $photo) {
                            $file = $request->file('file-' . $key);
                            $cropFile = $request->{"cropFile-$key"};
                            if (!empty($cropFile)) {
                                $this->insertImgeCrop(NewsItemModel::class, $request, $file, $cropFile, $id_insert, 'news', $key);
                            } else {
                                $this->insertImge(NewsItemModel::class, $request, $file, $id_insert, 'news', $key);
                            }
                        }
                    }

                    /* ALBUM */
                    if (!empty($this->configType->$type->categories->item->gallery_categories)) {
                        $files = (!empty($request->file('files'))) ? $request->file('files') : null;
                        $this->insertImges(GalleryModel::class, $request, $files, $id_insert, $com, $type, $type);
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
            $row = NewsItemModel::select('id', 'photo')
                ->where('id', $id)
                ->first();
            $rowGallery = GalleryModel::select('id', 'photo')->where('id_parent', $id)->where('com', $com)->where('type', $type)->get();
            if (!empty($row)) {
                if (File::exists(upload('news', $row['photo'], true))) {
                    File::delete(upload('news', $row['photo'], true));
                }
                NewsItemModel::where('id', $id)->delete();
            }
            if (!empty($rowGallery)) {
                foreach ($rowGallery as $v) {
                    if (File::exists(upload('news', $v['photo'], true))) {
                        File::delete(upload('news', $v['photo'], true));
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
                $row = NewsItemModel::select('id', 'photo')
                    ->where('id', $id)
                    ->first();
                $rowGallery = GalleryModel::select('id', 'photo')->where('id_parent', $id)->where('com', $com)->where('type', $type)->get();
                if (!empty($row)) {
                    if (File::exists(upload('news', $row['photo'], true))) {
                        File::delete(upload('news', $row['photo'], true));
                    }
                    NewsItemModel::where('id', $id)->delete();
                }
                if (!empty($rowGallery)) {
                    foreach ($rowGallery as $v) {
                        if (File::exists(upload('news', $v['photo'], true))) {
                            File::delete(upload('news', $v['photo'], true));
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

        $query = NewsSubModel::select('id', 'id_list', 'id_cat', 'id_item', 'namevi', 'photo', 'descvi', 'slugvi', 'status', 'numb')
            ->where('type', $type);
        if (!empty($keyword)) $query->where('namevi', 'like', '%' . $keyword . '%');
        if (!empty($id_list)) $query->where('id_list', '=', $id_list);
        if (!empty($id_cat)) $query->where('id_cat', '=', $id_cat);
        if (!empty($id_item)) $query->where('id_item', '=', $id_item);
        $items = $query
            ->orderBy('numb', 'asc')
            ->orderBy('id', 'desc')
            ->paginate(10);
        return view('news.sub.man', ['items' => $items]);
    }

    public function editSub($com, $act, $type, Request $request)
    {
        $id = $request->id;
        $item = [];
        $gallery = [];
        if (!empty($id)) {
            $item = NewsSubModel::select('*')
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

        return view('news.sub.add', ['item' => $item, 'gallery' => $gallery]);
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
                        if (!empty($request->{'slug'.$k})) $data['slug'.$k] = Func::changeTitle(htmlspecialchars($request->{'slug'.$k}));
                        else $data['slug'.$k] = (!empty($data['name'.$k])) ? Func::changeTitle($data['name'.$k]) : '';
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
                if (NewsSubModel::where('id', $id)->where('type', $type)->update($data)) {

                    /* IMAGE */
                    if (!empty($this->configType->$type->categories->sub->images)) {
                        foreach ($this->configType->$type->categories->sub->images as $key => $photo) {
                            $file = $request->file('file-' . $key);
                            $cropFile = $request->{"cropFile-$key"};
                            if (!empty($cropFile)) {
                                $this->insertImgeCrop(NewsSubModel::class, $request, $file, $cropFile, $id, 'news', $key);
                            } else {
                                $this->insertImge(NewsSubModel::class, $request, $file, $id, 'news', $key);
                            }
                        }
                    }

                    /* ALBUM */
                    if (!empty($this->configType->$type->categories->sub->gallery_categories)) {
                        $files = (!empty($request->file('files'))) ? $request->file('files') : null;
                        $this->insertImges(GalleryModel::class, $request, $files, $id, $com, $type, $type);
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
                $itemSave = NewsSubModel::create($data);
                if (!empty($itemSave)) {
                    $id_insert = $itemSave->id;


                    /* IMAGE */
                    if (!empty($this->configType->$type->categories->sub->images)) {
                        foreach ($this->configType->$type->categories->sub->images as $key => $photo) {
                            $file = $request->file('file-' . $key);
                            $cropFile = $request->{"cropFile-$key"};
                            if (!empty($cropFile)) {
                                $this->insertImgeCrop(NewsSubModel::class, $request, $file, $cropFile, $id_insert, 'news', $key);
                            } else {
                                $this->insertImge(NewsSubModel::class, $request, $file, $id_insert, 'news', $key);
                            }
                        }
                    }

                    /* ALBUM */
                    if (!empty($this->configType->$type->categories->sub->gallery_categories)) {
                        $files = (!empty($request->file('files'))) ? $request->file('files') : null;
                        $this->insertImges(GalleryModel::class, $request, $files, $id_insert, $com, $type, $type);
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
            $row = NewsSubModel::select('id', 'photo')
                ->where('id', $id)
                ->first();
            $rowGallery = GalleryModel::select('id', 'photo')->where('id_parent', $id)->where('com', $com)->where('type', $type)->get();
            if (!empty($row)) {
                if (File::exists(upload('news', $row['photo'], true))) {
                    File::delete(upload('news', $row['photo'], true));
                }
                NewsSubModel::where('id', $id)->delete();
            }
            if (!empty($rowGallery)) {
                foreach ($rowGallery as $v) {
                    if (File::exists(upload('news', $v['photo'], true))) {
                        File::delete(upload('news', $v['photo'], true));
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
                $row = NewsSubModel::select('id', 'photo')
                    ->where('id', $id)
                    ->first();
                $rowGallery = GalleryModel::select('id', 'photo')->where('id_parent', $id)->where('com', $com)->where('type', $type)->get();
                if (!empty($row)) {
                    if (File::exists(upload('news', $row['photo'], true))) {
                        File::delete(upload('news', $row['photo'], true));
                    }
                    NewsSubModel::where('id', $id)->delete();
                }
                if (!empty($rowGallery)) {
                    foreach ($rowGallery as $v) {
                        if (File::exists(upload('news', $v['photo'], true))) {
                            File::delete(upload('news', $v['photo'], true));
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
        $keyword = (!empty($request->keyword)) ? htmlspecialchars($request->keyword) : '';
        $id_list = (!empty($request->id_list)) ? htmlspecialchars($request->id_list) : 0;
        $id_cat = (!empty($request->id_cat)) ? htmlspecialchars($request->id_cat) : 0;
        $id_item = (!empty($request->id_item)) ? htmlspecialchars($request->id_item) : 0;
        $id_sub = (!empty($request->id_sub)) ? htmlspecialchars($request->id_sub) : 0;
        $id_parent = (!empty($request->id_parent)) ? htmlspecialchars($request->id_parent) : 0;

        $query = NewsModel::select('id', 'id_list', 'id_cat', 'id_item', 'id_sub', 'namevi', 'photo', 'descvi', 'slugvi', 'status', 'numb', 'numb')
            ->where('type', $type);
        if (!empty($keyword)) $query->where('namevi', 'like', '%' . $keyword . '%');
        if (!empty($id_list)) $query->where('id_list', '=', $id_list);
        if (!empty($id_cat)) $query->where('id_cat', '=', $id_cat);
        if (!empty($id_item)) $query->where('id_item', '=', $id_item);
        if (!empty($id_sub)) $query->where('id_sub', '=', $id_sub);
        if (!empty($id_parent)) $query->where('id_parent', '=', $id_parent);
        $items = $query
            ->orderBy('numb', 'asc')
            ->orderBy('id', 'desc')
            ->paginate(10);
        return view('news.man.man', ['items' => $items, 'id_parent' => $id_parent]);
    }

    public function edit($com, $act, $type, Request $request)
    {
        $id = (!empty($request->id)) ? htmlspecialchars($request->id) : 0;
        $id_parent = (!empty($request->id_parent)) ? htmlspecialchars($request->id_parent) : 0;

        $item = [];
        $gallery = [];

        if (!empty($id)) {
            $item = NewsModel::select('*')
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

        return view('news.man.add', ['item' => $item, 'gallery' => $gallery, 'id_parent' => $id_parent]);
    }

    public function save($com, $act, $type, Request $request)
    {
        if (!empty($request->csrf_token)) {
            /* Post dữ liệu */
            $message = '';
            $response = array();
            $params = array();
            $id = (!empty($request->id)) ? htmlspecialchars($request->id) : 0;
            $id_parent = (!empty($request->id_parent)) ? htmlspecialchars($request->id_parent) : 0;
            $data = (!empty($request->data)) ? $request->data : null;
            $dataTags = (!empty($request->input('dataTags'))) ? $request->input('dataTags') : null;
            $buildSchema = !empty($request->has('build-schema'));
            $dataSchema = (!empty($request->input('dataSchema'))) ? $request->input('dataSchema') : null;
            if (!empty($id_parent)) {
                $params['id_parent'] = $id_parent;
                $data['id_parent'] = $id_parent;
            }
            $params['page'] = $request->page;

            if ($data) {
                foreach ($data as $column => $value) {
                    if (strpos($column, 'content') !== false || strpos($column, 'desc') !== false) {
                        $data[$column] = htmlspecialchars(Func::sanitize($value, 'iframe'));
                    } else {
                        $data[$column] = htmlspecialchars(Func::sanitize($value));
                    }
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
                        if (!empty($request->{'slug'.$k})) $data['slug'.$k] = Func::changeTitle(htmlspecialchars($request->{'slug'.$k}));
                        else $data['slug'.$k] = (!empty($data['name'.$k])) ? Func::changeTitle($data['name'.$k]) : '';
                    }
                }

                $data['type'] = $type;
            }

            /* Post seo */
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
                if (NewsModel::where('id', $id)->where('type', $type)->update($data)) {

                    if (!empty($this->configType->$type->tags)) {
                        $this->insertTags(NewsTagsModel::class, $request, $dataTags, $id, $type);
                    }


                    /* IMAGE */
                    if (!empty($this->configType->$type->images)) {
                        foreach ($this->configType->$type->images as $key => $photo) {

                            $file = $request->file('file-' . $key);
                            $cropFile = $request->{"cropFile-$key"};
                            if (!empty($cropFile)) {
                                $this->insertImgeCrop(NewsModel::class, $request, $file, $cropFile, $id, 'news', $key);
                            } else {
                                $this->insertImge(NewsModel::class, $request, $file, $id, 'news', $key);
                            }
                        }
                    }


                    /* ALBUM */
                    if (!empty($this->configType->$type->gallery)) {
                        $files = (!empty($request->file('files'))) ? $request->file('files') : null;
                        $this->insertImges(GalleryModel::class, $request, $files, $id, $com, $type, $type, 'news');
                    }

                    /* FILE */
                    if (!empty($this->configType->$type->file)) {
                        $file = $request->file('file-taptin');
                        $this->insertImge(NewsModel::class, $request, $file, $id, 'file', 'file');
                    }

                    /* SEO */
                    if (!empty($this->configType->$type->seo)) {
                        $this->insertSeo($com, $act, $type, $id, $dataSeo);
                    }

                    /* Schema */
                    if (!empty($this->configType->$type->schema)) {
                        $this->insertSchemaNews(NewsModel::class, $com, $act, $type, $id, NewsModel::find($id), $dataSeo, $buildSchema, $dataSchema);
                    }

                    /* SLUG */
                    if (!empty($this->configType->$type->slug)) {
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
                $itemSave = NewsModel::create($data);
                if (!empty($itemSave)) {
                    $id_insert = $itemSave->id;

                    if (!empty($this->configType->$type->tags)) {
                        $this->insertTags(NewsTagsModel::class, $request, $dataTags, $id,$type);
                    }

                    /* IMAGE */
                    if (!empty($this->configType->$type->images)) {
                        foreach ($this->configType->$type->images as $key => $photo) {
                            $file = $request->file('file-' . $key);
                            $cropFile = $request->{"cropFile-$key"};
                            if (!empty($cropFile)) {
                                $this->insertImgeCrop(NewsModel::class, $request, $file, $cropFile, $id_insert, 'news', $key);
                            } else {
                                $this->insertImge(NewsModel::class, $request, $file, $id_insert, 'news', $key);
                            }
                        }
                    }

                    /* FILE DOWNLOAD */
                    if (!empty($this->configType->$type->file)) {
                        $file = $request->file('file-taptin');
                        $this->insertImge(NewsModel::class, $request, $file, $id_insert, 'file', 'file');
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

                    /* Schema */
                    if (!empty($this->configType->$type->schema)) {
                        $this->insertSchemaNews(NewsModel::class, $com, $act, $type, $id_insert, $itemSave, $dataSeo, $buildSchema, $dataSchema);
                    }

                    /* SLUG */
                    if (!empty($this->configType->$type->slug)) {
                        foreach (config('app.slugs') as $k => $v) {
                            $dataSlug['slug' . $k] = $data['slug' . $k];
                            $dataSlug['name' . $k] = $data['name' . $k];
                        }
                        $this->insertSlug($com, $act, $type, $id_insert, $dataSlug);
                    }
                    return $this->linkRequest($com, $act, $type, $id_insert, $request);
                } else {
                    response()->redirect(url('admin', ['com' => $com, 'act' => 'man', 'type' => $type], $params ?? []));
                }
            }
        }
    }

    public function delete($com, $act, $type, Request $request)
    {

        $id_parent = (!empty($request->id_parent)) ? htmlspecialchars($request->id_parent) : 0;

        if (!empty($id_parent)) {
            $params['id_parent'] = $id_parent;
        }
        if (!empty($request->id)) {

            $id = (!empty($request->id)) ? htmlspecialchars($request->id) : 0;
            $row = NewsModel::select('id', 'photo', 'icon')
                ->where('id', $id)
                ->first();
            $rowGallery = GalleryModel::select('id', 'photo')->where('id_parent', $id)->where('com', $com)->where('type', $type)->get();
            if (!empty($row)) {
                if (File::exists(upload('news', $row['photo'], true))) {
                    File::delete(upload('news', $row['photo'], true));
                }
                if (File::exists(upload('news', $row['icon'], true))) {
                    File::delete(upload('news', $row['icon'], true));
                }
                NewsModel::where('id', $id)->delete();
            }
            if (!empty($rowGallery)) {
                foreach ($rowGallery as $v) {
                    if (File::exists(upload('news', $v['photo'], true))) {
                        File::delete(upload('news', $v['photo'], true));
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
                $row = NewsModel::select('id', 'photo', 'icon')
                    ->where('id', $id)
                    ->first();
                $rowGallery = GalleryModel::select('id', 'photo')->where('id_parent', $id)->where('com', $com)->where('type', $type)->get();
                if (!empty($row)) {
                    if (File::exists(upload('news', $row['photo'], true))) {
                        File::delete(upload('news', $row['photo'], true));
                    }
                    if (File::exists(upload('news', $row['icon'], true))) {
                        File::delete(upload('news', $row['icon'], true));
                    }
                    NewsModel::where('id', $id)->delete();
                }
                if (!empty($rowGallery)) {
                    foreach ($rowGallery as $v) {
                        if (File::exists(upload('news', $v['photo'], true))) {
                            File::delete(upload('news', $v['photo'], true));
                        }
                    }
                    GalleryModel::where('id_parent', $id)->where('com', $com)->where('type', $type)->delete();
                }
                SlugModel::where('id_parent', $id)->where('com', $com)->where('type', $type)->delete();
                SeoModel::where('id_parent', $id)->where('com', $com)->where('type', $type)->delete();
            }
        }
        response()->redirect(url('admin', ['com' => $com, 'act' => 'man', 'type' => $type], $params ?? []));
    }

    public function sendMessageOnesignal($heading = '', $content = '', $url = 'https://www.google.com/', $photo = '')
    {


        $contents = array(
            "en" => $content
        );
        $headings = array(
            "en" => $heading
        );
        $uphoto =  assets_photo('news', '', $photo);

        $fields = array(
            'app_id' => config('app.oneSignal.id'),
            'included_segments' => array('All'),
            'contents' => $contents,
            'headings' => $headings,
            'url' => $url,
            'chrome_web_image' => $uphoto
        );

        $fields = json_encode($fields);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/notifications");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json; charset=utf-8',
            'Authorization: Basic ' . config('app.oneSignal.restId')
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }

    /* Sync onesignal */
    public function send($com, $act, $type, Request $request)
    {
        $id = (!empty($request->id)) ? htmlspecialchars($request->id) : 0;

        if ($id) {
            $row = NewsModel::select('*')
                ->where('type', 'thong-bao-day')
                ->where('id', $id)
                ->orderBy('numb', 'asc')
                ->first();
            $this->sendMessageOnesignal($row['namevi'], $row['descvi'], $row['link'], $row['photo']);
            return transfer('Gửi thông báo thành công.', true, linkReferer());
        } else {
            return transfer('Gửi thông báo thất bại.', false, linkReferer());
        }
    }
}
