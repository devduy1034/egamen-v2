<?php



namespace LARAVEL\LARAVELGateway\Momo\Message;

abstract class AbstractHashRequest extends AbstractRequest
{
    use Concerns\RequestHash;
    use Concerns\RequestEndpoint;
    public function getData(): array
    {
        $parameters = $this->getParameters();
        call_user_func_array([$this, 'validate'], $this->getHashParameters());
        $parameters['hash'] = $this->generateHash();
        unset($parameters['testMode'], $parameters['publicKey'], $parameters['secretKey']);
        return $parameters;
    }
}