<?php


namespace LARAVEL\Controllers\Admin;
use Illuminate\Http\Request;
use LARAVEL\Core\Support\Facades\File;
use LARAVEL\Core\Support\Facades\Flash;
use LARAVEL\Core\Support\Facades\Func;
use LARAVEL\Models\GalleryModel;
use LARAVEL\Models\SeoModel;
use LARAVEL\Models\SlugModel;
use LARAVEL\Models\TagsModel;
use LARAVEL\Traits\TraitSave;

class TagsController
{
    use TraitSave;
    private $configType;
    private $upload;

    public function __construct()
    {
        $this->configType = json_decode(json_encode(config('type')))->tags;
    }


    public function man($com, $act, $type, Request $request)
    {
        if ($request->isMethod('get') && !empty($request->keyword)) {
            $keyword = $request->keyword;
        }

        $query = TagsModel::select('id', 'namevi', 'photo', 'descvi', 'slugvi', 'status', 'numb')
            ->where('type', $type);
        if (!empty($keyword)) $query->where('namevi', 'like', '%' . $keyword . '%');

        $items = $query->orderBy('numb', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(10);
        return view('tags.man.man', ['items' => $items]);
    }

    public function edit($com, $act, $type, Request $request)
    {
        $id = $request->id;
        $item = '';

        if (!empty($id)) {
            $item = TagsModel::select('*')
                ->where('type', $type)
                ->where('id', $id)
                ->orderBy('numb', 'asc')
                ->first();
        }

        return view('tags.man.add', ['item' => $item]);
    }

    public function save($com, $act, $type, Request $request)
    {

        if (!empty($request->csrf_token)) {

            /* Post dữ liệu */
            $message = '';
            $response = array();
            $id = (!empty($request->id)) ? htmlspecialchars($request->id) : 0;
            $data = (!empty($request->data)) ? $request->data : null;
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
                if (TagsModel::where('id', $id)->where('type', $type)->update($data)) {

                    /* IMAGE */
                    if (!empty($this->configType->$type->images)) {
                        $file = $request->file('file');
                        $this->insertImge(TagsModel::class, $request, $file, $id, 'news');
                    }

                    /* ALBUM */
                    if (!empty($this->configType->$type->gallery)) {
                        $files = (!empty($request->file('files'))) ? $request->file('files') : null;
                        $this->insertImges(GalleryModel::class, $request, $files, $id, $com, $type, $type);
                    }

                    /* SEO */
                    if (!empty($this->configType->$type->seo)) {
                        $this->insertSeo($com, $act, $type, $id, $dataSeo);
                    }


                    /* SLUG */
                    if (!empty($this->configType->$type->slug)) {
                        foreach (config('app.slugs') as $k => $v) {
                            $dataSlug['slug' . $k] = $data['slug' . $k];
                            $dataSlug['name' . $k] = $data['name' . $k];
                        }
                        $this->insertSlug($com, $act, $type, $id, $dataSlug);
                    }
                    return transfer('Cập nhật dữ liệu thành công.', true, url('admin', ['com' => $com, 'act' => 'man', 'type' => $type], ['page' => $request->page]));
                } else {
                    return transfer('Cập nhật dữ liệu thất bại.', false, linkReferer());
                }
            } else {
                $data['date_created'] = time();
                $itemSave = TagsModel::create($data);
                if (!empty($itemSave)) {
                    $id_insert = $itemSave->id;
                    /* IMAGE */
                    if (!empty($this->configType->$type->images)) {
                        $file = $request->file('file');
                        $this->insertImge(TagsModel::class, $request, $file, $id_insert, 'news');
                    }

                    /* ALBUM */
                    if (!empty($this->configType->$type->gallery)) {
                        $files = (!empty($request->file('files'))) ? $request->file('files') : null;
                        $this->insertImges(GalleryModel::class, $request, $files, $id_insert, $com, $type, $type);
                    }

                    /* SEO */
                    if (!empty($this->configType->$type->seo)) {
                        $this->insertSeo($com, $act, $type, $id_insert, $dataSeo);
                    }
                    /* SLUG */
                    if (!empty($this->configType->$type->slug)) {
                        foreach (config('app.slugs') as $k => $v) {
                            $dataSlug['slug' . $k] = $data['slug' . $k];
                            $dataSlug['name' . $k] = $data['name' . $k];
                        }
                        $this->insertSlug($com, $act, $type, $id_insert, $dataSlug);
                    }

                    response()->redirect(url('admin', ['com' => $com, 'act' => 'man', 'type' => $type], ['page' => $request->page]));
                } else {
                    return transfer('Thêm dữ liệu thất bại.', false, linkReferer());
                }
            }
        }
    }

    public function delete($com, $act, $type, Request $request)
    {
        if (!empty($request->id)) {
            $id = $request->id;

            $row = TagsModel::select('id', 'photo')
                ->where('id', $id)
                ->first();
            $rowGallery = GalleryModel::select('id', 'photo')->where('id_parent', $id)->where('com', $com)->where('type', $type)->get();
            if (!empty($row)) {
                if (File::exists(upload('news', $row['photo'], true))) {
                    File::delete(upload('news', $row['photo'], true));
                }
                TagsModel::where('id', $id)->delete();
            }
            if (!empty($rowGallery)) {
                foreach ($rowGallery as $v) {
                    if (File::exists(upload('news', $v['photo'], true))) {
                        File::delete(upload('news', $v['photo'], true));
                    }
                }
                GalleryModel::where('id_parent', $id)->where('com', $com)->where('type', $type)->delete();
            }

            SeoModel::where('id_parent', $id)->where('com', $com)->where('type', $type)->delete();
            SlugModel::where('id_parent', $id)->where('com', $com)->where('type', $type)->delete();
        } elseif (!empty($request->listid)) {
            $listid = explode(",", $request->listid);

            for ($i = 0; $i < count($listid); $i++) {
                $id = htmlspecialchars($listid[$i]);
                $row = TagsModel::select('id', 'photo')
                    ->where('id', $id)
                    ->first();
                $rowGallery = GalleryModel::select('id', 'photo')->where('id_parent', $id)->where('com', $com)->where('type', $type)->get();
                if (!empty($row)) {
                    if (File::exists(upload('news', $row['photo']))) {
                        File::delete(upload('news', $row['photo']));
                    }
                    TagsModel::where('id', $id)->delete();
                }
                if (!empty($rowGallery)) {
                    foreach ($rowGallery as $v) {
                        if (File::exists(upload('news', $v['photo']))) {
                            File::delete(upload('news', $v['photo']));
                        }
                    }
                    GalleryModel::where('id_parent', $id)->where('com', $com)->where('type', $type)->delete();
                }
                SeoModel::where('id_parent', $id)->where('com', $com)->where('type', $type)->delete();
                SlugModel::where('id_parent', $id)->where('com', $com)->where('type', $type)->delete();
            }
        }
        response()->redirect(url('admin', ['com' => $com, 'act' => 'man', 'type' => $type], ['page' => $request->page]));
    }
}
