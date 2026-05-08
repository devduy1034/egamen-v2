<?php



namespace LARAVEL\LARAVELGateway\Omnipay;

use Omnipay\Common\Exception\RuntimeException;
use Omnipay\Common\Http\ClientInterface;
use Symfony\Component\HttpFoundation\Request as HttpRequest;

class GatewayFactory extends \Omnipay\Common\GatewayFactory
{
    public function create($class, ClientInterface $httpClient = null, HttpRequest $httpRequest = null)
    {
        $class = \LARAVEL\LARAVELGateway\Omnipay\Helper::getGatewayClassName($class);
        if (!class_exists($class)) {
            throw new RuntimeException("Class '$class' not found");
        }
        return new $class($httpClient, $httpRequest);
    }
}