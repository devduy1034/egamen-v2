<?php


namespace LARAVEL\Controllers\Admin;
use Illuminate\Http\Request;
use LARAVEL\Core\Support\Facades\Auth;
use LARAVEL\Core\Support\Facades\File;
use LARAVEL\Core\Support\Facades\Hash;
use LARAVEL\Core\Support\Facades\Validator;
use LARAVEL\Core\Support\JsonResponse;
use LARAVEL\Models\UserModel;
use LARAVEL\Models\UserLimitModel;
use LARAVEL\Models\UserLogModel;
use LARAVEL\Core\Support\Facades\Func;
use LARAVEL\Core\Support\Facades\Flash;
use LARAVEL\Traits\TraitSave;
use LARAVELPermission\Models\Role;

class UserController
{
    use JsonResponse, TraitSave;
    protected $loginpage;
    public function __construct()
    {
        $this->loginpage = config('auth.loginpage') ?? 'cover';
    }
    /* Login */
    public function login(Request $request)
    {
        if (session()->has('admin')) response()->redirect(url('index'));
        if ($request->isMethod('post')) {
            if (!empty($request->csrf_token)) {
                $credentials = [
                    'username' => $request->username,
                    'password' => $request->password,
                ];
                $username = (!empty($request->username)) ? $request->username : '';
                $password = (!empty($request->password)) ? $request->password : '';
                $error = "";
                $success = "";
                $login_failed = false;
                $ip = request()->ip();

                $row = UserLimitModel::select('id', 'login_ip', 'login_attempts', 'attempt_time', 'locked_time')
                    ->where('login_ip', $ip)
                    ->first();



                if (!empty($row)) {
                    $id_login = $row->id;
                    $time_now = time();
                    if ($row->locked_time > 0) {
                        $locked_time = $row->locked_time;
                        $delay_time = 15;
                        $interval = $time_now  - $locked_time;

                        if ($interval <= $delay_time * 60) {
                            $time_remain = $delay_time * 60 - $interval;
                            $error = 'Xin lỗi vui lòng đăng nhập lại sau' . " " . round($time_remain / 60) . ' Phút' . "..!";
                            return view("component.login.{$this->loginpage}", ['mess' => $error]);
                        } else {
                            $dataLimit['login_attempts'] = 0;
                            $dataLimit['attempt_time'] = $time_now;
                            $dataLimit['locked_time'] = 0;
                            UserLimitModel::where('id', $id_login)->update($dataLimit);
                        }
                    }
                }
                if ($error == '') {
                    if ($username == '' && $password == '') {
                        $error = "Bạn chưa nhập tên đăng nhập và mật khẩu";
                        return view("component.login.{$this->loginpage}", ['mess' => $error]);
                    } else if ($username == '') {
                        $error = "Bạn chưa nhập tên đăng nhập";
                        return view("component.login.{$this->loginpage}", ['mess' => $error]);
                    } else if ($password == '') {
                        $error = "Bạn chưa nhập mật khẩu";
                        return view("component.login.{$this->loginpage}", ['mess' => $error]);
                    } else {
                        // Manual login
                        $user = UserModel::where('username', $credentials['username'])->first();
                       
                        if ($user && Hash::check($credentials['password'], $user->password)) {
                            // Set session for login
                            session()->put('admin', $user->id);
                            $admin = $user;
                            $timenow = time();
                            $id_user = $admin->id;
                            $ip = request()->ip();
                            $token = md5(time());
                            $user_agent = $_SERVER['HTTP_USER_AGENT'];
                            $device = strtolower(agent()->deviceType());
                            $sessionhash = md5(sha1($admin->password . $admin->username));
                            UserLogModel::create(['id_user' => $id_user, 'ip' => $ip, 'timelog' => $timenow, 'user_agent' => $user_agent, 'device' => $device, 'operation' => 'login']);
                            UserModel::where('id', $id_user)->update(['login_session' => $sessionhash, 'lastlogin' => $timenow, 'user_token' => $token]);
                            UserLogModel::where('id', $id_user)->update(['login_session' => $sessionhash, 'lastlogin' => $timenow]);
                            $limitlogin = UserLimitModel::select('*')->where('login_ip', $ip)->first();
                            if (!empty($limitlogin) == 1) {
                                $id_login = $limitlogin->id;
                                UserLimitModel::where('id', $id_login)->update(['login_attempts' => 0, 'locked_time' => 0]);
                            }
                            session()->get(config('app.token'), true);
                            $secret_key = session()->get($sessionhash);
                            $admin->where('id', $admin->id)->update(['secret_key' => $secret_key]);
                            return response()->redirect(request()->query('redirect') ?? url('index'));
                        } else {
                            $login_failed = true;
                        }
                       
                        if ($login_failed) {
                            $ip = Func::getRealIPAddress();
                            $row = UserLimitModel::select('*')
                                ->where('login_ip', $ip)
                                ->first();
                            if (!empty($row) == 1) {
                                $id_login = $row->id;
                                $attempt = $row->login_attempts;
                                $noofattmpt = 5;
                                if ($attempt < $noofattmpt) {
                                    $attempt = $attempt + 1;
                                    UserLimitModel::where('id', $id_login)->update(['login_attempts' => $attempt]);
                                    $no_ofattmpt = $noofattmpt + 1;
                                    $remain_attempt = $no_ofattmpt - $attempt;
                                    $error = "Sai thông tin còn" . ' ' . $remain_attempt . ' ' . "lần thử";
                                    return view("component.login.{$this->loginpage}", ['mess' => $error]);
                                } else {
                                    if ($row->locked_time == 0) {
                                        $attempt = $attempt + 1;
                                        $timenow = time();
                                        UserLimitModel::where('id', $id_login)->update(['login_attempts' => $attempt, 'locked_time' => $timenow]);
                                    } else {
                                        $attempt = $attempt + 1;
                                        UserLimitModel::where('id', $id_login)->update(['login_attempts' => $attempt]);
                                    }
                                    $delay_time = 15;
                                    $error = "Bạn đã hết số lần thử vui lòng đăng nhập sau" . " " . $delay_time . " " . 'phút';
                                    return view("component.login.{$this->loginpage}", ['mess' => $error]);
                                }
                            } else {
                                $timenow = time();
                                UserLimitModel::create(['login_ip' => $ip, 'login_attempts' => 1, 'attempt_time' => $timenow, 'locked_time' => 0]);
                                $remain_attempt = 5;
                                $error = 'Sai thông tin còn' . ' ' . $remain_attempt . ' ' . 'lần thử';
                                return view("component.login.{$this->loginpage}", ['mess' => $error]);
                            }
                        }
                    }
                }
            }
        } else {
            return view("component.login.{$this->loginpage}", ['items' => '']);
        }
    }

