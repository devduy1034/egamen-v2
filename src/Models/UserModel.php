<?php



namespace LARAVEL\Models;
use LARAVEL\DatabaseCore\Eloquent\Factories\HasFactory;
use LARAVEL\DatabaseCore\Eloquent\Authenticate;
use LARAVELPermission\Traits\HasPermission;

class UserModel extends Authenticate
{
    use HasFactory,HasPermission;
    public $timestamps = false;
    protected $guard = "admin";
    protected $table = 'user';
    protected $guarded = [];
    protected $hidden = [
        'password'
    ];
    protected $casts = [
        'password' => 'hashed'
    ];
    protected string $username = 'username';
    protected string $password = 'password';
}