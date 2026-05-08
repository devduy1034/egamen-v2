<?php

namespace LARAVEL\Models;

use LARAVEL\DatabaseCore\Eloquent\Factories\HasFactory;
use LARAVEL\DatabaseCore\Eloquent\Model;

class VoucherModel extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $table = 'vouchers';
}
