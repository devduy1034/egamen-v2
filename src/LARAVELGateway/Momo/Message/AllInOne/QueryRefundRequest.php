<?php



namespace LARAVEL\LARAVELGateway\Momo\Message\AllInOne;

class QueryRefundRequest extends AbstractSignatureRequest
{
    protected $responseClass = QueryRefundResponse::class;

    public function initialize(array $parameters = [])
    {
        parent::initialize($parameters);
        $this->setParameter('requestType', 'refundStatus');

        return $this;
    }


    protected function getSignatureParameters(): array
    {
        return [
            'partnerCode', 'accessKey', 'requestId', 'orderId', 'requestType',
        ];
    }
}