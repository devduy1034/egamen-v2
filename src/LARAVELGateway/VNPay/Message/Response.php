<?php



namespace LARAVEL\LARAVELGateway\VNPay\Message;
use Omnipay\Common\Message\AbstractResponse;

class Response extends AbstractResponse
{
    use Concerns\ResponseProperties;
    public function isSuccessful(): bool
    {
        return '00' === $this->getCode();
    }
    public function isCancelled(): bool
    {
        return '24' === $this->getCode();
    }
    public function getCode(): ?string
    {
        return $this->data['vnp_ResponseCode'] ?? null;
    }
    public function getTransactionId(): ?string
    {
        return $this->data['vnp_TxnRef'] ?? null;
    }
    public function getTransactionReference(): ?string
    {
        return $this->data['vnp_TransactionNo'] ?? null;
    }
    public function getMessage(): ?string
    {
        return $this->data['vnp_Message'] ?? null;
    }
}