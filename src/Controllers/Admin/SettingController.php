<?php


namespace LARAVEL\Controllers\Admin;
use Illuminate\Http\Request;
use LARAVEL\Core\Support\Facades\Flash;
use LARAVEL\Core\Support\Facades\Func;
use LARAVEL\Models\SettingModel;
use LARAVEL\Traits\TraitSave;
use LARAVEL\Core\Support\Facades\File;
class SettingController
{
    use TraitSave;
    private $configType;
    public function __construct()
    {
        $this->configType = json_decode(json_encode(config('type')))->setting;
    }

    public function man($com, $act, $type)
    {
        match ($type) {
            'cau-hinh' => $this->getSeting($type),
            'dieu-huong' => $this->getRedirect($type),
            default => null,
        };
    }
    protected function getRedirect($type)
    {
        $items = \LARAVEL\Models\PhotoModel::select(['link', 'link_redirect', 'numb', 'id', 'redirect'])->where('type', $type)->orderBy('id', 'desc')->paginate(10);
        $count = \LARAVEL\Models\PhotoModel::where('type', $type)->count();
        return view('setting.redirect.index', ['items' => $items ?? collect(), 'count' => $count]);
    }
    public function edit($com, $act, $type, Request $request)
    {
        $id = $request->query('id');
        $item = \LARAVEL\Models\PhotoModel::find($id) ?? collect();

        return view('setting.redirect.add', ['item' => $item]);
    }
    public function getSeting($type)
    {
        $item = SettingModel::select('*')->where('type', $type)->first();
        $options = json_decode($item['options'], true);
        $config_firewall = [];
        if(File::exists(base_path('upload/file/firewall.json'))){
            $data_firewall = file_get_contents(assets('upload/file/firewall.json'));
            $config_firewall = json_decode($data_firewall,true);
        }
        return view('setting.man.add', ['item' => $item, 'options' => $options,'config_firewall'=>json_encode($config_firewall,JSON_PRETTY_PRINT)]);
    }
    public function save($com, $act, $type, Request $request): void
    {
        match ($type) {
            'cau-hinh' => $this->saveSeting($com, $act, $type, $request),
            'dieu-huong' => $this->saveRedirect($com, $act, $type, $request),
            default => null,
        };
    }
    protected function saveRedirect($com, $act, $type, $request)
    {
        $data = $request->input('data');
        $id = $request->input('id');
        $data['type'] = $type;
        $data['redirect'] = (!empty($data['redirect'])) ? '302' : '301';

        if (!empty($id)) \LARAVEL\Models\PhotoModel::where('id', $id)->update($data);
        else \LARAVEL\Models\PhotoModel::create($data);
        return transfer('Cập nhật dữ liệu thành công.', true, url('admin', ['com' => $com, 'act' => 'man', 'type' => $type]));
    }
    protected function saveSeting($com, $act, $type, $request)
    {
        if (!empty($request->csrf_token)) {
            $response = array();
            $setting = SettingModel::where('type', $type)->first();
            $options = json_decode($setting['options'], true);
            $data = (!empty($request->data)) ? $request->data : null;
            $datafile = (!empty($request->datafile)) ? $request->datafile : null;
            $firewall =  (!empty($request->firewall)) ? $request->firewall : null;
            if (!empty($datafile)) {
                foreach ($datafile as $k => $value) {
                    $myfile = fopen("src/Lang/" . $k . "/web.json", "w");
                    $txt = $value;
                    fwrite($myfile, $txt);
                    fclose($myfile);
                }
            }
            if (!empty($firewall)) {
                $myfile = fopen('upload/file/firewall.json', "w");
                fwrite($myfile, $firewall);
                fclose($myfile);
                
            }

            if ($data) {
                foreach ($data as $column => $value) {
                    if (is_array($value)) {
                        foreach ($value as $k2 => $v2) {
                            if ($k2 == 'coords_iframe') {
                                $options[$k2] = Func::sanitize($v2, 'iframe');
                            } else $options[$k2] = $v2;
                        }
                        $data[$column] = json_encode($options);
                    } else {
                        if ($column == 'mastertool') {
                            $data[$column] = htmlspecialchars(Func::sanitize($value, 'meta'));
                        } else if (in_array($column, array('headjs', 'bodyjs', 'analytics'))) {
                            $data[$column] = htmlspecialchars(Func::sanitize($value, 'script'));
                        } else {
                            $data[$column] = htmlspecialchars(Func::sanitize($value));
                        }
                    }
                }
            }
            unset($data['firewall']);
            if (!empty($this->configType->$type->seo)) $dataSeo = $this->getSeo($request);
            if (!empty($response)) {
                if (!empty($data)) {
                    foreach ($data as $k => $v) {
                        if (!empty($v)) Flash::set($k, $v);
                    }
                }
                if (!empty($dataSeo)) {
                    foreach ($dataSeo as $k => $v) {
                        if (!empty($v)) Flash::set($k, $v);
                    }
                }
                $response['status'] = 'danger';
                $message = base64_encode(json_encode($response));
                Flash::set('message', $message);
                response()->redirect(linkReferer());
            }
            if (!empty($setting)) {
                if (SettingModel::where('type', $type)->update($data)) {
                    $id = $setting['id'];
                    if (!empty($this->configType->$type->seo)) $this->insertSeo($com, $act, $type, $id, $dataSeo);
                    return transfer('Cập nhật dữ liệu thành công.', true, url('admin', ['com' => $com, 'act' => 'man', 'type' => $type]));
                } else return transfer('Cập nhật dữ liệu thất bại.', false, linkReferer());
            } else {
                $itemSave = SettingModel::create($data);
                if (!empty($itemSave)) {
                    $id_insert = $itemSave->id;
                    if (!empty($this->configType->$type->seo)) $this->insertSeo($com, $act, $type, $id_insert, $dataSeo);
                    response()->redirect(url('admin', ['com' => $com, 'act' => 'man', 'type' => $type]));
                } else response()->redirect(url('admin', ['com' => $com, 'act' => 'man', 'type' => $type]));
            }
        }
    }
    public function delete($com, $act, $type, Request $request)
    {
        if (!empty($request->id)) {
            $id = $request->id;
            \LARAVEL\Models\PhotoModel::where('id', $id)->delete();
        } elseif (!empty($request->listid)) {
            $listid = explode(",", $request->listid);
            for ($i = 0; $i < count($listid); $i++) {
                $id = htmlspecialchars($listid[$i]);
                \LARAVEL\Models\PhotoModel::where('id', $id)->delete();
            }
        }
        response()->redirect(url('admin', ['com' => $com, 'act' => 'man', 'type' => $type]));
    }
}
