<?php


namespace LARAVEL\LARAVELGateway\VTCPay\Message;
class CompletePurchaseRequest extends AbstractIncomingRequest
{
    /**
     * {@inheritdoc}
     * @throws \Omnipay\Common\Exception\InvalidRequestException
     */
    public function getData(): array
    {
        $this->validate('amount', 'reference_number', 'status', 'website_id');

        return parent::getData();
    }

    /**
     * {@inheritdoc}
     */
    protected function getIncomingParameters(): array
    {
        return $this->httpRequest->query->all();
    }

    protected function getSignatureParameters(): array
    {
        // TODO: Implement getSignatureParameters() method.
    }
}
