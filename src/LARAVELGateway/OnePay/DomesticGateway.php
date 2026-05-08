<?php


namespace LARAVEL\LARAVELGateway\OnePay;

use LARAVEL\LARAVELGateway\OnePay\Message\Domestic\PurchaseRequest;
use LARAVEL\LARAVELGateway\OnePay\Message\Domestic\QueryTransactionRequest;
use LARAVEL\LARAVELGateway\OnePay\Message\IncomingRequest;
class DomesticGateway extends AbstractGateway
{
    public function getName(): string
    {
        return 'OnePay Domestic';
    }
    /**
     *{@inheritdoc}
     * @return \Omnipay\Common\Message\AbstractRequest|PurchaseRequest
     */
    public function purchase(array $options = []): PurchaseRequest
    {
        return $this->createRequest(PurchaseRequest::class, $options);
    }
    /**
     *{@inheritdoc}
     * @return \Omnipay\Common\Message\AbstractRequest|IncomingRequest
     */
    public function completePurchase(array $options = []): IncomingRequest
    {
        return $this->createRequest(IncomingRequest::class, $options);
    }
    /**
     *{@inheritdoc}
     * @return \Omnipay\Common\Message\AbstractRequest|IncomingRequest
     */
    public function notification(array $options = []): IncomingRequest
    {
        return $this->createRequest(IncomingRequest::class, $options);
    }
    /**
     *{@inheritdoc}
     * @return \Omnipay\Common\Message\AbstractRequest|QueryTransactionRequest
     */
    public function queryTransaction(array $options = []): QueryTransactionRequest
    {
        return $this->createRequest(QueryTransactionRequest::class, $options);
    }
}
