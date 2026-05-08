<?php



namespace LARAVEL\LARAVELGateway\Momo\Message;

abstract class AbstractIncomingRequest extends AbstractRequest
{
    public function getData(): array
    {
        call_user_func_array(
            [$this, 'validate'],
            array_keys($parameters = $this->getIncomingParameters())
        );
        return $parameters;
    }
    public function initialize(array $parameters = [])
    {
        parent::initialize($parameters);

        foreach ($this->getIncomingParameters() as $parameter => $value) {
            $this->setParameter($parameter, $value);
        }
        return $this;
    }
    abstract protected function getIncomingParameters(): array;
}