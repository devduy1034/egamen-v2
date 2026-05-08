<?php



namespace LARAVEL\LARAVELGateway\Momo\Message;

class PayConfirmResponse extends AbstractSignatureResponse
{
    protected function getSignatureParameters(): array
    {
        return [
            'amount' => 'data.amount',
            'momoTransId' => 'data.momoTransId',
            'partnerCode' => 'data.partnerCode',
            'partnerRefId' => 'data.partnerRefId',
        ];
    }
    public function getCode(): ?string
    {
        return $this->data['status'] ?? null;
    }
    public function getTransactionReference(): ?string
    {
        return $this->data['data']['momoTransId'] ?? null;
    }
    public function getTransactionId(): ?string
    {
        return $this->data['data']['partnerRefId'] ?? null;
    }
}