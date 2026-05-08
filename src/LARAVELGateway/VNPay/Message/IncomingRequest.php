<?php



namespace LARAVEL\LARAVELGateway\VNPay\Message;
use Omnipay\Common\Message\AbstractRequest;
use LARAVEL\LARAVELGateway\VNPay\Concerns\Parameters;
use LARAVEL\LARAVELGateway\VNPay\Concerns\ParametersNormalization;

class IncomingRequest extends AbstractRequest
{
    use Parameters;
    use ParametersNormalization;
    public function getData(): array
    {
        call_user_func_array(
            [$this, 'validate'],
            array_keys($parameters = $this->getIncomingParameters())
        );

        return $parameters;
    }
    public function sendData($data): SignatureResponse
    {
        return $this->response = new SignatureResponse($this, $data);
    }
    public function initialize(array $parameters = [])
    {
        parent::initialize(
            $this->normalizeParameters($parameters)
        );

        foreach ($this->getIncomingParameters() as $parameter => $value) {
            $this->setParameter($parameter, $value);
        }

        return $this;
    }
    protected function getIncomingParameters(): array
    {
        return $this->httpRequest->query->all();
    }
}