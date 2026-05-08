<?php


namespace LARAVEL\LARAVELGateway\OnePay\Message;
abstract class AbstractSignatureRequest extends AbstractRequest
{
    use Concerns\RequestEndpoint;
    use Concerns\RequestSignature;

    /**
     * {@inheritdoc}
     */
    public function getData(): array
    {
        $parameters = $this->getParameters();
        call_user_func_array(
            [$this, 'validate'],
            $this->getSignatureParameters()
        );
        $parameters['vpc_SecureHash'] = $this->generateSignature();
        unset($parameters['vpc_HashKey'], $parameters['testMode']);

        return $parameters;
    }
}
