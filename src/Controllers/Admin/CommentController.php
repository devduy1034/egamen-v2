<?php


namespace LARAVEL\Controllers\Admin;

use Carbon\Carbon;
use Illuminate\Http\Request;
use LARAVEL\Core\Support\Facades\File;
use LARAVEL\Core\Support\Facades\Func;
use LARAVEL\Models\GalleryModel;
use LARAVEL\Models\CommentModel;
use LARAVEL\Traits\TraitSave;

class CommentController
{
    private $configType;
    private $upload;
    use TraitSave;

    public function man($com, $act, $type, Request $request)
    {

        $keyword = $request->keyword;
        $status = $request->status;

        $query = CommentModel::select('*')
            ->where('id_parent', 0)
            ->where('star', '<>', 0);
        if (!empty($keyword)) $query->where('fullname', 'like', '%' . $keyword . '%');
        if (!empty($status)) $query->where('status', '');
        $items = $query->orderBy('id', 'desc')
            ->paginate(10);

        return view('comment.man.man', ['items' => $items]);
    }

    public function edit($com, $act, $type, Request $request)
    {
        $id = $request->id;
        $item = [];
        if (!empty($id)) {
            $item = CommentModel::select('*')
                ->where('id', $id)
                ->first();
        }


        return view('comment.man.add', ['item' => $item]);
    }

    public function save($com, $act, $type, Request $request)
    {
        if (!empty($request->csrf_token)) {
            /* Post dữ liệu */
            $data = (!empty($request->data)) ? $request->data : null;
            $id = (!empty($data['id_parent'])) ? htmlspecialchars($data['id_parent']) : 0;
            if ($data) {
                foreach ($data as $column => $value) {
                    if (strpos($column, 'content') !== false || strpos($column, 'desc') !== false) {
                        $data[$column] = htmlspecialchars(Func::sanitize($value, 'iframe'));
                    } else {
                        $data[$column] = htmlspecialchars(Func::sanitize($value));
                    }
                }
                $data['status'] = 'hienthi';
                $data['poster'] = 'admin';
                $data['fullname'] = 'Admin';
                $data['date_posted'] = time();
            }
            if ($id) {
                $itemSave = CommentModel::create($data);
                if (!empty($itemSave)) {
                    return transfer('Cập nhật dữ liệu thành công.', true, url('admin', ['com' => $com, 'act' => 'man', 'type' => $type]));
                } else {
                    return transfer('Cập nhật dữ liệu thất bại.', true, linkReferer());
                }
            }
        }
    }

    public function delete($com, $act, $type, Request $request)
    {
        if (!empty($request->id)) {
            $id = $request->id;
            $row = CommentModel::select('id', 'photo')
                ->where('id', $id)
                ->first();
            $rowGallery = GalleryModel::select('id', 'photo')->where('id_parent', $id)->where('com', $com)->where('type', $type)->get();
            if (!empty($row)) {
                if (File::exists(upload('photo', $row['photo']))) {
                    File::delete(upload('photo', $row['photo']));
                }
                CommentModel::where('id', $id)->delete();
                CommentModel::where('id_parent', $id)->delete();
            }
            if (!empty($rowGallery)) {
                foreach ($rowGallery as $v) {
                    if (File::exists(upload('photo', $v['photo']))) {
                        File::delete(upload('photo', $v['photo']));
                    }
                }
                GalleryModel::where('id_parent', $id)->where('com', $com)->where('type', $type)->delete();
            }
        } elseif (!empty($request->listid)) {
            $listid = explode(",", $request->listid);

            for ($i = 0; $i < count($listid); $i++) {
                $id = htmlspecialchars($listid[$i]);
                $row = CommentModel::select('id', 'photo')
                    ->where('id', $id)
                    ->first();
                $rowGallery = GalleryModel::select('id', 'photo')->where('id_parent', $id)->where('com', $com)->where('type', $type)->get();
                if (!empty($row)) {
                    if (File::exists(upload('photo', $row['photo']))) {
                        File::delete(upload('photo', $row['photo']));
                    }
                    CommentModel::where('id', $id)->delete();
                    CommentModel::where('id_parent', $id)->delete();
                }
                if (!empty($rowGallery)) {
                    foreach ($rowGallery as $v) {
                        if (File::exists(upload('photo', $v['photo']))) {
                            File::delete(upload('photo', $v['photo']));
                        }
                    }
                    GalleryModel::where('id_parent', $id)->where('com', $com)->where('type', $type)->delete();
                }
            }
        }
        response()->redirect(url('admin', ['com' => $com, 'act' => 'man', 'type' => $type]));
    }
}
