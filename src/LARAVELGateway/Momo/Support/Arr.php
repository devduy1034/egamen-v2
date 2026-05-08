<?php



namespace LARAVEL\LARAVELGateway\Momo\Support;

class Arr
{
    public static function getValue($element, array $arr, $default = null)
    {
        while (false !== ($pos = strpos($element, '.'))) {
            $sub = substr($element, 0, $pos);
            $element = substr($element, $pos + 1);

            if (isset($arr[$sub]) && is_array($arr[$sub])) {
                $arr = $arr[$sub];
            } else {
                break;
            }
        }
        if (false === strpos($element, '.')) {
            return $arr[$element] ?? $default;
        }
        return $default;
    }
}