<?php


namespace LARAVEL\Controllers\Admin;
use Illuminate\Http\Request;
use LARAVEL\Core\Support\Facades\File;
use LARAVEL\Models\NewslettersModel;
use LARAVEL\Core\Support\Facades\Func;
use LARAVEL\Core\Support\Facades\Email;
use LARAVEL\Core\Support\Facades\Flash;
use LARAVEL\Core\Support\Facades\Validator;
use LARAVEL\Traits\TraitSave;

class NewslettersController
{
    private $configType;
    use TraitSave;
    public function __construct()
    {
        $this->configType = json_decode(json_encode(config('type')))->newsletters;
    }

    public function man($com, $act, $type, Request $request)
    {

        $keyword = (!empty($request->keyword)) ? $request->keyword : '';
        $page = (!empty($request->page)) ? $request->page : 1;
        $status = (!empty($request->status)) ? $request->status : '';
        $starred = (!empty($request->starred)) ? $request->starred : 0;

        $query = NewslettersModel::select('*')
            ->where('type', $type);
        if (!empty($keyword)) {
            $query->where(function ($query) use ($keyword) {
                $query->where('email', 'like', '%' . $keyword . '%')->orWhere('fullname', 'like', '%' . $keyword . '%');
            });
        }
        if (!empty($status)) $query->where('status', $status);
        else {
            $query->where(function ($query) {
                $query->where('status', '1')->orWhere('status', 0);
            });
        }

        if (!empty($starred)) $query->where('starred', $starred);
        $items = $query->orderBy('numb', 'desc')
            ->orderBy('id', 'desc');

        $counts = $items->count();
        $items = $items->paginate(10);

        return view('newsletters.man.man', ['items' => $items, 'counts' => $counts, 'page' => $page]);
    }

    public function edit($com, $act, $type, Request $request)
    {
        $item = NewslettersModel::select('*')
            ->where('type', $type)
            ->first();

        return view('newsletters.man.add', ['item' => $item]);
    }

    public function save($com, $act, $type, Request $request)
    {

        if (!empty($request->csrf_token)) {

            /* Post dữ liệu */
            $message = '';
            $response = array();

            $static = NewslettersModel::select('id')
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
                if (NewslettersModel::where('type', $type)->update($data)) {
                    return transfer('Cập nhật dữ liệu thành công.', true, url('admin', ['com' => $com, 'act' => 'man', 'type' => $type]));
                } else {
                    return transfer('Cập nhật dữ liệu thất bại.', false, linkReferer());
                }
            } else {
                $data['date_created'] = time();
                $itemSave = NewslettersModel::create($data);
                if (!empty($itemSave)) {
                    response()->redirect(url('admin', ['com' => $com, 'act' => 'man', 'type' => $type]));
                } else {
                    return transfer('Thêm dữ liệu thất bại.', false, linkReferer());
                }
            }
        }
    }

    public function starred($com, $act, $type, Request $request)
    {
        $id = (!empty($request->id)) ? $request->id : 0;
        $starred = (!empty($request->starred)) ? $request->starred : 0;
        if (!empty($id)) {
            $data['starred'] = $starred;
            if (NewslettersModel::where('id', $id)->update($data)) {
                response()->redirect(url('admin', ['com' => $com, 'act' => 'man', 'type' => $type]));
            } else {
                return transfer('Dữ liệu không tồn tại', false, linkReferer());
            }
        }
    }

    public function delete($com, $act, $type, Request $request)
    {
        if (!empty($request->id)) {
            $id = $request->id;
            $row = NewslettersModel::select('id', 'file_attach')
                ->where('id', $id)
                ->first();

            if (!empty($row)) {
                if (File::exists(upload('file', $row['file_attach'],true))) {
                    File::delete(upload('file', $row['file_attach'],true));
                }
                NewslettersModel::where('id', $id)->delete();
            }
        } elseif (!empty($request->listid)) {
            $listid = explode(",", $request->listid);

            for ($i = 0; $i < count($listid); $i++) {
                $id = htmlspecialchars($listid[$i]);
                $row = NewslettersModel::select('id', 'file_attach')
                    ->where('id', $id)
                    ->first();

                if (!empty($row)) {
                    if (File::exists(upload('file', $row['file_attach'],true))) {
                        File::delete(upload('file', $row['file_attach'],true));
                    }
                    NewslettersModel::where('id', $id)->delete();
                }
            }
        }
        response()->redirect(url('admin', ['com' => $com, 'act' => 'man', 'type' => $type]));
    }

    public function manSend($com, $act, $type, Request $request)
    {

        if (!empty($request->listemail)) {
            $newsletter = array();
            $listemail = $request->listemail;
            $arrayEmail = array();
            $email = array();
            for ($i = 0; $i < count($listemail); $i++) {
                $id = htmlspecialchars($listemail[$i]);
                $row = NewslettersModel::select('id', 'email', 'fullname')
                    ->where('id', $id)
                    ->where('type', $type)
                    ->first();
                if (!empty($row)) {
                    $data = array();
                    $data['name'] = $row['fullname'];
                    $data['email'] = $row['email'];
                    $arrayEmail["dataEmail" . $i] = $data;
                    $email[] = $row['email'];
                }
            }
            $newsletter['subject'] = !empty($request->subject) ? $request->subject : 'Thư liên hệ';
            $newsletter['content'] = !empty($request->input('content')) ? $request->input('content') : '';
            $newsletter['type'] = $type;
            $newsletter['fullname'] = session()->get('admin.fullname');
            $newsletter['emailto'] = implode(',', $email);
            $newsletter['status'] = '2';
            $newsletter['date_created'] = time();
            $itemSave = NewslettersModel::create($newsletter);
            if (!empty($itemSave)) {
                $id_insert = $itemSave->id;
                $file = $request->file('file');
                $this->insertImge(NewslettersModel::class, $request, $file, $id_insert, 'file', 'file_attach');

                $subject = 'Thư liên hệ';
                $message = Email::markdown('newsletters.man.send', $newsletter);
                $optCompany = json_decode(Func::setting('options'), true);
                $company = Func::setting();
                $file = 'file';
                if (Email::send("customer", $arrayEmail, $subject, $message, $file, $optCompany, $company)) {
                    return transfer('Email được gửi thành công.', true, linkReferer());
                } else {
                    return transfer('Email được gửi thất bại.', false, linkReferer());
                }
            } else {
                return transfer('Email được gửi thất bại.', false, linkReferer());
            }
        }
    }
}
