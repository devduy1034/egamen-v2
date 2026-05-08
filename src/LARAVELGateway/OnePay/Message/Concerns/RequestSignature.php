<?php


namespace LARAVEL\LARAVELGateway\OnePay\Message\Concerns;

use LARAVEL\LARAVELGateway\OnePay\Support\Signature;

trait RequestSignature
{
    protected function generateSignature(): string
    {
        $data = [];
        $signature = new Signature(
            $this->getVpcHashKey()
        );

        foreach ($this->getSignatureParameters() as $parameter) {
            $data[$parameter] = $this->getParameter($parameter);
        }

        return $signature->generate($data);
    }
    abstract protected function getSignatureParameters(): array;
}
