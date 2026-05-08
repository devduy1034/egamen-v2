<?php



namespace LARAVEL\LARAVELGateway\Momo\Message\Concerns;
use LARAVEL\LARAVELGateway\MoMo\Support\Arr;
use LARAVEL\LARAVELGateway\MoMo\Support\RSAEncrypt;

trait RequestHash
{
    protected function generateHash(): string
    {
        $data = [];
        $rsa = new RSAEncrypt(
            $this->getPublicKey()
        );
        $parameters = $this->getParameters();

        foreach ($this->getHashParameters() as $pos => $parameter) {
            if (! is_string($pos)) {
                $pos = $parameter;
            }

            $data[$pos] = Arr::getValue($parameter, $parameters);
        }

        return $rsa->encrypt($data);
    }
    abstract protected function getHashParameters(): array;
}