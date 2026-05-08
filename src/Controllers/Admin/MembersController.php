<?php

namespace LARAVEL\Controllers\Admin;

use Illuminate\Http\Request;
use LARAVEL\Core\Support\Facades\File;
use LARAVEL\Models\MemberModel;
use LARAVEL\Models\OrdersModel;

class MembersController
{
    public function man($com, $act, $type, Request $request)
    {
        $keyword = trim((string) $request->query('keyword', ''));
        $status = trim((string) $request->query('status', 'all'));

        $query = MemberModel::select('id', 'fullname', 'email', 'phone', 'status', 'created_at');

        if ($keyword !== '') {
            $query->where(function ($q) use ($keyword) {
                $q->where('email', 'like', '%' . $keyword . '%')
                    ->orWhere('phone', 'like', '%' . $keyword . '%')
                    ->orWhere('fullname', 'like', '%' . $keyword . '%');
            });
        }

        if ($status === 'locked') {
            $query->where('status', 'locked');
        } elseif ($status === 'active') {
            $query->where(function ($q) {
                $q->whereNull('status')->orWhere('status', '<>', 'locked');
            });
        }

        $items = $query->orderBy('id', 'desc')->paginate(20);

        return view('user.member.man', [
            'items' => $items,
            'filterKeyword' => $keyword,
            'filterStatus' => in_array($status, ['all', 'active', 'locked'], true) ? $status : 'all',
        ]);
    }

    public function edit($com, $act, $type, Request $request)
    {
        $id = (int) $request->query('id', 0);
        $member = MemberModel::find($id);

        if (empty($member)) {
            return transfer('Tài khoản không tồn tại.', false, url('admin', ['com' => 'members', 'act' => 'man', 'type' => $type]));
        }

        $ordersQuery = OrdersModel::query()->where(function ($query) use ($member) {
            $query->where('id_user', (int) $member->id);

            if (!empty($member->email)) {
                $query->orWhereRaw('JSON_UNQUOTE(JSON_EXTRACT(info_user, "$.email")) = ?', [(string) $member->email]);
            }

            if (!empty($member->phone)) {
                $query->orWhereRaw('JSON_UNQUOTE(JSON_EXTRACT(info_user, "$.phone")) = ?', [(string) $member->phone]);
            }
        });

        $ordersCount = (clone $ordersQuery)->count();
        $ordersTotal = (float) ((clone $ordersQuery)->sum('total_price') ?? 0);
        $orders = $ordersQuery->orderBy('id', 'desc')->paginate(10);
        $addresses = $this->loadAddresses((int) $member->id);

        return view('user.member.detail', [
            'member' => $member,
            'orders' => $orders,
            'ordersCount' => $ordersCount,
            'ordersTotal' => $ordersTotal,
            'addresses' => $addresses,
        ]);
    }

    public function save($com, $act, $type, Request $request)
    {
        if (empty($request->csrf_token)) {
            return response()->redirect(linkReferer());
        }

        $id = (int) $request->input('id', 0);
        $action = trim((string) $request->input('member_action', ''));
        $member = MemberModel::find($id);

        if (empty($member)) {
            return transfer('Tài khoản không tồn tại.', false, linkReferer());
        }

        if ($action === 'lock') {
            $member->update(['status' => 'locked']);
            return transfer('Đã khóa tài khoản.', true, linkReferer());
        }

        if ($action === 'unlock') {
            $member->update(['status' => 'active']);
            return transfer('Đã mở khóa tài khoản.', true, linkReferer());
        }
        if ($action === 'update_info') {
            $fullname = trim((string) $request->input('fullname', ''));
            $email = trim((string) $request->input('email', ''));
            $phone = trim((string) $request->input('phone', ''));
            $address = trim((string) $request->input('address', ''));
            $gender = (int) $request->input('gender', 0);
            $birthdayRaw = trim((string) $request->input('birthday', ''));

            if ($fullname === '') {
                return transfer('Họ tên không được để trống.', false, linkReferer());
            }
            if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return transfer('Email không hợp lệ.', false, linkReferer());
            }
            if ($email !== '' && MemberModel::where('email', $email)->where('id', '<>', $member->id)->exists()) {
                return transfer('Email đã tồn tại.', false, linkReferer());
            }
            if ($phone !== '' && MemberModel::where('phone', $phone)->where('id', '<>', $member->id)->exists()) {
                return transfer('Số điện thoại đã tồn tại.', false, linkReferer());
            }

            $birthday = 0;
            if ($birthdayRaw !== '') {
                $birthday = strtotime(str_replace('/', '-', $birthdayRaw)) ?: 0;
            }

            $dataUpdate = [
                'fullname' => $fullname,
                'email' => $email,
                'phone' => $phone,
                'address' => $address,
                'gender' => $gender,
            ];
            $dataUpdate['birthday'] = $birthday;

            if ((int) $request->input('remove_avatar', 0) === 1) {
                if (!empty($member->avatar) && File::exists(upload('user', $member->avatar, true))) {
                    File::delete(upload('user', $member->avatar, true));
                }
                $dataUpdate['avatar'] = '';
            }

            if ($request->has('avatar')) {
                $file = $request->file('avatar');
                if (!empty($file)) {
                    $filename = time() . $file->getClientOriginalName();
                    if ($file->storeAs('user', $filename)) {
                        if (!empty($member->avatar) && File::exists(upload('user', $member->avatar, true))) {
                            File::delete(upload('user', $member->avatar, true));
                        }
                        $dataUpdate['avatar'] = $filename;
                    }
                }
            }

            $member->update($dataUpdate);
            return transfer('Đã cập nhật thông tin user.', true, linkReferer());
        }

        return transfer('Thao tác không hợp lệ.', false, linkReferer());
    }

    private function loadAddresses(int $userId): array
    {
        if ($userId <= 0) {
            return [];
        }

        $path = dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'caches' . DIRECTORY_SEPARATOR . 'member_addresses' . DIRECTORY_SEPARATOR . 'member_' . $userId . '.json';
        if (!is_file($path)) {
            return [];
        }

        $payload = json_decode((string) file_get_contents($path), true);
        return is_array($payload) ? array_values($payload) : [];
    }
}