    public function manAdmin($com, $act, $type, Request $request)
    {
        $changepass = false;
        if (!empty($request->changepass) && ($request->changepass == 1)) {
            $changepass = true;
        } else {
            $changepass = false;
        }
        $item = UserModel::select('*')
            ->where('role', 3)
            ->first();

        return view('user.admin.add', ['item' => $item, 'changepass' => $changepass]);
    }

    public function saveAdmin($com, $act, $type, Request $request)
    {
        if (!empty($request->input('id'))) {
            $id = UserModel::find(session()->get('admin'))->id;
            if (!empty($request->changepass) && ($request->changepass == 1)) {
                $changepass = true;
            } else {
                $changepass = false;
            }
            $message = '';
            $response = array();
            $id = (!empty($request->id)) ? htmlspecialchars($request->id) : 0;
            $data = (!empty($request->data)) ? $request->data : null;
            if ($data) {
                foreach ($data as $column => $value) {
                    $data[$column] = htmlspecialchars(Func::sanitize($value));
                }
            }

            if (!empty($changepass)) {
                $old_pass = (!empty($request->oldpassword)) ? $request->oldpassword : '';
                $new_pass = (!empty($request->newpassword)) ? $request->newpassword : '';
                $renew_pass = (!empty($request->renewpassword)) ? $request->renewpassword : '';
                $admin = UserModel::find(session()->get('admin'));
                /* Valid data */
                if (empty($old_pass)) {
                    $response['messages'][] = 'Mật khẩu cũ không được trống';
                }

                if (!empty($old_pass)) {
                    if (!Hash::check($request->oldpassword, $admin->password)) {
                        $response['messages'][] = 'Mật khẩu cũ không chính xác';
                    }
                }

                if (empty($new_pass)) {
                    $response['messages'][] = 'Mật khẩu mới không được trống';
                }


                if (!empty($new_pass) && empty($renew_pass)) {
                    $response['messages'][] = 'Xác nhận mật khẩu mới không được trống';
                }

                if (!empty($new_pass) && !empty($renew_pass) && !Func::isMatch($new_pass, $renew_pass)) {
                    $response['messages'][] = 'Xác nhận mật khẩu mới không trùng khớp';
                }

                if (!empty($response)) {
                    $response['status'] = 'danger';
                    $message = base64_encode(json_encode($response));
                    Flash::set('message', $message);
                    response()->redirect(linkReferer());
                }

                $data['password'] = Hash::make($request->newpassword);
                UserModel::where('id', $admin->id)->update($data);
                Auth::guard('admin')->logout();
                return transfer('Đổi mật khẩu thành công.', true, url('loginAdmin'));
            } else {
                $birthday = $data['birthday'];
                $data['birthday'] = strtotime(str_replace("/", "-", $data['birthday']));




                if (!empty($data['username']) && !Func::isAlphaNum($data['username'])) {
                    $response['messages'][] = "Tài khoản chỉ được nhập chữ thường và số";
                }

                if (!empty($data['username'])) {
                    if (Func::checkAccount($data['username'], 'username', 'user', $id)) {
                        $response['messages'][] = "Tài khoản đã tồn tại";
                    }
                }

                if (empty($data['fullname'])) {
                    $response['messages'][] = "Họ tên không thể trống";
                }

                if (empty($data['email'])) {
                    $response['messages'][] = "Email không được trống";
                }

                if (!empty($data['email']) && !Func::isEmail($data['email'])) {
                    $response['messages'][] = "Email không hợp lệ";
                }

                if (!empty($data['email'])) {
                    if (Func::checkAccount($data['email'], 'email', 'user', $id)) {
                        $response['messages'][] = "email đã tồn tại";
                    }
                }

                if (!empty($data['phone']) && !Func::isPhone($data['phone'])) {
                    $response['messages'][] = "Số điện thoại không hợp lệ";
                }

                if (empty($data['gender'])) {
                    $response['messages'][] = "chưa chọn giới tính";
                }

                if (empty($birthday)) {
                    $response['messages'][] = "Ngày sinh không được trống";
                }

                if (!empty($birthday) && !Func::isDate($birthday)) {
                    $response['messages'][] = "Ngày sinh không hợp lệ";
                }

                if (empty($data['address'])) {
                    $response['messages'][] = "Địa chỉ không được trống";
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
                    response()->redirect(url('admin', ['com' => $com, 'act' => 'man', 'type' => $type],['page'=>$request->page]));
                }

                /* Save data */
                if ($request->has('avatar')) {
                    $file = $request->file('avatar');
                    $filename = time() . $file->getClientOriginalName();
                    if ($file->storeAs('user', $filename)) {
                        $data['avatar'] = $filename;
                        if (File::exists(upload('user', $admin->avatar))) {
                            File::delete(upload('user', $admin->avatar));
                        }
                    }
                }
                if ($admin->update($data)) {

                    return transfer('Cập nhật dữ liệu thành công.', true, linkReferer());
                } else {
                    return transfer('Cập nhật dữ liệu thất bại.', false, linkReferer());
                }
            }
        }
    }

