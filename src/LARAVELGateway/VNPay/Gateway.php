<?php


namespace LARAVEL\LARAVELGateway\VNPay;
use LARAVEL\LARAVELGateway\VNPay\Message\IncomingRequest;
use LARAVEL\LARAVELGateway\VNPay\Message\PurchaseRequest;
use LARAVEL\LARAVELGateway\VNPay\Message\QueryTransactionRequest;
use LARAVEL\LARAVELGateway\VNPay\Message\RefundRequest;
use Omnipay\Common\AbstractGateway;
class Gateway extends AbstractGateway
{
    use Concerns\Parameters;
    use Concerns\ParametersNormalization;

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'VNPay';
    }

    /**
     * {@inheritdoc}
     */
    public function initialize(array $parameters = []): AbstractGateway|Gateway
    {
        return parent::initialize(
            $this->normalizeParameters($parameters)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultParameters(): array
    {
        return [
            'vnp_Version' => config('gateways.gateways.VNPay.vnp_Version'),
        ];
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
     * @return \Omnipay\Common\Message\AbstractRequest|IncomingRequest
     */
    public function completePurchase(array $options = []): IncomingRequest
    {
        return $this->createRequest(IncomingRequest::class, $options);
    }

    /**
     * Tạo yêu cầu xác minh IPN gửi từ VNPay.
     *
     * @param  array  $options
     * @return \Omnipay\Common\Message\AbstractRequest|IncomingRequest
     */
    public function notification(array $options = []): IncomingRequest
    {
        return $this->createRequest(IncomingRequest::class, $options);
    }

    /**
     * Tạo yêu cầu truy vấn thông tin giao dịch đến VNPay.
     *
     * @param  array  $options
     * @return \Omnipay\Common\Message\AbstractRequest|QueryTransactionRequest
     */
    public function queryTransaction(array $options = []): QueryTransactionRequest
    {
        return $this->createRequest(QueryTransactionRequest::class, $options);
    }

    /**
     * {@inheritdoc}
     * @return \Omnipay\Common\Message\AbstractRequest|RefundRequest
     */
    public function refund(array $options = []): RefundRequest
    {
        return $this->createRequest(RefundRequest::class, $options);
    }
}
