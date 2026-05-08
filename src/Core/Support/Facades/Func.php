<?php
namespace LARAVEL\Core\Support\Facades;

/**
 * @method static formatMoney(float $price)
 * @method static buildSchemaProduct(mixed $id, mixed $string, array $url_img_pro, mixed $string1, mixed $code, mixed $name_list, mixed $string2, mixed $url_pro, mixed $price)
 * @method static stringRandom(int $int)
 */
class Func extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'func';
    }
}