    public function logout(Request $request)
    {
        if (session()->has('admin')) {
            unset($_SESSION['admin']);
            return response()->redirect(url('loginAdmin'));
        }
    }

    public function index()
    {
        $items = UserModel::where('role', '<>', 3)->paginate(10);
        $count = UserModel::where('role', '<>', 3)->count();
        return view('user.man_admin.index', compact('items', 'count'));
    }
    public function add()
    {
        $item = [];
        $roles = (config('type.users.permission')) ? Role::get()->toArray() : [];
        return view('user.man_admin.update', compact('item', 'roles'));
    }
    public function edit(Request $request)
    {
        $id = $request->get('id') ?? 0;
        $item = UserModel::find($id);
        $roles = (config('type.users.permission')) ? Role::get()->toArray() : [];
        return view('user.man_admin.update', compact('item', 'roles'));
    }
    public function save(Request $request): void
    {
        if ($request->input('id')) $this->editUser($request);
        else $this->saveUser($request);
    }

    protected function saveUser($request)
    {

        $validator = Validator::makeValidate($request, [
            'username' => 'unique:user,username;' . $request->input('username'),
            'fullname' => 'required',
            're-password' => 'same:password'
        ], [
            'username.unique' => '*Username đã tồn tại',
            'fullname.required' => '* Vui lòng nhập họ tên',
            're-password.same' => '*Nhập lại mật khẩu không chính xác'
        ]);

        if ($validator->isFailed()) {
            foreach ($request->all() as $k => $v) Flash::set($k, $v);
            Flash::set('message', $validator->errors());
            response()->redirect(url('user.add'));
        }
        if (!empty($request->input('idrole'))) $role = Role::find($request->input('idrole'));
        else $role = [];
        $data['username'] = $request->input('username');
        $data['fullname'] = $request->input('fullname');
        $data['address'] = $request->input('address');
        $data['phone'] = $request->input('phone');
        $data['email'] = $request->input('email');
        $data['status'] = 'hienthi';
        $data['numb'] = 1;
        $user = UserModel::create($data);
        UserModel::where('id', $user->id)->update(['password' => Hash::make($request->input('password'))]);
        if (!empty($role) && config('type.users.permission')) $user->grantRole($role);
        transfer('Tạo tài khoản thành viên thành công', 1, url('user'));
    }
    protected function editUser($request)
    {
        $id = $request->input('id');
        $user = UserModel::find($id);
        if (empty($user)) transfer('Tài khoản thành viên không tồn tại', 0, url('user'));
        if (!empty($request->input('password'))) {
            $validator = Validator::makeValidate($request, [
                're-password' => 'same:password'
            ], [
                're-password.same' => '*Nhập lại mật khẩu không chính xác'
            ]);
            if ($validator->isFailed()) {
                foreach ($request->all() as $k => $v) Flash::set($k, $v);
                Flash::set('message', $validator->errors());
                response()->redirect(url('user.add'));
            }
            $password = Hash::make($request->input('password'));
            if (!empty($password)) UserModel::where('id', $user->id)->update(['password' => $password]);
        } else {
            $validator = Validator::makeValidate($request, [
                'fullname' => 'required',
            ], [
                'fullname.required' => '* Vui lòng nhập họ tên',
            ]);
            if ($validator->isFailed()) {
                foreach ($request->all() as $k => $v) Flash::set($k, $v);
                Flash::set('message', $validator->errors());
                response()->redirect(url('user.add'));
            }
            if (!empty($request->input('id_student_list'))) {
                $dataStudent = $request->input('id_student_list');
                $status = '';
                foreach ($dataStudent as $attr_column => $attr_value) {
                    if ($attr_value != "") {
                        $status .= $attr_value . ',';
                    }
                    $data['list_student'] = (!empty($status)) ? rtrim($status, ",") : "";
                }
            }
            $data['fullname'] = $request->input('fullname');
            $data['address'] = $request->input('address');
            $data['phone'] = $request->input('phone');
            $data['email'] = $request->input('email');
            $data['status'] = 'hienthi';
            $data['numb'] = 1;
            UserModel::where('id', $user->id)->update($data);
        }

        if (empty($request->input('password'))) {
            $role = $user->roles()->first()??Role::find($request->input('idrole'));
            $user->terminateToRole($role);
            $user->grantRole(Role::find($request->input('idrole')));
        }

        transfer('Cập nhật tài khoản thành viên thành công', 1, url('user'));
    }

    public function delete(Request $request)
    {
        if (!empty($request->id)) {
            $id = $request->id;

            $row = UserModel::select('id')
                ->where('id', $id)
                ->first();
            if (!empty($row)) {
                UserModel::where('id', $id)->delete();
            }
        } elseif (!empty($request->listid)) {
            $listid = explode(",", $request->listid);

            for ($i = 0; $i < count($listid); $i++) {
                $id = htmlspecialchars($listid[$i]);
                $row = UserModel::select('id')
                    ->where('id', $id)
                    ->first();
                if (!empty($row)) {
                    UserModel::where('id', $id)->delete();
                }
            }
        }
        transfer('Xóa khoản thành viên thành công', 1, url('user'));
    }
}
