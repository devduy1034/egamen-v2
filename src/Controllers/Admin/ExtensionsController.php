<?php


namespace LARAVEL\Controllers\Admin;

use Illuminate\Http\Request;
use LARAVEL\Core\Support\Facades\Flash;
use LARAVEL\Core\Support\Facades\Func;
use LARAVEL\Models\ExtensionsModel;
use LARAVEL\Traits\TraitSave;

class ExtensionsController
{
    use TraitSave;
    private $configType;
    public function __construct()
    {
        $this->configType = json_decode(json_encode(config('type')))->extensions;
    }

    /* Popup */
    public function man($com, $act, $type, Request $request)
    {

        $options = array();
        $item = ExtensionsModel::select('*')->where('type', $type)->first();
        if (!empty($item)) {
            $options = json_decode($item['options'], true);
        }

        return view('extensions.' . $type . '.add', ['item' => $item, 'options' => $options]);
    }

    public function save($com, $act, $type, Request $request)
    {

        if (!empty($request->csrf_token)) {

            /* Post dữ liệu */
            $message = '';
            $response = array();

            $extens = ExtensionsModel::select('id')
                ->where('type', $type)
                ->first();

            $data = (!empty($request->data)) ? $request->data : null;

            if ($data) {
                foreach ($data as $column => $value) {
                    if (is_array($value)) {
                        foreach ($value as $k2 => $v2) {
                            $option[$k2] = $v2;
                        }
                        $data[$column] = json_encode($option);
                    } else {
                        if ($column == 'mastertool') {
                            $data[$column] = htmlspecialchars(Func::sanitize($value, 'meta'));
                        } else {
                            $data[$column] = htmlspecialchars(Func::sanitize($value));
                        }
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

            if (!empty($extens)) {

                if (ExtensionsModel::where('type', $type)->update($data)) {

                    $id = $extens['id'];
                    /* IMAGE */
                    if (!empty($this->configType->$type->images)) {
                        $file = $request->file('file-');
                        $this->insertImge(ExtensionsModel::class, $request, $file, $id,'photo');
                    }

                    return transfer('Cập nhật dữ liệu thành công.', true, url('admin', ['com' => $com, 'act' => 'man', 'type' => $type]));
                } else {
                    return transfer('Cập nhật dữ liệu thất bại.', true, linkReferer());
                }
            } else {

                $itemSave = ExtensionsModel::create($data);
                if (!empty($itemSave)) {
                    $id_insert = $itemSave->id;
                    /* IMAGE */
                    if (!empty($this->configType->$type->images)) {
                        $file = $request->file('file-');
                        $this->insertImge(ExtensionsModel::class, $request, $file, $id_insert,'photo');
                    }

                    response()->redirect(url('admin', ['com' => $com, 'act' => 'man', 'type' => $type]));
                } else {
                    response()->redirect(url('admin', ['com' => $com, 'act' => 'man', 'type' => $type]));
                }
            }
        }
    }
}
