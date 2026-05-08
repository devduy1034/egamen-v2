<?php



namespace LARAVEL\LARAVELGateway\Momo\Message;

class PayQueryStatusResponse extends AbstractResponse
{
    public function getCode(): ?string
    {
        return $this->data['status'] ?? null;
    }
    public function getTransactionId(): ?string
    {
        return $this->data['data']['billId'] ?? null;
    }
    public function getTransactionReference(): ?string
    {
        return $this->data['data']['transid'] ?? null;
    }
}