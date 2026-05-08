<?php


namespace LARAVEL\LARAVELGateway\OnePay\Message\Domestic;
use LARAVEL\LARAVELGateway\OnePay\Message\AbstractQueryTransactionRequest;
class QueryTransactionRequest extends AbstractQueryTransactionRequest
{
    protected $testEndpoint = 'https://mtf.onepay.vn/onecomm-pay/Vpcdps.op';

    protected $productionEndpoint = 'https://onepay.vn/onecomm-pay/Vpcdps.op';
}
