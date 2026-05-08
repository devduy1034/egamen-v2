<?php



namespace LARAVEL\LARAVELGateway\Momo\Message\AllInOne;

class IncomingResponse extends AbstractSignatureResponse
{
    protected function getSignatureParameters(): array
    {
        return [
            'partnerCode', 'accessKey', 'requestId', 'amount', 'orderId', 'orderInfo', 'orderType',
            'transId', 'message', 'localMessage', 'responseTime', 'errorCode', 'payType', 'extraData',
        ];
    }
}