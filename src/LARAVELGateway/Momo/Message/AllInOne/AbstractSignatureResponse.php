<?php



namespace LARAVEL\LARAVELGateway\Momo\Message\AllInOne;
use LARAVEL\LARAVELGateway\MoMo\Message\AbstractSignatureResponse as BaseAbstractSignatureResponse;
abstract class AbstractSignatureResponse extends BaseAbstractSignatureResponse
{
    public function getCode(): ?string
    {
        return $this->data['errorCode'] ?? null;
    }
    public function getTransactionId(): ?string
    {
        return $this->data['orderId'] ?? null;
    }
    public function getTransactionReference(): ?string
    {
        return $this->data['transId'] ?? null;
    }
}