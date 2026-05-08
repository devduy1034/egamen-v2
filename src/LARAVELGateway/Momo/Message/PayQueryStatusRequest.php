<?php



namespace LARAVEL\LARAVELGateway\Momo\Message;

class PayQueryStatusRequest extends AbstractHashRequest
{
    public function initialize(array $parameters = [])
    {
        parent::initialize($parameters);
        $this->setParameter('version', 2);

        return $this;
    }
    public function sendData($data): PayQueryStatusResponse
    {
        $response = $this->httpClient->request('POST', $this->getEndpoint().'/pay/query-status', [
            'Content-Type' => 'application/json; charset=utf-8',
        ], json_encode($data));
        $responseData = $response->getBody()->getContents();

        return $this->response = new PayQueryStatusResponse($this, json_decode($responseData, true) ?? []);
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
    protected function getHashParameters(): array
    {
        $parameters = [
            'requestId', 'partnerCode', 'partnerRefId',
        ];

        if ($this->getParameter('momoTransId')) {
            $parameters[] = 'momoTransId';
        }

        return $parameters;
    }
}