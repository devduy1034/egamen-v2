<?php



namespace LARAVEL\LARAVELGateway\Omnipay;

use Omnipay\Common\GatewayInterface;

class Helper extends \Omnipay\Common\Helper
{
    public static function getGatewayClassName($shortName)
    {
        if (0 === strpos($shortName, '\\') || 0 === strpos($shortName, 'Omnipay\\')) {
            return $shortName;
        }
        if (is_subclass_of($shortName, GatewayInterface::class, true)) {
            return $shortName;
        }
        $shortName = str_replace('_', '\\', $shortName);
        if (false === strpos($shortName, '\\')) {
            $shortName .= '\\';
        }
        return '\\LARAVEL\LARAVELGateway\\'.$shortName.'Gateway';
    }
}