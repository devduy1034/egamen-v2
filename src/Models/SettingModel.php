<?php



namespace LARAVEL\Models;
use LARAVEL\DatabaseCore\Eloquent\Factories\HasFactory;
use LARAVEL\DatabaseCore\Eloquent\Model;

/**
 * @method static first()
 */
class SettingModel extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $table = 'setting';
}