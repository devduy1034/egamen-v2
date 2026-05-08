<?php


namespace LARAVEL\LARAVELGateway\VTCPay;

use Omnipay\Common\AbstractGateway;
use LARAVEL\LARAVELGateway\VTCPay\Message\PurchaseRequest;
use LARAVEL\LARAVELGateway\VTCPay\Message\NotificationRequest;
use LARAVEL\LARAVELGateway\VTCPay\Message\CompletePurchaseRequest;

class Gateway extends AbstractGateway
{
    use Concerns\Parameters;
    use Concerns\ParametersNormalize;

    public function getName(): string
    {
        return 'VTCPay';
    }
    public function initialize(array $parameters = []): Gateway
    {
        return parent::initialize(
            $this->normalizeParameters($parameters)
        );
    }

    /**
     * {@inheritdoc}
     * @return \Omnipay\Common\Message\AbstractRequest|PurchaseRequest
     */
    public function purchase(array $options = []): PurchaseRequest
    {
        return $this->createRequest(PurchaseRequest::class, $options);
    }

    /**
     * {@inheritdoc}
     * @return \Omnipay\Common\Message\AbstractRequest|CompletePurchaseRequest
     */
    public function completePurchase(array $options = []): CompletePurchaseRequest
    {
        return $this->createRequest(CompletePurchaseRequest::class, $options);
    }

    /**
     * Khởi tạo IPN request tiếp nhận từ VTCPay gửi sang.
     *
     * @return \Omnipay\Common\Message\AbstractRequest|NotificationRequest
     */
    public function notification(array $options = []): NotificationRequest
    {
        return $this->createRequest(NotificationRequest::class, $options);
    }
}
