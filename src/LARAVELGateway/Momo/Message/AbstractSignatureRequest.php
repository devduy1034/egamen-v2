<?php



namespace LARAVEL\LARAVELGateway\Momo\Message;

abstract class AbstractSignatureRequest extends AbstractRequest
{
    use Concerns\RequestEndpoint;
    use Concerns\RequestSignature;
    public function getData(): array
    {
        $parameters = $this->getParameters();
        call_user_func_array(
            [$this, 'validate'],
            $this->getSignatureParameters()
        );
        $parameters['signature'] = $this->generateSignature();
        unset($parameters['secretKey'], $parameters['testMode'], $parameters['publicKey']);

        return $parameters;
    }
}
