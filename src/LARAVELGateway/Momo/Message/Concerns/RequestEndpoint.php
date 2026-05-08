<?php



namespace LARAVEL\LARAVELGateway\Momo\Message\Concerns;

trait RequestEndpoint
{
    protected function getEndpoint(): string
    {
        return $this->getTestMode() ? 'https://test-payment.momo.vn' : 'https://payment.momo.vn';
    }
}