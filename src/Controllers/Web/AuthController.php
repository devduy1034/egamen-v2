<?php

namespace LARAVEL\Controllers\Web;

use Illuminate\Http\Request;
use LARAVEL\Controllers\Controller;
use LARAVEL\Core\Support\Facades\Email;
use LARAVEL\Core\Support\Facades\File;
use LARAVEL\Core\Support\Facades\Func;
use LARAVEL\Core\Support\Facades\Hash;
use LARAVEL\Core\Support\Facades\View;
use LARAVEL\Models\MemberModel;
use LARAVEL\Models\OrdersModel;
use LARAVEL\Models\OrderHistoryModel;
use LARAVEL\Models\OrderStatusModel;
use LARAVEL\Models\Place\CityModel;
use LARAVEL\Models\Place\WardModel;
use LARAVEL\Models\WishlistModel;
use LARAVEL\Traits\TraitOrderInventory;

class AuthController extends Controller
{
    use TraitOrderInventory;

    public function showLogin(Request $request)
    {
        if (session()->has('member')) {
            return response()->redirect(url('user.account'));
        }

        $socialError = trim((string) $request->query('social_error', ''));
        $socialErrors = [
            'google_disabled' => 'Đăng nhập Google chưa được bật.',
            'google_failed' => 'Đăng nhập Google thất bại. Vui lòng thử lại.',
            'google_invalid_state' => 'Phiên đăng nhập Google không hợp lệ. Vui lòng thử lại.',
            'google_no_email' => 'Không lấy được email từ tài khoản Google.',
            'account_locked' => 'Tài khoản đã bị khóa.',
        ];

        return view('account.login', [
            'titleMain' => 'Đăng nhập',
            'error' => $socialErrors[$socialError] ?? '',
            'redirect' => $request->query('redirect') ?? '',
        ]);
    }

    public function login(Request $request)
    {
        if (empty($request->csrf_token)) {
            return response()->redirect(url('user.login'));
        }

        $identity = trim((string) $request->input('identity'));
        $password = (string) $request->input('password');
        $redirect = trim((string) $request->input('redirect'));

        if ($identity === '' || $password === '') {
            return view('account.login', [
                'titleMain' => 'Đăng nhập',
                'error' => 'Vui lòng nhập đầy đủ thông tin đăng nhập.',
                'redirect' => $redirect,
            ]);
        }

        $user = MemberModel::where('phone', $identity)->orWhere('email', $identity)->first();
        if (empty($user) || !Hash::check($password, $user->password)) {
            return view('account.login', [
                'titleMain' => 'Đăng nhập',
                'error' => 'Số điện thoại/email hoặc mật khẩu không đúng.',
                'redirect' => $redirect,
            ]);
        }
        if ($this->isMemberLocked($user)) {
            return view('account.login', [
                'titleMain' => 'Đăng nhập',
                'error' => 'Tài khoản đã bị khóa.',
                'redirect' => $redirect,
            ]);
        }

        $this->beginMemberSession($user);
        session()->set('toast', ['text' => 'Đăng nhập thành công.', 'status' => 'success']);
        return response()->redirect($this->resolveAccountRedirect($redirect));
    }

    public function showRegister(Request $request)
    {
        if (session()->has('member')) {
            return response()->redirect(url('user.account'));
        }

        $socialError = trim((string) $request->query('social_error', ''));
        $socialErrors = [
            'google_disabled' => 'Đăng ký Google chưa được bật.',
            'google_failed' => 'Đăng ký Google thất bại. Vui lòng thử lại.',
            'google_invalid_state' => 'Phiên đăng ký Google không hợp lệ. Vui lòng thử lại.',
            'google_no_email' => 'Không lấy được email từ tài khoản Google.',
            'account_locked' => 'Tài khoản đã bị khóa.',
        ];

        return view('account.register', [
            'titleMain' => 'Đăng ký tài khoản',
            'error' => $socialErrors[$socialError] ?? '',
        ]);
    }

    public function showForgotPassword(Request $request)
    {
        if (session()->has('member')) {
            return response()->redirect(url('home'));
        }

        $statusMap = [
            'sent' => 'Nếu email tồn tại trong hệ thống, chúng tôi đã gửi hướng dẫn đặt lại mật khẩu.',
        ];
        $errorMap = [
            'invalid_email' => 'Email không hợp lệ.',
            'send_failed' => 'Không thể gửi email lúc này. Vui lòng thử lại.',
            'invalid_token' => 'Liên kết đặt lại mật khẩu không hợp lệ hoặc đã hết hạn.',
        ];

        return view('account.forgot-password', [
            'titleMain' => 'Quên mật khẩu',
            'status' => $statusMap[(string) $request->query('status', '')] ?? '',
            'error' => $errorMap[(string) $request->query('error', '')] ?? '',
        ]);
    }

    public function forgotPassword(Request $request)
    {
        if (empty($request->csrf_token)) {
            return response()->redirect(url('user.forgot'));
        }

        $email = trim((string) $request->input('email'));
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return response()->redirect(url('user.forgot', null, ['error' => 'invalid_email']));
        }

        $user = MemberModel::where('email', $email)->first();
        if (empty($user)) {
            return response()->redirect(url('user.forgot', null, ['status' => 'sent']));
        }

        $token = $this->createPasswordResetToken((int) $user->id, $email);
        $message = Email::markdown('account.reset-password-mail', [
            'fullname' => (string) ($user->fullname ?? $email),
            'reset_link' => $this->makeAbsolutePublicUrl(url('user.reset', null, ['token' => $token])),
            'expire_minutes' => 60,
        ]);
        $optCompany = json_decode((string) Func::setting('options'), true) ?? [];
        $companySetting = Func::setting();
        $company = is_array($companySetting) ? $companySetting : ((is_object($companySetting) && method_exists($companySetting, 'toArray')) ? $companySetting->toArray() : []);
        $receivers = [['name' => (string) ($user->fullname ?? $email), 'email' => $email]];

        if (!$this->sendResetPasswordEmail($receivers, 'Đặt lại mật khẩu tài khoản', $message, $optCompany, $company)) {
            return response()->redirect(url('user.forgot', null, ['error' => 'send_failed']));
        }

