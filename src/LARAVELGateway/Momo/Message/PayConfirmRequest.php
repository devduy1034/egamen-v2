<?php



namespace LARAVEL\LARAVELGateway\Momo\Message;

class PayConfirmRequest extends AbstractSignatureRequest
{
    public function sendData($data): PayConfirmResponse
    {
        $response = $this->httpClient->request('POST', $this->getEndpoint().'/pay/confirm', [
            'Content-Type' => 'application/json; charset=utf-8',
        ], json_encode($data));
        $responseData = $response->getBody()->getContents();

        return $this->response = new PayConfirmResponse($this, json_decode($responseData, true) ?? []);
    }
    public function getRequestId(): ?string
    {
        return $this->getParameter('requestId');
    }
    public function setRequestId(?string $id)
    {
        return $this->setParameter('requestId', $id);
    }
    public function getPartnerRefId(): ?string
    {
        return $this->getParameter('partnerRefId');
    }
    public function setPartnerRefId(?string $id)
    {
        return $this->setParameter('partnerRefId', $id);
    }
    public function getMomoTransId(): ?string
    {
        return $this->getParameter('momoTransId');
    }
    public function setMomoTransId(?string $id)
    {
        return $this->setParameter('momoTransId', $id);
    }
    public function getCustomerNumber(): ?string
    {
        return $this->getParameter('customerNumber');
    }
    public function setCustomerNumber(?string $number)
    {
        return $this->setParameter('customerNumber', $number);
    }
    public function getRequestType(): ?string
    {
        return $this->getParameter('requestType');
    }
    public function setRequestType(?string $type)
    {
        return $this->setParameter('requestType', $type);
    }
    protected function getSignatureParameters(): array
    {
        return [
            'partnerCode', 'partnerRefId', 'requestType', 'requestId', 'momoTransId',
        ];
    }
}