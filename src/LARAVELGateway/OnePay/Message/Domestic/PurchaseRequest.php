<?php


namespace LARAVEL\LARAVELGateway\OnePay\Message\Domestic;
use LARAVEL\LARAVELGateway\OnePay\Message\AbstractPurchaseRequest;

class PurchaseRequest extends AbstractPurchaseRequest
{
    protected $testEndpoint = 'https://mtf.onepay.vn/onecomm-pay/vpc.op';

    protected $productionEndpoint = 'https://onepay.vn/onecomm-pay/vpc.op';
}
