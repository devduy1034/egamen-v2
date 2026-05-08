<?php



namespace LARAVEL\LARAVELGateway\Momo\Message\Concerns;
use LARAVEL\LARAVELGateway\MoMo\Support\Arr;
use LARAVEL\LARAVELGateway\MoMo\Support\Signature;
trait RequestSignature
{
    protected function generateSignature(): string
    {
        $data = [];
        $signature = new Signature(
            $this->getSecretKey()
        );
        $parameters = $this->getParameters();

        foreach ($this->getSignatureParameters() as $pos => $parameter) {
            if (! is_string($pos)) {
                $pos = $parameter;
            }

            $data[$pos] = Arr::getValue($parameter, $parameters);
        }

        return $signature->generate($data);
    }
    abstract protected function getSignatureParameters(): array;
}