<?php



namespace LARAVEL\LARAVELGateway\Momo\Message\AllInOne;

class RefundRequest extends AbstractSignatureRequest
{
    protected $responseClass = RefundResponse::class;
    public function initialize(array $parameters = [])
    {
        parent::initialize($parameters);
        $this->setParameter('requestType', 'refundMoMoWallet');

        return $this;
    }
    public function getTransactionId(): ?string
    {
        return $this->getTransId();
    }
    public function setTransactionId($value)
    {
        return $this->setTransId($value);
    }
    public function getTransId(): ?string
    {
        return $this->getParameter('transId');
    }
    public function setTransId(?string $id)
    {
        return $this->setParameter('transId', $id);
    }
    protected function getSignatureParameters(): array
    {
        return [
            'partnerCode', 'accessKey', 'requestId', 'amount', 'orderId', 'transId', 'requestType',
        ];
    }
}