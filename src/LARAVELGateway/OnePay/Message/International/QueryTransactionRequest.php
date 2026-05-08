<?php


namespace LARAVEL\LARAVELGateway\OnePay\Message\International;

use LARAVEL\LARAVELGateway\OnePay\Message\AbstractQueryTransactionRequest;
class QueryTransactionRequest extends AbstractQueryTransactionRequest
{
    protected $testEndpoint = 'https://mtf.onepay.vn/vpcpay/Vpcdps.op';

    protected $productionEndpoint = 'https://onepay.vn/vpcpay/Vpcdps.op';
}
