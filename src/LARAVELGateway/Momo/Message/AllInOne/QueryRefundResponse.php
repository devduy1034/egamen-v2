<?php



namespace LARAVEL\LARAVELGateway\Momo\Message\AllInOne;

class QueryRefundResponse extends AbstractSignatureResponse
{
    protected function getSignatureParameters(): array
    {
        return [
            'partnerCode', 'accessKey', 'requestId', 'orderId', 'errorCode', 'transId', 'amount', 'message',
            'localMessage', 'requestType',
        ];
    }
}