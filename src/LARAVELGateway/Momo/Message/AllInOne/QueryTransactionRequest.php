<?php



namespace LARAVEL\LARAVELGateway\Momo\Message\AllInOne;

class QueryTransactionRequest extends AbstractSignatureRequest
{
    protected $responseClass = QueryTransactionResponse::class;
    public function initialize(array $parameters = [])
    {
        parent::initialize($parameters);
        $this->setParameter('requestType', 'transactionStatus');

        return $this;
    }
    protected function getSignatureParameters(): array
    {
        return [
            'partnerCode', 'accessKey', 'requestId', 'orderId', 'requestType',
        ];
    }
}