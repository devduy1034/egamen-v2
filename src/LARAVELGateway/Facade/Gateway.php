<?php



namespace LARAVEL\LARAVELGateway\Facade;

use LARAVEL\Core\Support\Facades\Facade;
use Omnipay\Common\CreditCard;

/**
 * @method static gateway(Gateway $class)
 */
class Gateway extends Facade
{
    public static function creditCard($parameters = null): CreditCard {
        return new CreditCard($parameters);
    }
    protected static function getFacadeAccessor(): string {
        return 'gateway';
    }
}