        return response()->redirect(url('user.forgot', null, ['status' => 'sent']));
    }

    public function showResetPassword(Request $request)
    {
        if (session()->has('member')) {
            return response()->redirect(url('home'));
        }

        $token = trim((string) $request->query('token', ''));
        if (empty($this->getValidPasswordResetRecord($token))) {
            return response()->redirect(url('user.forgot', null, ['error' => 'invalid_token']));
        }

        return view('account.reset-password', [
            'titleMain' => 'Đặt lại mật khẩu',
            'token' => $token,
            'error' => '',
        ]);
    }

    public function resetPassword(Request $request)
    {
        if (empty($request->csrf_token)) {
            return response()->redirect(url('user.forgot'));
        }

        $token = trim((string) $request->input('token'));
        $password = (string) $request->input('password');
        $passwordConfirm = (string) $request->input('password_confirm');
        $record = $this->getValidPasswordResetRecord($token);

        if (empty($record)) {
            return response()->redirect(url('user.forgot', null, ['error' => 'invalid_token']));
        }

        if ($password === '' || strlen($password) < 6 || $password !== $passwordConfirm) {
            return view('account.reset-password', [
                'titleMain' => 'Đặt lại mật khẩu',
                'token' => $token,
                'error' => 'Mật khẩu mới không hợp lệ hoặc xác nhận không khớp.',
            ]);
        }

        $user = MemberModel::where('id', (int) ($record['user_id'] ?? 0))
            ->where('email', (string) ($record['email'] ?? ''))
            ->first();
        if (empty($user)) {
            return response()->redirect(url('user.forgot', null, ['error' => 'invalid_email']));
        }

        $user->update(['password' => Hash::make($password)]);
        $this->deletePasswordResetToken($token);
        $this->beginMemberSession($user);

        session()->set('toast', ['text' => 'Đặt lại mật khẩu thành công.', 'status' => 'success']);
        return response()->redirect(url('home'));
    }

    public function register(Request $request)
    {
        if (empty($request->csrf_token)) {
            return response()->redirect(url('user.register'));
        }

        $fullname = trim((string) $request->input('fullname'));
        $email = trim((string) $request->input('email'));
        $phone = trim((string) $request->input('phone'));
        $password = (string) $request->input('password');
        $passwordConfirm = (string) $request->input('password_confirm');

        $errors = [];
        if ($fullname === '') $errors[] = 'Vui lòng nhập họ tên.';
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email không hợp lệ.';
        if (MemberModel::where('email', $email)->exists()) $errors[] = 'Email đã được sử dụng.';
        if ($phone === '') $errors[] = 'Vui lòng nhập số điện thoại.';
        if (MemberModel::where('phone', $phone)->exists()) $errors[] = 'Số điện thoại đã được sử dụng.';
        if ($password === '' || strlen($password) < 6) $errors[] = 'Mật khẩu tối thiểu 6 ký tự.';
        if ($password !== $passwordConfirm) $errors[] = 'Xác nhận mật khẩu không khớp.';

        if (!empty($errors)) {
            return view('account.register', [
                'titleMain' => 'Đăng ký tài khoản',
                'error' => implode('<br>', $errors),
            ]);
        }

        $user = MemberModel::create([
            'fullname' => $fullname,
            'username' => $phone !== '' ? $phone : $email,
            'email' => $email,
            'phone' => $phone,
            'status' => 'hienthi',
            'numb' => 1,
            'password' => Hash::make($password),
        ]);

        $this->beginMemberSession($user);
        session()->set('toast', ['text' => 'Đăng ký tài khoản thành công.', 'status' => 'success']);
        return response()->redirect(url('home'));
    }

    public function googleRedirect(Request $request)
    {
        $flow = trim((string) $request->query('flow', 'login'));
        $flow = $flow === 'register' ? 'register' : 'login';
        $targetUrl = $this->googleOAuthTargetUrl($flow);
        session()->set('google_oauth_flow', $flow);

        if (!$this->isGoogleOAuthEnabled()) {
            return response()->redirect(url($targetUrl, null, ['social_error' => 'google_disabled']));
        }

        $googleConfig = config('app.oauth.google') ?? [];
        $state = bin2hex(random_bytes(16));
        session()->set('google_oauth_state', $state);
        $query = http_build_query([
            'client_id' => (string) $googleConfig['client_id'],
            'redirect_uri' => (string) $googleConfig['redirect'],
            'response_type' => 'code',
            'scope' => 'openid email profile',
            'state' => $state,
            'access_type' => 'online',
            'include_granted_scopes' => 'true',
            'prompt' => 'select_account',
        ]);

        return response()->redirect('https://accounts.google.com/o/oauth2/v2/auth?' . $query);
    }

    public function googleCallback(Request $request)
    {
        $flow = trim((string) session()->get('google_oauth_flow', 'login'));
        $flow = $flow === 'register' ? 'register' : 'login';
        $targetUrl = $this->googleOAuthTargetUrl($flow);
        session()->unset('google_oauth_flow');

        if (!$this->isGoogleOAuthEnabled()) {
            return response()->redirect(url($targetUrl, null, ['social_error' => 'google_disabled']));
        }
        if (!empty($request->query('error'))) {
            return response()->redirect(url($targetUrl, null, ['social_error' => 'google_failed']));
        }

        $state = (string) $request->query('state', '');
        $savedState = (string) session()->get('google_oauth_state', '');
        session()->unset('google_oauth_state');
        if ($state === '' || $savedState === '' || !hash_equals($savedState, $state)) {
            return response()->redirect(url($targetUrl, null, ['social_error' => 'google_invalid_state']));
        }

        $code = trim((string) $request->query('code', ''));
        if ($code === '') {
            return response()->redirect(url($targetUrl, null, ['social_error' => 'google_failed']));
        }

        $googleConfig = config('app.oauth.google') ?? [];
        $tokenData = $this->httpPostForm('https://oauth2.googleapis.com/token', [
            'code' => $code,
            'client_id' => (string) $googleConfig['client_id'],
            'client_secret' => (string) $googleConfig['client_secret'],
            'redirect_uri' => (string) $googleConfig['redirect'],
            'grant_type' => 'authorization_code',
        ]);
        $accessToken = (string) ($tokenData['access_token'] ?? '');
        if ($accessToken === '') {
            return response()->redirect(url($targetUrl, null, ['social_error' => 'google_failed']));
        }

        $profile = $this->httpGetJson('https://openidconnect.googleapis.com/v1/userinfo', [
            'Authorization: Bearer ' . $accessToken,
        ]);
        $email = trim((string) ($profile['email'] ?? ''));
        $googleSub = trim((string) ($profile['sub'] ?? ''));
        $googleLinkedFlag = $googleSub !== '' ? 1 : 0;
        if ($email === '') {
            return response()->redirect(url($targetUrl, null, ['social_error' => 'google_no_email']));
        }

        $name = trim((string) ($profile['name'] ?? ''));
        if ($name === '') {
            $name = explode('@', $email)[0] ?? 'Member';
        }

        $user = MemberModel::where('email', $email)->first();
        if (empty($user)) {
            $user = MemberModel::create([
                'fullname' => $name,
                'username' => $this->makeUniqueUsernameFromEmail($email),
                'id_social' => $googleLinkedFlag,
                'email' => $email,
                'phone' => '',
                'status' => 'hienthi',
                'numb' => 1,
                'password' => Hash::make(bin2hex(random_bytes(16))),
            ]);
        } elseif ($googleLinkedFlag === 1 && empty($user->id_social)) {
            $user->update(['id_social' => 1]);
        }
        if ($this->isMemberLocked($user)) {
            return response()->redirect(url($targetUrl, null, ['social_error' => 'account_locked']));
        }

        $this->beginMemberSession($user);
        session()->set('toast', ['text' => $flow === 'register' ? 'Đăng ký Google thành công.' : 'Đăng nhập Google thành công.', 'status' => 'success']);
        return response()->redirect(url('home'));
    }

    public function account(Request $request)
    {
        $user = $this->currentMember();
        if (empty($user)) {
            session()->unset('member');
            session()->unset('member_name');
            return response()->redirect(url('user.login'));
        }

        $activeSection = trim((string) $request->query('section', 'profile'));
        if (!in_array($activeSection, ['profile', 'orders', 'address', 'wishlist', 'security'], true)) {
            $activeSection = 'profile';
        }
        $orderStatusFilter = (int) $request->query('order_status', 0);
        $selectedOrderId = (int) $request->query('order_id', 0);
        $orderKeyword = trim((string) $request->query('order_code', ''));

        $ordersQuery = OrdersModel::with(['getStatus', 'getPayment'])
            ->where('id_user', (int) $user->id);
        if ($orderStatusFilter > 0) {
            $ordersQuery->where('order_status', $orderStatusFilter);
        }
        if ($orderKeyword !== '') {
            $ordersQuery->where(function ($query) use ($orderKeyword) {
                $query->where('code', 'like', '%' . $orderKeyword . '%');
                if (is_numeric($orderKeyword)) {
                    $query->orWhere('id', (int) $orderKeyword);
                }
            });
        }
        $orders = $ordersQuery->orderBy('id', 'desc')->paginate(20);
        $orders->appends(array_filter([
            'section' => 'orders',
            'order_status' => $orderStatusFilter > 0 ? $orderStatusFilter : null,
            'order_code' => $orderKeyword !== '' ? $orderKeyword : null,
        ], static function ($value) {
            return $value !== null && $value !== '';
        }));
        $excludedOrderStatusIds = array_values(array_filter([
            $this->resolveCanceledOrderStatusId(),
            $this->resolveDeliveredOrderStatusId(),
        ], static function ($statusId) {
            return (int) $statusId > 0;
        }));
        $activeOrdersCountQuery = OrdersModel::where('id_user', (int) $user->id);
        if (!empty($excludedOrderStatusIds)) {
            $activeOrdersCountQuery->whereNotIn('order_status', $excludedOrderStatusIds);
        }
        $activeOrdersCount = (int) $activeOrdersCountQuery->count();

        $selectedOrder = null;
        $orderTimeline = [];
        if ($selectedOrderId > 0) {
            $selectedOrder = OrdersModel::with(['getStatus', 'getPayment'])
                ->where('id', $selectedOrderId)
                ->where('id_user', (int) $user->id)
                ->first();

            if (!empty($selectedOrder)) {
                $orderTimeline = OrderHistoryModel::where('id_order', (int) $selectedOrder->id)
                    ->orderBy('id', 'desc')
                    ->get();
            }
        }

        $statusMap = [
            'profile_updated' => 'Đã cập nhật thông tin tài khoản.',
            'address_saved' => 'Đã lưu địa chỉ.',
            'address_deleted' => 'Đã xóa địa chỉ.',
            'password_changed' => 'Đổi mật khẩu thành công.',
        ];
        $errorMap = [
            'invalid_profile' => 'Thông tin tài khoản chưa hợp lệ.',
            'phone_exists' => 'Số điện thoại đã được sử dụng.',
            'invalid_address' => 'Thông tin địa chỉ chưa hợp lệ.',
            'address_not_found' => 'Không tìm thấy địa chỉ.',
            'invalid_password' => 'Thông tin mật khẩu chưa hợp lệ.',
            'wrong_current_password' => 'Mật khẩu hiện tại chưa đúng.',
            'google_unlink_password_required' => 'Vui lòng nhập mật khẩu hiện tại để hủy liên kết Google.',
        ];

        $addresses = $this->loadAddresses((int) $user->id);
        $loginSessions = $this->loadLoginSessions((int) $user->id);
        $cities = CityModel::select('id', 'namevi')->orderBy('id', 'asc')->get();
        $wishlistCount = 0;
        $wishlistItems = [];
        try {
            $wishlistCount = WishlistModel::from('wishlists')->where('user_id', (int) $user->id)->count();
            $wishlistItems = (new WishlistController())->accountItems((int) $user->id);
        } catch (\Throwable $e) {
            $wishlistCount = 0;
            $wishlistItems = [];
        }

        return view('account.index', [
            'titleMain' => 'Tài khoản của tôi',
            'user' => $user,
            'orders' => $orders,
            'orderStatuses' => OrderStatusModel::orderBy('id', 'asc')->get(),
            'orderStatusFilter' => $orderStatusFilter,
            'orderKeyword' => $orderKeyword,
            'selectedOrder' => $selectedOrder,
            'orderTimeline' => $orderTimeline,
            'addresses' => $addresses,
            'cities' => $cities,
            'activeSection' => $activeSection,
            'statusMessage' => $statusMap[(string) $request->query('status', '')] ?? '',
            'errorMessage' => $errorMap[(string) $request->query('error', '')] ?? '',
            'googleLinked' => !empty($user->id_social),
            'loginSessions' => $loginSessions,
            'currentSessionId' => $this->currentSessionKey(),
            'ordersCount' => $activeOrdersCount,
            'activeOrdersCount' => $activeOrdersCount,
            'addressesCount' => count($addresses),
            'wishlistCount' => (int) $wishlistCount,
            'wishlistItems' => $wishlistItems,
            'birthdayValue' => !empty($user->birthday) ? date('Y-m-d', (int) $user->birthday) : '',
            'noImageUrl' => rtrim((string) config('app.asset'), '/') . '/assets/images/noimage.png',
            'deliveredOrderStatusId' => $this->resolveDeliveredOrderStatusId(),
        ]);
    }

    public function accountOrderDetail(Request $request)
    {
        $user = $this->currentMember();
        if (empty($user)) {
            return response()->json([
                'status' => false,
                'message' => 'Vui lòng đăng nhập lại.',
            ], 401);
        }

        $orderId = (int) $request->query('order_id', 0);
        if ($orderId <= 0) {
            return response()->json([
                'status' => false,
                'message' => 'Đơn hàng không hợp lệ.',
            ], 422);
        }

        $order = OrdersModel::with(['getStatus', 'getPayment'])
            ->where('id', $orderId)
            ->where('id_user', (int) $user->id)
            ->first();

        if (empty($order)) {
            return response()->json([
                'status' => false,
                'message' => 'Không tìm thấy đơn hàng.',
            ], 404);
        }

        $orderTimeline = OrderHistoryModel::where('id_order', (int) $order->id)
            ->orderBy('id', 'desc')
            ->get();

        $html = View::render('account.partials.order-detail', [
            'selectedOrder' => $order,
            'orderTimeline' => $orderTimeline,
            'deliveredOrderStatusId' => $this->resolveDeliveredOrderStatusId(),
        ]);

        return response()->json([
            'status' => true,
            'order_id' => (int) $order->id,
            'html' => $html,
        ]);
    }

    public function accountCancelOrder(Request $request)
    {
        if (empty($request->csrf_token)) {
            return response()->json([
                'status' => false,
                'message' => 'Phiên làm việc đã hết hạn. Vui lòng tải lại trang.',
            ], 419);
        }

        $user = $this->currentMember();
        if (empty($user)) {
            return response()->json([
                'status' => false,
                'message' => 'Vui lòng đăng nhập lại.',
            ], 401);
        }

        $orderId = (int) $request->input('order_id', 0);
        if ($orderId <= 0) {
            return response()->json([
                'status' => false,
                'message' => 'Đơn hàng không hợp lệ.',
            ], 422);
        }

        $reason = $this->sanitizeCancellationReason((string) $request->input('reason', ''));
        if ($reason === '') {
            return response()->json([
                'status' => false,
                'message' => 'Vui lòng chọn hoặc nhập lý do hủy đơn.',
            ], 422);
        }

        $order = OrdersModel::where('id', $orderId)
            ->where('id_user', (int) $user->id)
            ->first();

        if (empty($order)) {
            return response()->json([
                'status' => false,
                'message' => 'Không tìm thấy đơn hàng.',
            ], 404);
        }

        if (!$this->canCancelOrder($order)) {
            return response()->json([
                'status' => false,
                'message' => 'Đơn hàng đã xác nhận hoặc đang giao, không thể hủy.',
            ], 422);
        }

        $cancelStatusId = $this->resolveCanceledOrderStatusId();
        if ($cancelStatusId <= 0) {
            return response()->json([
                'status' => false,
                'message' => 'Chưa cấu hình trạng thái hủy đơn.',
            ], 422);
        }

        $cancelNote = 'Khách hủy đơn: ' . $reason;
        $currentNotes = trim((string) ($order->notes ?? ''));
        $infoUser = is_array($order->info_user ?? null) ? $order->info_user : [];
        if ($this->hasReservedInventoryFlag($infoUser)) {
            $this->releaseOrderInventory($order->order_detail ?? []);
            $order->info_user = $this->clearReservedInventoryFlag($infoUser);
        }
        $order->order_status = $cancelStatusId;
        $order->notes = $currentNotes !== '' ? ($currentNotes . PHP_EOL . $cancelNote) : $cancelNote;
        $order->save();

        OrderHistoryModel::create([
            'id_order' => (int) $order->id,
            'order_status' => (int) $cancelStatusId,
            'notes' => $cancelNote,
        ]);

        $statusName = (string) (OrderStatusModel::where('id', $cancelStatusId)->value('namevi') ?? 'Đã hủy');
        return response()->json([
            'status' => true,
            'message' => 'Đã hủy đơn hàng.',
            'order_id' => (int) $order->id,
            'order_status' => (int) $cancelStatusId,
            'order_status_name' => $statusName,
        ]);
    }

    public function updateProfile(Request $request)
    {
        if (empty($request->csrf_token)) {
            return response()->redirect(url('user.account', null, ['section' => 'profile']));
        }

        $user = $this->currentMember();
        if (empty($user)) {
            return response()->redirect(url('user.login'));
        }

        $fullname = trim((string) $request->input('fullname'));
        $phone = trim((string) $request->input('phone'));
        $birthdayText = trim((string) $request->input('birthday'));
        $gender = (int) $request->input('gender', 0);

        if ($fullname === '' || $phone === '') {
            return response()->redirect(url('user.account', null, ['section' => 'profile', 'error' => 'invalid_profile']));
        }
        if (MemberModel::where('phone', $phone)->where('id', '<>', (int) $user->id)->exists()) {
            return response()->redirect(url('user.account', null, ['section' => 'profile', 'error' => 'phone_exists']));
        }
        if (!in_array($gender, [1, 2], true)) {
            return response()->redirect(url('user.account', null, ['section' => 'profile', 'error' => 'invalid_profile']));
        }

        $birthday = 0;
        if ($birthdayText !== '') {
            $parsedBirthday = strtotime(str_replace('/', '-', $birthdayText));
            if ($parsedBirthday === false) {
                return response()->redirect(url('user.account', null, ['section' => 'profile', 'error' => 'invalid_profile']));
            }
            $birthday = (int) $parsedBirthday;
        }

        $dataUpdate = [
            'fullname' => $fullname,
            'phone' => $phone,
            'birthday' => $birthday,
            'gender' => $gender,
        ];

        $removeAvatar = (string) $request->input('remove_avatar', '0') === '1';
        if ($removeAvatar) {
            $oldAvatar = (string) ($user->avatar ?? '');
            $dataUpdate['avatar'] = '';
            if ($oldAvatar !== '' && File::exists(upload('user', $oldAvatar))) {
                File::delete(upload('user', $oldAvatar));
            }
        }

        $avatarFile = $request->file('avatar');
        if (!empty($avatarFile) && method_exists($avatarFile, 'getClientOriginalName')) {
            $ext = strtolower((string) pathinfo((string) $avatarFile->getClientOriginalName(), PATHINFO_EXTENSION));
            if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true)) {
                return response()->redirect(url('user.account', null, ['section' => 'profile', 'error' => 'invalid_profile']));
            }

            $avatarName = time() . '_' . preg_replace('/[^a-zA-Z0-9\._-]/', '', (string) $avatarFile->getClientOriginalName());
            if ($avatarFile->storeAs('user', $avatarName)) {
                $oldAvatar = (string) ($user->avatar ?? '');
                $dataUpdate['avatar'] = $avatarName;
                if ($oldAvatar !== '' && File::exists(upload('user', $oldAvatar))) {
                    File::delete(upload('user', $oldAvatar));
                }
            }
        }

        $user->update($dataUpdate);
        session()->set('member_name', $fullname);
        session()->set('toast', ['text' => 'Cập nhật thông tin thành công.', 'status' => 'success']);

        return response()->redirect(url('user.account', null, ['section' => 'profile', 'status' => 'profile_updated']));
    }

    public function saveAddress(Request $request)
    {
        if (empty($request->csrf_token)) {
            return response()->redirect(url('user.account', null, ['section' => 'address']));
        }

        $user = $this->currentMember();
        if (empty($user)) {
            return response()->redirect(url('user.login'));
        }

        $addressId = trim((string) $request->input('address_id'));
        $recipientName = trim((string) $request->input('recipient_name'));
        $recipientPhone = trim((string) $request->input('recipient_phone'));
        $addressLine = trim((string) $request->input('address_line'));
        $cityRaw = trim((string) $request->input('city'));
        $city = $cityRaw;
        if ($cityRaw !== '' && ctype_digit($cityRaw)) {
            $cityRow = CityModel::select('namevi')->where('id', (int) $cityRaw)->first();
            if (!empty($cityRow) && !empty($cityRow->namevi)) {
                $city = trim((string) $cityRow->namevi);
            }
        }
        $ward = trim((string) $request->input('ward'));
        $isDefault = (string) $request->input('is_default') === '1';

        if ($recipientName === '' || $recipientPhone === '' || $addressLine === '') {
            return response()->redirect(url('user.account', null, ['section' => 'address', 'error' => 'invalid_address']));
        }

        $addresses = $this->loadAddresses((int) $user->id);
        $editingIndex = null;
        if ($addressId !== '') {
            foreach ($addresses as $index => $address) {
                if ((string) ($address['id'] ?? '') === $addressId) {
                    $editingIndex = $index;
                    break;
                }
            }
        }

        $record = [
            'id' => $addressId !== '' ? $addressId : bin2hex(random_bytes(8)),
            'recipient_name' => $recipientName,
            'recipient_phone' => $recipientPhone,
            'address_line' => $addressLine,
            'city' => $city,
            'ward' => $ward,
            'is_default' => $isDefault ? 1 : 0,
            'updated_at' => time(),
        ];
        if ($editingIndex === null) {
            $record['created_at'] = time();
            $addresses[] = $record;
        } else {
            $record['created_at'] = (int) ($addresses[$editingIndex]['created_at'] ?? time());
            $addresses[$editingIndex] = $record;
        }

        if (count($addresses) === 1) {
            $addresses[0]['is_default'] = 1;
        }

        if (!empty($record['is_default'])) {
            foreach ($addresses as $index => $address) {
                $addresses[$index]['is_default'] = ((string) ($address['id'] ?? '') === (string) $record['id']) ? 1 : 0;
            }
        } else {
            $hasDefault = false;
            foreach ($addresses as $address) {
                if (!empty($address['is_default'])) {
                    $hasDefault = true;
                    break;
                }
            }
            if (!$hasDefault && !empty($addresses)) {
                $addresses[0]['is_default'] = 1;
            }
        }

        $this->saveAddresses((int) $user->id, $addresses);
        $this->syncDefaultAddressToMember($user, $addresses);

        session()->set('toast', ['text' => 'Lưu địa chỉ thành công.', 'status' => 'success']);
        return response()->redirect(url('user.account', null, ['section' => 'address', 'status' => 'address_saved']));
    }

    public function wardsByCity(Request $request): void
    {
        $cityId = (int) $request->query('city_id', 0);
        if ($cityId <= 0) {
            response()->json(['wards' => []]);
            return;
        }

        $wards = WardModel::select('id', 'namevi')->where('id_city', $cityId)->orderBy('id', 'asc')->get();
        response()->json(['wards' => $wards]);
    }

    public function deleteAddress(Request $request)
    {
        if (empty($request->csrf_token)) {
            return response()->redirect(url('user.account', null, ['section' => 'address']));
        }

        $user = $this->currentMember();
        if (empty($user)) {
            return response()->redirect(url('user.login'));
        }

        $addressId = trim((string) $request->input('address_id'));
        if ($addressId === '') {
            return response()->redirect(url('user.account', null, ['section' => 'address', 'error' => 'address_not_found']));
        }

        $addresses = $this->loadAddresses((int) $user->id);
        $kept = [];
        $removed = false;
        foreach ($addresses as $address) {
            if ((string) ($address['id'] ?? '') === $addressId) {
                $removed = true;
                continue;
            }
            $kept[] = $address;
        }

        if (!$removed) {
            return response()->redirect(url('user.account', null, ['section' => 'address', 'error' => 'address_not_found']));
        }

        $hasDefault = false;
        foreach ($kept as $address) {
            if (!empty($address['is_default'])) {
                $hasDefault = true;
                break;
            }
        }
        if (!$hasDefault && !empty($kept)) {
            $kept[0]['is_default'] = 1;
        }

        $this->saveAddresses((int) $user->id, $kept);
        $this->syncDefaultAddressToMember($user, $kept);

        session()->set('toast', ['text' => 'Xóa địa chỉ thành công.', 'status' => 'success']);
        return response()->redirect(url('user.account', null, ['section' => 'address', 'status' => 'address_deleted']));
    }

    public function changePassword(Request $request)
    {
        if (empty($request->csrf_token)) {
            return response()->redirect(url('user.account', null, ['section' => 'security']));
        }

        $user = $this->currentMember();
        if (empty($user)) {
            return response()->redirect(url('user.login'));
        }

        $currentPassword = (string) $request->input('current_password');
        $newPassword = (string) $request->input('new_password');
        $newPasswordConfirm = (string) $request->input('new_password_confirm');

        if ($currentPassword === '' || $newPassword === '' || $newPasswordConfirm === '') {
            return response()->redirect(url('user.account', null, ['section' => 'security', 'error' => 'invalid_password']));
        }
        if (!Hash::check($currentPassword, (string) $user->password)) {
            return response()->redirect(url('user.account', null, ['section' => 'security', 'error' => 'wrong_current_password']));
        }
        if ($newPassword !== $newPasswordConfirm) {
            return response()->redirect(url('user.account', null, ['section' => 'security', 'error' => 'invalid_password']));
        }
        if (Hash::check($newPassword, (string) $user->password)) {
            return response()->redirect(url('user.account', null, ['section' => 'security', 'error' => 'invalid_password']));
        }
        if (!$this->isStrongPassword($newPassword)) {
            return response()->redirect(url('user.account', null, ['section' => 'security', 'error' => 'invalid_password']));
        }

        $user->update(['password' => Hash::make($newPassword)]);
        $currentSessionId = $this->currentSessionKey();
        $sessions = $this->loadLoginSessions((int) $user->id);
        $sessions = array_values(array_filter($sessions, function ($row) use ($currentSessionId) {
            return (string) ($row['session_id'] ?? '') === $currentSessionId;
        }));
        $this->saveLoginSessions((int) $user->id, $sessions);
        session()->set('toast', ['text' => 'Đổi mật khẩu thành công.', 'status' => 'success']);
        return response()->redirect(url('user.account', null, ['section' => 'security', 'status' => 'password_changed']));
    }

    public function unlinkGoogle(Request $request)
    {
        if (empty($request->csrf_token)) {
            return response()->redirect(url('user.account', null, ['section' => 'security']));
        }

        $user = $this->currentMember();
        if (empty($user)) {
            return response()->redirect(url('user.login'));
        }

        $currentPassword = trim((string) $request->input('current_password'));
        if ($currentPassword === '') {
            return response()->redirect(url('user.account', null, ['section' => 'security', 'error' => 'google_unlink_password_required']));
        }
        if (empty($user->password) || !Hash::check($currentPassword, (string) $user->password)) {
            return response()->redirect(url('user.account', null, ['section' => 'security', 'error' => 'wrong_current_password']));
        }

        $user->update(['id_social' => 0]);
        session()->set('toast', ['text' => 'Đã hủy liên kết Google.', 'status' => 'success']);

        return response()->redirect(url('user.account', null, ['section' => 'security', 'status' => 'google_unlinked']));
    }

    public function revokeSession(Request $request)
    {
        if (empty($request->csrf_token)) {
            return response()->redirect(url('user.account', null, ['section' => 'security']));
        }

        $user = $this->currentMember();
        if (empty($user)) {
            return response()->redirect(url('user.login'));
        }

        $targetSessionId = trim((string) $request->input('session_id'));
        if ($targetSessionId === '') {
            return response()->redirect(url('user.account', null, ['section' => 'security', 'error' => 'session_not_found']));
        }

        $sessions = $this->loadLoginSessions((int) $user->id);
        $found = false;
        foreach ($sessions as $index => $sessionRow) {
            if ((string) ($sessionRow['session_id'] ?? '') === $targetSessionId) {
                $found = true;
                unset($sessions[$index]);
                break;
            }
        }
        if (!$found) {
            return response()->redirect(url('user.account', null, ['section' => 'security', 'error' => 'session_not_found']));
        }

        $this->saveLoginSessions((int) $user->id, array_values($sessions));
        if ($targetSessionId === $this->currentSessionKey()) {
            session()->unset('member');
            session()->unset('member_name');
            session()->set('toast', ['text' => 'Phiên hiện tại đã đăng xuất.', 'status' => 'success']);
            return response()->redirect(url('user.login'));
        }

        session()->set('toast', ['text' => 'Đã đăng xuất phiên được chọn.', 'status' => 'success']);
        return response()->redirect(url('user.account', null, ['section' => 'security', 'status' => 'session_revoked']));
    }

    public function revokeOtherSessions(Request $request)
    {
        if (empty($request->csrf_token)) {
            return response()->redirect(url('user.account', null, ['section' => 'security']));
        }

        $user = $this->currentMember();
        if (empty($user)) {
            return response()->redirect(url('user.login'));
        }

        $currentSessionId = $this->currentSessionKey();
        $sessions = $this->loadLoginSessions((int) $user->id);
        $sessions = array_values(array_filter($sessions, function ($row) use ($currentSessionId) {
            return (string) ($row['session_id'] ?? '') === $currentSessionId;
        }));
        $this->saveLoginSessions((int) $user->id, $sessions);

        session()->set('toast', ['text' => 'Đã đăng xuất tất cả thiết bị khác.', 'status' => 'success']);
        return response()->redirect(url('user.account', null, ['section' => 'security', 'status' => 'other_sessions_revoked']));
    }

    public function logout()
    {
        $user = $this->currentMember();
        if (!empty($user)) {
            $this->removeLoginSession((int) $user->id, $this->currentSessionKey());
        }
        session()->unset('member');
        session()->unset('member_name');
        session()->set('toast', ['text' => 'Đăng xuất thành công.', 'status' => 'success']);
        return response()->redirect(url('home'));
    }

    private function beginMemberSession(MemberModel $user): void
    {
        session()->set('member', (int) $user->id);
        session()->set('member_name', (string) ($user->fullname ?? $user->username ?? 'Member'));
        $this->registerLoginSession((int) $user->id);
    }

    private function currentMember(): ?MemberModel
    {
        $memberSession = session()->get('member');
        if (is_array($memberSession)) {
            $memberSession = $memberSession['member'] ?? 0;
        }
        $userId = (int) $memberSession;
        if ($userId <= 0) {
            return null;
        }
        $user = MemberModel::where('id', $userId)->first();
        if ($this->isMemberLocked($user)) {
            return null;
        }

        return $user;
    }

    private function isMemberLocked(?MemberModel $user): bool
    {
        if (empty($user)) {
            return false;
        }

        return strtolower(trim((string) ($user->status ?? ''))) === 'locked';
    }

    private function loadAddresses(int $userId): array
    {
        $path = $this->addressStoragePath($userId);
        if (!is_file($path)) {
            return [];
        }

        $payload = json_decode((string) file_get_contents($path), true);
        return is_array($payload) ? array_values($payload) : [];
    }

    private function saveAddresses(int $userId, array $addresses): void
    {
        $path = $this->addressStoragePath($userId);
        file_put_contents($path, json_encode(array_values($addresses), JSON_UNESCAPED_UNICODE));
    }

    private function syncDefaultAddressToMember(MemberModel $user, array $addresses): void
    {
        $default = null;
        foreach ($addresses as $address) {
            if (!empty($address['is_default'])) {
                $default = $address;
                break;
            }
        }

        if (empty($default)) {
            $user->update(['address' => '']);
            return;
        }

        $user->update(['address' => (string) ($default['address_line'] ?? '')]);
    }

    private function addressStoragePath(int $userId): string
    {
        $dir = dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'caches' . DIRECTORY_SEPARATOR . 'member_addresses';
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }

        return $dir . DIRECTORY_SEPARATOR . 'member_' . $userId . '.json';
    }

    private function currentSessionKey(): string
    {
        if (function_exists('session_id')) {
            $sessionId = (string) session_id();
            if ($sessionId !== '') {
                return $sessionId;
            }
        }

        return hash('sha256', (string) ($_SERVER['REMOTE_ADDR'] ?? '') . '|' . (string) ($_SERVER['HTTP_USER_AGENT'] ?? ''));
    }

    private function loginSessionStoragePath(int $userId): string
    {
        $dir = dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'caches' . DIRECTORY_SEPARATOR . 'member_login_sessions';
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }

        return $dir . DIRECTORY_SEPARATOR . 'member_' . $userId . '.json';
    }

    private function loadLoginSessions(int $userId): array
    {
        $path = $this->loginSessionStoragePath($userId);
        if (!is_file($path)) {
            return [];
        }

        $payload = json_decode((string) file_get_contents($path), true);
        if (!is_array($payload)) {
            return [];
        }

        usort($payload, function ($a, $b) {
            return (int) ($b['last_seen'] ?? 0) <=> (int) ($a['last_seen'] ?? 0);
        });

        return array_values($payload);
    }

    private function saveLoginSessions(int $userId, array $sessions): void
    {
        $path = $this->loginSessionStoragePath($userId);
        file_put_contents($path, json_encode(array_values($sessions), JSON_UNESCAPED_UNICODE));
    }

    private function registerLoginSession(int $userId): void
    {
        $sessions = $this->loadLoginSessions($userId);
        $sessionId = $this->currentSessionKey();
        $now = time();
        $updated = false;

        foreach ($sessions as $index => $row) {
            if ((string) ($row['session_id'] ?? '') === $sessionId) {
                $sessions[$index]['last_seen'] = $now;
                $sessions[$index]['ip'] = (string) ($_SERVER['REMOTE_ADDR'] ?? '');
                $sessions[$index]['user_agent'] = (string) ($_SERVER['HTTP_USER_AGENT'] ?? '');
                $updated = true;
                break;
            }
        }

        if (!$updated) {
            $sessions[] = [
                'session_id' => $sessionId,
                'ip' => (string) ($_SERVER['REMOTE_ADDR'] ?? ''),
                'user_agent' => (string) ($_SERVER['HTTP_USER_AGENT'] ?? ''),
                'created_at' => $now,
                'last_seen' => $now,
            ];
        }

        $sessions = array_values(array_slice($sessions, 0, 15));
        $this->saveLoginSessions($userId, $sessions);
    }

    private function removeLoginSession(int $userId, string $sessionId): void
    {
        if ($sessionId === '') {
            return;
        }

        $sessions = $this->loadLoginSessions($userId);
        $sessions = array_values(array_filter($sessions, function ($row) use ($sessionId) {
            return (string) ($row['session_id'] ?? '') !== $sessionId;
        }));
        $this->saveLoginSessions($userId, $sessions);
    }

    private function isGoogleOAuthEnabled(): bool
    {
        $googleConfig = config('app.oauth.google') ?? [];
        return !empty($googleConfig['active']) && !empty($googleConfig['client_id']) && !empty($googleConfig['client_secret']) && !empty($googleConfig['redirect']);
    }

    private function googleOAuthTargetUrl(string $flow): string
    {
        return $flow === 'register' ? 'user.register' : 'user.login';
    }

    private function httpPostForm(string $url, array $payload): array
    {
        $ch = curl_init($url);
        if ($ch === false) {
            return [];
        }

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_TIMEOUT => 20,
            CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
            CURLOPT_POSTFIELDS => http_build_query($payload),
        ]);

        $response = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response === false || $httpCode < 200 || $httpCode >= 300) {
            return [];
        }

        $decoded = json_decode((string) $response, true);
        return is_array($decoded) ? $decoded : [];
    }

    private function httpGetJson(string $url, array $headers = []): array
    {
        $ch = curl_init($url);
        if ($ch === false) {
            return [];
        }

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 20,
            CURLOPT_HTTPHEADER => $headers,
        ]);

        $response = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response === false || $httpCode < 200 || $httpCode >= 300) {
            return [];
        }

        $decoded = json_decode((string) $response, true);
        return is_array($decoded) ? $decoded : [];
    }

    private function makeUniqueUsernameFromEmail(string $email): string
    {
        $base = strtolower(trim((string) explode('@', $email)[0]));
        $base = preg_replace('/[^a-z0-9_]/', '', $base ?? '') ?: 'member';
        $candidate = $base;
        $index = 1;
        while (MemberModel::where('username', $candidate)->exists()) {
            $candidate = $base . $index;
            $index++;
        }

        return $candidate;
    }

    private function isStrongPassword(string $password): bool
    {
        $length = strlen($password);
        if ($length < 8 || $length > 128) {
            return false;
        }

        $hasUpper = preg_match('/[A-Z]/', $password) === 1;
        $hasLower = preg_match('/[a-z]/', $password) === 1;
        $hasDigit = preg_match('/[0-9]/', $password) === 1;
        $hasSpecial = preg_match('/[^A-Za-z0-9]/', $password) === 1;

        return $hasUpper && $hasLower && $hasDigit && $hasSpecial;
    }

    private function canCancelOrder(OrdersModel $order): bool
    {
        return (int) ($order->order_status ?? 0) === 1;
    }

    private function sanitizeCancellationReason(string $reason): string
    {
        $reason = trim(strip_tags($reason));
        $reason = preg_replace('/\s+/u', ' ', $reason) ?? '';
        if (mb_strlen($reason, 'UTF-8') > 300) {
            $reason = mb_substr($reason, 0, 300, 'UTF-8');
        }

        return trim($reason);
    }

    private function resolveCanceledOrderStatusId(): int
    {
        $statuses = OrderStatusModel::select('id', 'namevi')->get();
        if (empty($statuses)) {
            return 0;
        }

        $keywordGroups = [
            ['huy don', 'da huy', 'huy'],
            ['cancel', 'cancelled', 'canceled'],
        ];

        foreach ($keywordGroups as $keywords) {
            foreach ($statuses as $status) {
                $name = $this->normalizeText((string) ($status->namevi ?? ''));
                foreach ($keywords as $keyword) {
                    if ($keyword !== '' && strpos($name, $keyword) !== false) {
                        return (int) ($status->id ?? 0);
                    }
                }
            }
        }

        return 0;
    }

    private function resolveDeliveredOrderStatusId(): int
    {
        $statuses = OrderStatusModel::select('id', 'namevi')->get();
        if (empty($statuses)) {
            return 0;
        }

        $keywordGroups = [
            ['da giao', 'giao hang thanh cong', 'giao thanh cong', 'hoan tat', 'hoan thanh'],
            ['delivered', 'completed'],
        ];

        foreach ($keywordGroups as $keywords) {
            foreach ($statuses as $status) {
                $name = $this->normalizeText((string) ($status->namevi ?? ''));
                foreach ($keywords as $keyword) {
                    if ($keyword !== '' && strpos($name, $keyword) !== false) {
                        return (int) ($status->id ?? 0);
                    }
                }
            }
        }

        return 0;
    }

    private function normalizeText(string $value): string
    {
        $value = mb_strtolower(trim($value), 'UTF-8');
        if ($value === '') {
            return '';
        }

        $replacements = [
            'à' => 'a', 'á' => 'a', 'ạ' => 'a', 'ả' => 'a', 'ã' => 'a', 'â' => 'a', 'ầ' => 'a', 'ấ' => 'a', 'ậ' => 'a', 'ẩ' => 'a', 'ẫ' => 'a', 'ă' => 'a', 'ằ' => 'a', 'ắ' => 'a', 'ặ' => 'a', 'ẳ' => 'a', 'ẵ' => 'a',
            'è' => 'e', 'é' => 'e', 'ẹ' => 'e', 'ẻ' => 'e', 'ẽ' => 'e', 'ê' => 'e', 'ề' => 'e', 'ế' => 'e', 'ệ' => 'e', 'ể' => 'e', 'ễ' => 'e',
            'ì' => 'i', 'í' => 'i', 'ị' => 'i', 'ỉ' => 'i', 'ĩ' => 'i',
            'ò' => 'o', 'ó' => 'o', 'ọ' => 'o', 'ỏ' => 'o', 'õ' => 'o', 'ô' => 'o', 'ồ' => 'o', 'ố' => 'o', 'ộ' => 'o', 'ổ' => 'o', 'ỗ' => 'o', 'ơ' => 'o', 'ờ' => 'o', 'ớ' => 'o', 'ợ' => 'o', 'ở' => 'o', 'ỡ' => 'o',
            'ù' => 'u', 'ú' => 'u', 'ụ' => 'u', 'ủ' => 'u', 'ũ' => 'u', 'ư' => 'u', 'ừ' => 'u', 'ứ' => 'u', 'ự' => 'u', 'ử' => 'u', 'ữ' => 'u',
            'ỳ' => 'y', 'ý' => 'y', 'ỵ' => 'y', 'ỷ' => 'y', 'ỹ' => 'y',
            'đ' => 'd',
        ];

        $value = strtr($value, $replacements);
        $value = preg_replace('/[^a-z0-9]+/', ' ', $value) ?? '';
        return trim($value);
    }

    private function sendResetPasswordEmail(array $receivers, string $subject, string $message, array $optCompany, array $company): bool
    {
        if (empty($optCompany['mailertype']) || empty($company)) {
            return false;
        }

        return Email::send('customer', $receivers, $subject, $message, '', $optCompany, $company);
    }

    private function createPasswordResetToken(int $userId, string $email): string
    {
        $this->purgePasswordResetTokensByEmail($email);
        $this->cleanupExpiredPasswordResetTokens();

        $token = bin2hex(random_bytes(32));
        $hash = hash('sha256', $token);
        $payload = [
            'user_id' => $userId,
            'email' => $email,
            'expires_at' => time() + 3600,
            'created_at' => time(),
        ];
        file_put_contents($this->passwordResetDirectory() . DIRECTORY_SEPARATOR . $hash . '.json', json_encode($payload));

        return $token;
    }

    private function getValidPasswordResetRecord(string $token): array
    {
        if ($token === '') {
            return [];
        }

        $path = $this->passwordResetDirectory() . DIRECTORY_SEPARATOR . hash('sha256', $token) . '.json';
        if (!is_file($path)) {
            return [];
        }

        $data = json_decode((string) file_get_contents($path), true);
        if (!is_array($data) || (int) ($data['expires_at'] ?? 0) < time()) {
            @unlink($path);
            return [];
        }

        return $data;
    }

    private function deletePasswordResetToken(string $token): void
    {
        if ($token === '') {
            return;
        }

        $path = $this->passwordResetDirectory() . DIRECTORY_SEPARATOR . hash('sha256', $token) . '.json';
        if (is_file($path)) {
            @unlink($path);
        }
    }

    private function purgePasswordResetTokensByEmail(string $email): void
    {
        if ($email === '') {
            return;
        }

        $pattern = $this->passwordResetDirectory() . DIRECTORY_SEPARATOR . '*.json';
        foreach (glob($pattern) ?: [] as $file) {
            $data = json_decode((string) file_get_contents($file), true);
            if (!is_array($data) || (string) ($data['email'] ?? '') === $email) {
                @unlink($file);
            }
        }
    }

    private function cleanupExpiredPasswordResetTokens(): void
    {
        $pattern = $this->passwordResetDirectory() . DIRECTORY_SEPARATOR . '*.json';
        foreach (glob($pattern) ?: [] as $file) {
            $data = json_decode((string) file_get_contents($file), true);
            if (!is_array($data) || (int) ($data['expires_at'] ?? 0) < time()) {
                @unlink($file);
            }
        }
    }

    private function passwordResetDirectory(): string
    {
        $dir = dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'caches' . DIRECTORY_SEPARATOR . 'password_resets';
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }

        return $dir;
    }

    private function makeAbsolutePublicUrl(string $pathOrUrl): string
    {
        $url = trim($pathOrUrl);
        if ($url === '' || preg_match('/^https?:\/\//i', $url)) {
            return $url;
        }

        $base = rtrim((string) (config('app.asset') ?? ''), '/');
        if ($base === '') {
            return $url;
        }

        $path = ltrim($url, '/');
        $parts = parse_url($base);
        $scheme = $parts['scheme'] ?? 'http';
        $host = $parts['host'] ?? '';
        $port = isset($parts['port']) ? ':' . $parts['port'] : '';
        $origin = $host !== '' ? ($scheme . '://' . $host . $port) : '';
        $basePath = trim((string) ($parts['path'] ?? ''), '/');

        if ($origin !== '' && $basePath !== '' && ($path === $basePath || str_starts_with($path, $basePath . '/'))) {
            return $origin . '/' . $path;
        }

        return $base . '/' . $path;
    }

    private function resolveAccountRedirect(string $redirect): string
    {
        $path = trim($redirect);
        if ($path === '') {
            return url('home');
        }

        if (preg_match('/^https?:\/\//i', $path)) {
            return url('home');
        }
        if (str_starts_with($path, '//')) {
            return url('home');
        }

        if (!str_starts_with($path, '/')) {
            $path = '/' . ltrim($path, '/');
        }

        return $path;
    }
}
