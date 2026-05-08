<?php



namespace LARAVEL\LARAVELGateway\Momo\Message\AllInOne;

class RefundResponse extends AbstractSignatureResponse
{
    protected function getSignatureParameters(): array
    {
        return [
            'partnerCode', 'accessKey', 'requestId', 'orderId', 'errorCode', 'transId', 'message',
            'localMessage', 'requestType',
        ];
    }
}
