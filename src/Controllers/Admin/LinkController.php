<?php


namespace LARAVEL\Controllers\Admin;

use Carbon\Carbon;
use Illuminate\Http\Request;
use LARAVEL\Core\Support\Facades\Flash;
use LARAVEL\Core\Support\Facades\Func;
use LARAVEL\Models\LinkModel;
use LARAVEL\Traits\TraitSave;


class LinkController
{
    use TraitSave;
    private $configType;
    private $upload;

    public function __construct()
    {
        $this->configType = json_decode(json_encode(config('type')))->link;
    }

    /* Sub */

    public function man($com, $act, $type, Request $request)
    {
        $keyword = (!empty($request->keyword)) ? htmlspecialchars($request->keyword) : '';
        $id_parent = (!empty($request->id_parent)) ? htmlspecialchars($request->id_parent) : 0;
        $query = LinkModel::select('*')
            ->where('type', $type);
        if (!empty($keyword)) $query->where('link', 'like', '%' . $keyword . '%');
        if (!empty($id_parent)) $query->where('id_parent', '=', $id_parent);
        $items = $query
            ->orderBy('numb', 'asc')
            ->orderBy('id', 'desc')
            ->paginate(10);
        return view('link.man.man', ['items' => $items, 'id_parent' => $id_parent]);
    }

    public function edit($com, $act, $type, Request $request)
    {
        $id = (!empty($request->id)) ? htmlspecialchars($request->id) : 0;
        $id_parent = (!empty($request->id_parent)) ? htmlspecialchars($request->id_parent) : 0;

        $item = [];

        if (!empty($id)) {
            $item = LinkModel::select('*')
                ->where('type', $type)
                ->where('id', $id)
                ->orderBy('numb', 'asc')
                ->first();
        }

        return view('link.man.add', ['item' => $item,  'id_parent' => $id_parent]);
    }

    public function save($com, $act, $type, Request $request)
    {
        if (!empty($request->csrf_token)) {
            /* Post dữ liệu */
            $message = '';
            $response = array();
            $id = (!empty($request->id)) ? htmlspecialchars($request->id) : 0;
            $id_parent = (!empty($request->id_parent)) ? htmlspecialchars($request->id_parent) : 0;
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

            if ($id) {
                $data['date_updated'] = time();
                if (LinkModel::where('id', $id)->where('type', $type)->update($data)) {
                    Func::updatetHtml($id);
                    if ($data['type_link'] == 'src') {
                        Func::updatetContent($id);
                    }
                    return transfer('Cập nhật dữ liệu thành công.', true, url('admin', ['com' => $com, 'act' => 'man', 'type' => $type], ['page' => $request->page]));
                } else {
                    return transfer('Cập nhật dữ liệu thất bại.', true, linkReferer());
                }
            } else {
                $data['date_created'] = time();
                $itemSave = LinkModel::create($data);
                if (!empty($itemSave)) {
                    response()->redirect(url('admin', ['com' => $com, 'act' => 'man', 'type' => $type], ['page' => $request->page]));
                } else {
                    response()->redirect(url('admin', ['com' => $com, 'act' => 'man', 'type' => $type], ['page' => $request->page]));
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
            $row = LinkModel::select('id')
                ->where('id', $id)
                ->first();
            if (!empty($row)) {
                LinkModel::where('id', $id)->delete();
            }
        } elseif (!empty($request->listid)) {
            $listid = explode(",", $request->listid);

            for ($i = 0; $i < count($listid); $i++) {
                $id = htmlspecialchars($listid[$i]);
                $row = LinkModel::select('id')
                    ->where('id', $id)
                    ->first();
                if (!empty($row)) {
                    LinkModel::where('id', $id)->delete();
                }
            }
        }
        response()->redirect(url('admin', ['com' => $com, 'act' => 'man', 'type' => $type], ['page' => $request->page]));
    }
}
