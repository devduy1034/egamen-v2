<?php
namespace LARAVEL\Core\Auth;
use Illuminate\Support\Str;
use LARAVEL\Core\Support\Facades\DB;
use LARAVEL\Core\Support\Facades\Cookie;
use LARAVEL\Core\Contracts\Auth\Authentication;
use LARAVEL\Core\Support\Facades\Hash;
use LARAVEL\Core\Support\Facades\Session;
use LARAVEL\DatabaseCore\Eloquent\Model;
use LARAVEL\Models\UserModel;

final class Authenticatable implements Authentication
{

    private string $guard = "";
    private string $provider = "";
    private string $model = "";
    protected string $rememberTokenName = 'remember_token';
    private ?Model $object = null;

    public function attempt(array $options = [], bool $remember = false): bool
    {
        $model = new $this->model;
        $columnPassword = $model->password();
        $table = $model->table();
        $paramPassword = $options[$columnPassword];
        unset($options[$columnPassword]);
        $object = DB::table($table)->where($options)->first();
        if (!$object || $object && !Hash::check($paramPassword, $object->password)) {
            return false;
        }
        return $this->setUserAuth(
            $this->model::where($options)->firstOrFail(),
            $remember
        );
    }
    public function checkRemember(){
        if(empty(request()->cookie($this->rememberTokenName))) return false;
        $tokenBase = request()->cookie($this->rememberTokenName);
        [$token,$id,$username] = explode('|',$tokenBase);
        $userCheck = $this->model::where('id',$id)->where('username',$username)->first();
        $tokenName = $this->rememberTokenName;
        if(empty($userCheck) || !Hash::check($tokenBase,$userCheck->$tokenName)){
            return;
        }
        return $this->setUserAuth($userCheck,
            true
        );
    }
    public function user(): ?Model
    {
        if (!is_null($this->getObject())) {
            return $this->getObject();
        }
        if(empty($this->getCurrentGuard())) return new UserModel();

        $guardDriver = $this->getConfigDriverFromGuard(
            $this->getCurrentGuard()
        );
        switch ($guardDriver) {
            case 'session':
                $id = $_SESSION[$this->guard] ?? null;
                if (is_array($id)) {
                    $id = $id[$this->guard] ?? null;
                }
                return $id ? $this->model::find($id) : null;
            default:
                throw new \Exception('Unknown authentication');
        }
    }
    public function trueFormatKey(string $key): string
    {
        return base64_decode(strtr($key, '-_', '+/'));
    }
    public function logout(): void
    {
        $user = $this->user();
        if ($user) {
            $dataUpdate[$this->rememberTokenName] = '';
            $user->update($dataUpdate);
        }
        Cookie::queue(Cookie::forget($this->rememberTokenName,'/'));
        Cookie::setCookies();
        $guardDriver = $this->getConfigDriverFromGuard(
            $this->getCurrentGuard()
        );
        switch ($guardDriver) {
            case 'session':
                unset($_SESSION[$this->guard]);
                unset($_SESSION[$this->guard.'ckfider']);
                break;
            default:
                throw new \Exception("Unknown authentication");
        }
    }
    public function check(): bool{
        if (!is_null($this->user()) && !empty($this->user())) {
            return true;
        }
        return false;
    }
    private function setUserAuth(Model $user, bool $remember): bool
    {
        $this->setObject($user);
        $guardDriver = $this->getConfigDriverFromGuard(
            $this->getCurrentGuard()
        );
        switch ($guardDriver) {
            case 'session':
                $_SESSION[$this->guard] = $user->id;
                $_SESSION[$this->guard.'ckfider'] = true;
                break;
            default:
                throw new \Exception("Unknown authentication");
        }
        if ($user && $remember) {
            $this->updateRememberToken($user,$token = Str::random(60).'|'.$user->id.'|'.$user->username);
            Cookie::queue(Cookie::forever($this->rememberTokenName,$token,'/'));
            Cookie::setCookies();
        }
        return true;
    }
    public function guard($guard = ""): Authenticatable
    {
        if (empty($guard)) {
            $guard = $this->getDefaultGuard();
        }

        $this->setGuard($guard);
        $guard = $this->getCurrentGuard();
        $this->setProvider(
            $this->getConfigProviderFromGuard($guard)
        );
        $provider = $this->getProvider();
        $this->setModel(
            $this->getConfigModelFromProvider($provider)
        );
        return $this;
    }
    protected function getConfigModelFromProvider(string $provider): string
    {
        return config("auth.providers.{$provider}.model");
    }
    protected function getConfigProviderFromGuard(string $guard): string
    {
        return config("auth.guards.{$guard}.provider");
    }
    protected function getConfigDriverFromGuard(string $guard): string
    {
        return config("auth.guards.{$guard}.driver");
    }
    protected function setModel(string $model): void
    {
        $this->model = $model;
    }
    protected function getProvider(): string
    {
        return $this->provider;
    }
    public function setProvider(string $provider): void
    {
        $this->provider = $provider;
    }
    public function setGuard(string $guard): void
    {
        $this->guard = $guard;
    }
    private function getDefaultGuard(): string
    {
        return config('auth.defaults.guard');
    }
    public function getCurrentGuard(): string
    {
        return $this->guard;
    }
    protected function setObject(Model $object): void
    {
        $this->object = $object;
    }
    protected function getObject(): ?Model
    {
        return $this->object;
    }
    protected function updateRememberToken(Model $user,string $token): bool
    {
        return $user->update([$this->rememberTokenName=>Hash::make($token)]);
    }
}