<?php


namespace LARAVEL\LARAVELGateway\VNPay\Message;
use Omnipay\Common\Message\AbstractRequest;
use LARAVEL\LARAVELGateway\VNPay\Concerns\Parameters;
use LARAVEL\LARAVELGateway\VNPay\Concerns\ParametersNormalization;
abstract class AbstractSignatureRequest extends AbstractRequest
{
    use Parameters;
    use ParametersNormalization;
    use Concerns\RequestEndpoint;
    use Concerns\RequestSignature;

    public function initialize(array $parameters = [])
    {

        parent::initialize(
            $this->normalizeParameters($parameters)
        );
        $this->setVnpIpAddr(
            $this->getVnpIpAddr() ?? $this->httpRequest->getClientIp()
        );
        $this->setVnpCreateDate(
            $this->getVnpCreateDate() ?? date('YmdHis')
        );

        return $this;
    }

    public function getData(): array
    {
        call_user_func_array(
            [$this, 'validate'],
            $this->getSignatureParameters()
        );
        $parameters = $this->getParameters();
        $parameters['vnp_SecureHashType'] = strtoupper((string) ($this->getSecureHashType() ?? 'sha512'));
        $parameters['vnp_SecureHash'] = $this->generateSignature(
            (string) $parameters['vnp_SecureHashType']
        );

        unset($parameters['vnp_HashSecret'], $parameters['testMode']);
        return $parameters;
    }
    public function getDataQueryTransaction(): array
    {
        $parameters = $this->getParameters();
        $parameters['vnp_SecureHashType'] = strtoupper((string) ($this->getSecureHashType() ?? 'sha512'));
        $parameters['vnp_SecureHash'] = $this->generateSignatureQueryTransaction(
            (string) $parameters['vnp_SecureHashType']
        );
        unset($parameters['vnp_HashSecret'], $parameters['testMode']);
        return $parameters;
    }
    public function getVnpTxnRef(): ?string
    {
        return $this->getTransactionId();
    }
    public function setVnpTxnRef(?string $ref)
    {
        return $this->setTransactionId($ref);
    }
    public function getTransactionId(): ?string
    {
        return $this->getParameter('vnp_TxnRef');
    }
    public function setTransactionId($value)
    {
        return $this->setParameter('vnp_TxnRef', $value);
    }
    public function getVnpOrderInfo(): ?string
    {
        return $this->getParameter('vnp_OrderInfo');
    }
    public function setVnpOrderInfo(?string $info)
    {
        return $this->setParameter('vnp_OrderInfo', $info);
    }
    public function getVnpCreateDate(): ?string
    {
        return $this->getParameter('vnp_CreateDate');
    }
    public function setVnpCreateDate(?string $date)
    {
        return $this->setParameter('vnp_CreateDate', $date);
    }
    public function getVnpIpAddr(): ?string
    {
        return $this->getClientIp();
    }
    public function setVnpIpAddr(?string $ip)
    {
        return $this->setClientIp($ip);
    }
    public function getClientIp(): ?string
    {
        return $this->getParameter('vnp_IpAddr');
    }
    public function setClientIp($value)
    {
        return $this->setParameter('vnp_IpAddr', $value);
    }
    public function getSecureHashType(): ?string
    {
        return $this->getParameter('vnp_SecureHashType');
    }
    public function setSecureHashType(?string $secureHashType)
    {
        return $this->setParameter('vnp_SecureHashType', $secureHashType);
    }
}
