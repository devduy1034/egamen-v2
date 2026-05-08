<?php


namespace LARAVEL\LARAVELGateway\OnePay;

use Omnipay\Common\AbstractGateway as BaseAbstractGateway;
abstract class AbstractGateway extends BaseAbstractGateway
{
    use Concerns\Parameters;
    use Concerns\ParametersNormalization;
    public function initialize(array $parameters = [])
    {
        return parent::initialize(
            $this->normalizeParameters($parameters)
        );
    }
    public function getDefaultParameters(): array
    {
        return [
            'vpc_Version' => 2,
        ];
    }
}
