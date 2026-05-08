<?php



namespace LARAVEL\LARAVELGateway\Momo\Message;

class PayRefundResponse extends AbstractResponse
{
    public function getCode(): ?string
    {
        return $this->data['status'] ?? null;
    }
    public function getTransactionId(): ?string
    {
        return $this->data['partnerRefId'] ?? null;
    }
    public function getTransactionReference(): ?string
    {
        return $this->data['transid'] ?? null;
    }
}
