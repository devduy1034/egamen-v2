<?php



namespace LARAVEL\LARAVELGateway\VNPay\Message\Concerns;

use LARAVEL\LARAVELGateway\VNPay\Support\Signature;

trait RequestSignature
{
    protected function generateSignature(string $hashType = 'sha512'): string
    {
        $data = [];
        $signature = new Signature(
            $this->getVnpHashSecret(),
            $hashType
        );
        foreach ($this->getSignatureParameters() as $parameter) {
            $data[$parameter] = $this->getParameter($parameter);
        }

        return $signature->generate($data);
    }
    protected function generateSignatureQueryTransaction(string $hashType = 'sha512'): string
    {
        $data = [];
        $signature = new Signature(
            $this->getVnpHashSecret(),
            $hashType
        );
        foreach ($this->getSignatureParameters() as $parameter) {
            $data[$parameter] = $this->getParameter($parameter);
        }
        return $signature->generateQueryTransaction($data);
    }
    abstract protected function getSignatureParameters(): array;
}