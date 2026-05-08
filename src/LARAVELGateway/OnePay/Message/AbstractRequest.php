<?php


namespace LARAVEL\LARAVELGateway\OnePay\Message;

use Omnipay\Common\Message\AbstractRequest as BaseAbstractRequest;
use LARAVEL\LARAVELGateway\OnePay\Concerns\Parameters;
use LARAVEL\LARAVELGateway\OnePay\Concerns\ParametersNormalization;

abstract class AbstractRequest extends BaseAbstractRequest
{
    use Parameters;
    use ParametersNormalization;

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
