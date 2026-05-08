<?php


namespace LARAVEL\Controllers\Admin;

use Illuminate\Http\Request;
use LARAVEL\Core\Support\Facades\File;
use LARAVEL\Models\ContactModel;
use LARAVEL\Core\Support\Facades\Func;
use LARAVEL\Core\Support\Facades\Flash;
use LARAVEL\Core\Support\Facades\Validator;

class ContactController
{
    private $configType;
    public function __construct()
    {
        $this->configType = json_decode(json_encode(config('type')))->contact??[];
    }

    public function man($com, $act, $type, Request $request)
    {
        $items = ContactModel::select('*')
            ->where('type', $type)
            ->orderBy('numb', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('contact.man.man', ['items' => $items]);
    }

    public function edit($com, $act, $type, Request $request)
    {
        $item = ContactModel::select('*')
            ->where('type', $type)
            ->first();
        
        return view('contact.man.add', ['item' => $item]);
    }

    public function save($com, $act, $type, Request $request)
    {

        if (!empty($request->csrf_token)) {

            /* Post dữ liệu */
            $message = '';
            $response = array();

            $static = ContactModel::select('id')
                ->where('type', $type)
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

            if (!empty($static)) {
                $data['date_updated'] = time();
                if (ContactModel::where('type', $type)->update($data)) {
                    return transfer('Cập nhật dữ liệu thành công.', true, url('admin', ['com' => $com, 'act' => 'man', 'type' => $type],['page'=>$request->page]));
                } else {
                    return transfer('Cập nhật dữ liệu thất bại.', false, linkReferer());
                }
            } else {
                $data['date_created'] = time();
                $itemSave = ContactModel::create($data);
                if (!empty($itemSave)) {
                    response()->redirect(url('admin', ['com' => $com, 'act' => 'man', 'type' => $type],['page'=>$request->page]));
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
            $row = ContactModel::select('id', 'file_attach')
                ->where('id', $id)
                ->first();
            
            if (!empty($row)) {
                if (File::exists(upload('file', $row['file_attach']))) {
                    File::delete(upload('file', $row['file_attach']));
                }
                ContactModel::where('id', $id)->delete();
            }
           
        } elseif (!empty($request->listid)) {
            $listid = explode(",", $request->listid);

            for ($i = 0; $i < count($listid); $i++) {
                $id = htmlspecialchars($listid[$i]);
                $row = ContactModel::select('id', 'file_attach')
                    ->where('id', $id)
                    ->first();
                
                if (!empty($row)) {
                    if (File::exists(upload('file', $row['file_attach']))) {
                        File::delete(upload('file', $row['file_attach']));
                    }
                    ContactModel::where('id', $id)->delete();
                }
            }
        }
        response()->redirect(url('admin', ['com' => $com, 'act' => 'man', 'type' => $type],['page'=>$request->page]));
    }

}
