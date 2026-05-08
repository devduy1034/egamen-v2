<?php


namespace LARAVEL\LARAVELGateway\VTCPay\Message;
use LARAVEL\LARAVELGateway\VTCPay\Concerns\Parameters;
use LARAVEL\LARAVELGateway\VTCPay\Concerns\ParametersNormalize;
use Omnipay\Common\Message\AbstractRequest as BaseAbstractRequest;

abstract class AbstractRequest extends BaseAbstractRequest
{
    use Parameters;
    use ParametersNormalize;
    use Concerns\RequestEndpoint;
    use Concerns\RequestSignature;
    /**
     * {@inheritdoc}
     */
    public function initialize(array $parameters = [])
    {
        return parent::initialize(
            $this->normalizeParameters($parameters)
        );
    }
}
