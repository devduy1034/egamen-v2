<?php


namespace LARAVEL\LARAVELGateway\Omnipay;
use LARAVEL\LARAVELGateway\Omnipay\GatewayFactory;

class LARAVELPayment extends \Omnipay\Omnipay
{
    private static $factory;
    public static function getFactory()
    {
        if (is_null(self::$factory)) {
            self::$factory = new GatewayFactory;
        }
        return self::$factory;
    }
    public static function setFactory(GatewayFactory|\Omnipay\Common\GatewayFactory $factory = null)
    {
        self::$factory = $factory;
    }
    public static function __callStatic($method, $parameters)
    {
        $factory = self::getFactory();

        return call_user_func_array(array($factory, $method), $parameters);
    }
}