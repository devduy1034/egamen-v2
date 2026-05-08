<?php


namespace LARAVEL\LARAVELGateway\OnePay\Message\International;

use LARAVEL\LARAVELGateway\OnePay\Message\AbstractPurchaseRequest;

class PurchaseRequest extends AbstractPurchaseRequest
{
    protected $testEndpoint = 'https://mtf.onepay.vn/vpcpay/vpcpay.op';

    protected $productionEndpoint = 'https://onepay.vn/vpcpay/vpcpay.op';
}
