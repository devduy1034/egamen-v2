<?php


namespace LARAVEL\LARAVELGateway\VTCPay\Message\Concerns;

use LARAVEL\LARAVELGateway\VTCPay\Support\Signature;
trait RequestSignature
{
    /**
     * Trả về chữ ký điện tử gửi đến VTCPay dựa theo [[getSignatureParameters()]].
     *
     * @return string
     */
    protected function generateSignature(): string
    {
        $data = [];
        $signature = new Signature(
            $this->getSecurityCode()
        );

        foreach ($this->getSignatureParameters() as $parameter) {
            $data[$parameter] = $this->getParameter($parameter);
        }

        return $signature->generate($data);
    }

    abstract protected function getSignatureParameters(): array;
}
