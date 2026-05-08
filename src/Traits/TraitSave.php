<?php

namespace LARAVEL\Traits;

use Illuminate\Support\Arr;
use LARAVEL\Core\Support\Facades\File;
use LARAVEL\Core\Support\Facades\Func;
use LARAVEL\Core\Support\Facades\Validator;
use LARAVEL\Models\PhotoModel;
use LARAVEL\Models\SeoModel;
use LARAVEL\Models\SettingModel;
use LARAVEL\Models\SlugModel;
use LARAVEL\Models\ProductPropertiesModel;
use LARAVEL\Models\GalleryModel;
use LARAVEL\Models\LinkModel;
use DB;

trait TraitSave
{
    private function insertTags($model = null, $request = null, $dataTags = null, $id = 0, $type = ''): void
    {
        if ($dataTags) {
            $model::where('id_parent', $id)->where('type', $type)->delete();
            foreach ($dataTags as $v) {
                $dataTag = array();
                $dataTag['id_parent'] = $id;
                $dataTag['id_tags'] = $v;
                $dataTag['type'] = $type;
                $model::create($dataTag);
            }
        } else {
            $model::where('id_parent', $id)->where('type', $type)->delete();
        }
    }
    private function insertImge($model = null, $request = null, $file = null, $id = 0, $path = 'photo', $field = 'photo'): void
    {
        if (!empty($file) && class_exists($model)) {
            $photoUpdate = array();
            $filename =  Func::nameImg($file->getClientOriginalName());
            if ($file->storeAs($path, $filename)) {
                $row = $model::select($field)->where('id', $id)->first();
                if (!empty($row)) {
                    if (File::exists(upload($path, $row[$field], true))) {
                        File::delete(upload($path, $row[$field], true));
                    }
                }
                $photoUpdate[$field] = $filename;
                $model::where('id', $id)->update($photoUpdate);
                unset($photoUpdate);
            }
        }
    }
    private function insertImgeCrop($model = null, $request = null, $file = null, $cropFile = '', $id = 0, $path = 'photo', $field = 'photo'): void
    {
        if (!empty($cropFile) && class_exists($model)) {
            $photoUpdate = array();
            $row = $model::select($field)->where('id', $id)->first();
            if (!empty($file)) {
                $filename =  Func::nameImg($file->getClientOriginalName());
            } else {
                $filename =  Func::nameImgCrop($row[$field]);
            }
            if (!empty($row)) {
                if (File::exists(upload($path, $row[$field], true))) {
                    File::delete(upload($path, $row[$field], true));
                }
            }
            $photoUpdate[$field] = $filename;
            $model::where('id', $id)->update($photoUpdate);
            $image = str_replace('data:image/png;base64,', '', $cropFile);
            $image = str_replace(' ', '+', $image);
            $imageData = base64_decode($image);
            $filePath = 'upload/' . $path . '/' . $filename;
            file_put_contents($filePath, $imageData);
            unset($photoUpdate);
        }
    }
    private function insertImges($model = null, $request = null, $files = null, $id = 0, $com = '', $type = '', $type_parent = '', $path = 'photo', $field = 'photo'): void
    {
        if (!empty($files) && class_exists($model)) {
            for ($i = 0; $i < count($files); $i++) {
                if (!empty($files[$i])) {
                    $photoUpdate = array();
                    $filename =  Func::nameImg($files[$i]->getClientOriginalName());
                    if ($files[$i]->storeAs($path, $filename)) {
                        $photoUpdate[$field] = $filename;
                        $photoUpdate['com'] = $com;
                        $photoUpdate['type'] = $type;
                        if (!empty($type_parent)) {
                            $photoUpdate['type_parent'] = $type_parent;
                        }
                        if (!empty($id)) {
                            $photoUpdate['id_parent'] = $id;
                        }
                        $photoUpdate['status'] = 'hienthi';
                        $photoUpdate['numb'] = !empty($request->input('numb-filer')[$i]) ? $request->input('numb-filer')[$i] : ($i + 1);
                        if (!empty($request->input('name-filer-vi')[$i])) {
                            $photoUpdate['namevi'] = $request->input('name-filer-vi')[$i];
                        }
                        if (!empty($request->input('name-filer-en')[$i])) {
                            $photoUpdate['nameen'] = $request->input('name-filer-en')[$i];
                        }
                        $model::create($photoUpdate);
                        unset($photoUpdate);
                    }
                }
            }
        }
    }
    private function insertImgesProperties($model = null, $request = null, $files = null, $id = 0, $id_properties = 0, $com = '', $type = '', $type_parent = '', $path = 'photo',  $field = 'photo'): void
    {
        if (!empty($files) && class_exists($model)) {
            for ($i = 0; $i < count($files); $i++) {
                if (!empty($files[$i])) {
                    $photoUpdate = array();
                    $filename =  Func::nameImg($files[$i]->getClientOriginalName());
                    if ($files[$i]->storeAs($path, $filename)) {
                        $photoUpdate[$field] = $filename;
                        $photoUpdate['com'] = $com;
                        $photoUpdate['type'] = $type;
                        if (!empty($type_parent)) {
                            $photoUpdate['type_parent'] = $type_parent;
                        }
                        if (!empty($id)) {
                            $photoUpdate['id_parent'] = $id;
                        }
                        if (!empty($id_properties)) {
                            $photoUpdate['id_properties'] = $id_properties;
                        }
                        $photoUpdate['status'] = 'hienthi';
                        $photoUpdate['numb'] = $request->input('numb-filer')[$i];
                        if (!empty($request->input('name-filer-vi')[$i])) {
                            $photoUpdate['namevi'] = $request->input('name-filer-vi')[$i];
                        }
                        if (!empty($request->input('name-filer-en')[$i])) {
                            $photoUpdate['nameen'] = $request->input('name-filer-en')[$i];
                        }
                        $model::create($photoUpdate);
                        unset($photoUpdate);
                    }
                }
            }
        }
    }
    private function insertImgesPhoto($model = null, $request = null, $files = null, $com = '', $type = '', $path = 'photo', $field = 'photo'): void
    {
        if (!empty($files) && class_exists($model)) {
            for ($i = 0; $i < count($files); $i++) {
                if (!empty($files[$i])) {
                    $photoUpdate = array();
                    $filename =  Func::nameImg($files[$i]->getClientOriginalName());
                    if ($files[$i]->storeAs($path, $filename)) {
                        $photoUpdate[$field] = $filename;
                        $photoUpdate['com'] = $com;
                        $photoUpdate['type'] = $type;
                        $photoUpdate['status'] = 'hienthi';
                        $photoUpdate['numb'] = $request->input('numb-filer')[$i];
                        if (!empty($request->input('name-filer-vi')[$i])) {
                            $photoUpdate['namevi'] = $request->input('name-filer-vi')[$i];
                        }
                        if (!empty($request->input('name-filer-en')[$i])) {
                            $photoUpdate['nameen'] = $request->input('name-filer-en')[$i];
                        }
                        $model::create($photoUpdate);
                        unset($photoUpdate);
                    }
                }
            }
        }
    }
    private function insertSlug($com = '', $act = '', $type = '', $id = 0, $dataSlug = array(), $controller = ''): void
    {
        SlugModel::where('id_parent', $id)->where('com', $com)->where('type', $type)->where('act', $act)->delete();
        if (!empty($controller)) {
            $dataSlug['controller'] = $controller;
        } else {
            $dataSlug['controller'] = '\\' . str_replace('Admin', 'Web', get_class($this));
        }
        $dataSlug['model'] = convertToModelClass($com);
        $dataSlug['id_parent'] = $id;
        $dataSlug['com'] = $com;
        $dataSlug['act'] = $act;
        $dataSlug['type'] = $type;
        SlugModel::create($dataSlug);
        Func::writeJson();
    }
    protected function getSeo($request)
    {
        $dataSeo = (!empty($request->dataSeo)) ? $request->dataSeo : null;
        $metaIndex = $request->metaindex ?: 'index';
        $metaOrder = $request->metaorder ?: [];
        if ($dataSeo) {
            foreach ($dataSeo as $column => $value) {
                $dataSeo[$column] = htmlspecialchars(Func::sanitize($value));
            }
        }
        $dataSeo['meta'] = json_encode(['metaindex' => $metaIndex, 'metaorder' => Arr::join($metaOrder, ',')]);
        return $dataSeo;
    }
    private function insertSeo($com = '', $act = '', $type = '', $id = 0, $dataSeo = array()): void
    {
        SeoModel::where('id_parent', $id)->where('com', $com)->where('type', $type)->where('act', $act)->delete();
        $dataSeo['id_parent'] = $id;
        $dataSeo['com'] = $com;
        $dataSeo['act'] = $act;
        $dataSeo['type'] = $type;
        SeoModel::create($dataSeo);
    }
    private function insertSchema($model = null, $modelParent = null, $com = '', $act = '', $type = '', $id = 0, $data = array(), $dataSeo = array(), $buildSchema = array(), $dataSchema = array()): void
    {
        if ($buildSchema) {
            foreach (config('app.slugs') as $k => $v) {
                $pro_list = $modelParent::select("id", "name$k")->where('id', $data['id_list'])->where('type', $type)->first();
                if (!empty($pro_list)) {
                    $name_list = $pro_list['name' . $k];
                } else {
                    $name_list = '';
                }
                if ($k == 'vi' || $k == 'en') {
                    $url_pro = $data['slug' . $k];
                } else {
                    $url_pro = $data['slugvi'];
                }
                $url_img_pro = array();
                $img_pro = $model::select("id", "photo")
                    ->where('id', $id)
                    ->where('type', $type)
                    ->first();
                $url_img_pro[] = upload('product', $img_pro['photo']);
                $price = (!empty($data['sale_price'])) ? $data['sale_price'] : $data['regular_price'];
                $setting = SettingModel::first();
                $dataSchema['schema' . $k] = \View::render('component.schema.product', ['id_pro' => $id, 'name' => $data['name' . $k], 'image' => $url_img_pro, 'description' => $dataSeo['description' . $k], 'code_pro' => $data['code'], 'name_brand' => $name_list, 'name_author' => $setting['name' . $k], 'url' => $url_pro, 'price' => $price]);
            }
            foreach ($dataSchema as $k => &$schema) {
                $dataSchema[$k] = json_encode(json_decode($schema), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
            }
        } else {
            if ($dataSchema) {
                foreach ($dataSchema as $column => $value) {
                    $dataSchema[$column] = htmlspecialchars($value);
                }
            }
        }
        SeoModel::where('id_parent', $id)->where('com', $com)->where('act', $act)->where('type', $type)->update($dataSchema);
    }
    private function insertSchemaNews($model = null, $com = '', $act = '', $type = '', $id = 0, $data = array(), $dataSeo = array(), $buildSchema = array(), $dataSchema = array()): void
    {
        if ($buildSchema) {
            foreach (config('app.slugs') as $k => $v) {
                if ($k == 'vi' || $k == 'en') {
                    $url_news = $data['slug' . $k];
                } else $url_news = $data['slugvi'];
                $img_news = $model::select("id", "photo", "date_created")
                    ->where('id', $id)
                    ->where('type', $type)
                    ->first();
                $logo = PhotoModel::select("id", "photo")
                    ->where('type', 'logo')
                    ->first();
                $url_img_news = upload('news', $img_news['photo']);
                $setting = SettingModel::first();
                $dataSchema['schema' . $k] = \View::render('component.schema.news', ['name' => $data['name' . $k], 'image' => $url_img_news, 'ngaytao' => \Carbon\Carbon::parse($data['created_at'])->timestamp, 'ngaysua' => \Carbon\Carbon::parse($data['updated_at'])->timestamp, 'name_author' => $setting['name' . $k], 'url' => $url_news, 'logo_schema' => upload('photo', $logo['photo']), 'url_author' => config('app.asset')]);
            }
            foreach ($dataSchema as $k => &$schema) {
                $dataSchema[$k] = json_encode(json_decode($schema), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
            }
        } else {
            if ($dataSchema) {
                foreach ($dataSchema as $column => $value) {
                    $dataSchema[$column] = htmlspecialchars($value);
                }
            }
        }
        SeoModel::where('id_parent', $id)->where('com', $com)->where('act', $act)->where('type', $type)->update($dataSchema);
    }
    private function insertProperties($model = null, $properties = array(), $id = 0, $type = ''): void
    {
        if (!empty($properties)) {
            $data = array();
            $model::where('id_parent', $id)->delete();
            $data['id_parent'] = $id;
            foreach ($properties['name_properties'] as $k => $v) {
                $data['namevi'] = $properties['name_properties'][$k];
                $data['id_properties'] = $properties['id_properties'][$k];
                $data['code'] = (!empty($properties['code'][$k])) ? $properties['code'][$k] : '';
                $data['quantity'] = (!empty($properties['quantity'][$k])) ? (int)$properties['quantity'][$k] : 0;
                $data['number'] = (!empty($properties['number'][$k])) ? (int)$properties['number'][$k] : ((int)$k + 1);
                $data['id_photo'] = (!empty($properties['id_photo'][$k])) ? (int)$properties['id_photo'][$k] : 0;
                $data['regular_price'] = (!empty($properties['regular_price'][$k])) ? str_replace(",", "", $properties['regular_price'][$k]) : 0;
                $data['sale_price'] = (!empty($properties['sale_price'][$k])) ? str_replace(",", "", $properties['sale_price'][$k]) : 0;
                $data['discount'] = (!empty($properties['discount'][$k])) ? str_replace(",", "", $properties['discount'][$k]) : 0;
                $status = (!empty($properties['status'][$k])) ? $properties['status'][$k] : 'inactive';
                if ($status == 'active' && $data['quantity'] <= 0) {
                    $status = 'inactive';
                }
                $data['status'] = $status;
                $model::create($data);
            }
        } else {
            $row = ProductPropertiesModel::select('id')
                ->where('id_parent', $id)
                ->get();
            foreach ($row as $value) {
                ProductPropertiesModel::where('id', $value['id'])->delete();
                GalleryModel::where('id_parent', $value['id'])->delete();
            }
        }
    }
    private function linkRequest($com = '', $act = '', $type = '', $id = 0, $request = null)
    {
        $params = array();
        if (!empty($id)) {
            $params['id'] = $id;
        }
        if (!empty($request->data['id_list'])) {
            if (is_array($request->data['id_list'])) {
                $params['id_list'] = implode(',', $request->data['id_list']);
            } else {
                $params['id_list'] = $request->data['id_list'];
            }
        }
        if (!empty($request->data['id_cat'])) {
            if (is_array($request->data['id_cat'])) {
                $params['id_cat'] = implode(',', $request->data['id_cat']);
            } else {
                $params['id_cat'] = $request->data['id_cat'];
            }
        }
        if (!empty($request->data['id_item'])) {
            if (is_array($request->data['id_item'])) {
                $params['id_item'] = implode(',', $request->data['id_item']);
            } else {
                $params['id_item'] = $request->data['id_item'];
            }
        }
        if (!empty($request->data['id_sub'])) {
            if (is_array($request->data['id_sub'])) {
                $params['id_sub'] = implode(',', $request->data['id_sub']);
            } else {
                $params['id_sub'] = $request->data['id_sub'];
            }
        }
        if (!empty($request->data['id_brand'])) {
            $params['id_brand'] = $request->data['id_brand'];
        }
        if (!empty($request->id_parent)) {
            $params['id_parent'] = $request->id_parent;
        }
        $params['page'] = $request->page;
        if (!empty($request->{"button-out"})) {
            return transfer('Cập nhật dữ liệu thành công.', true, url('admin', ['com' => $com, 'act' => 'man', 'type' => $type], ['page' => $request->page]));
        } else {
            return transfer('Cập nhật dữ liệu thành công.', true, url('admin', ['com' => $com, 'act' => 'edit', 'type' => $type], $params));
        }
    }
}
