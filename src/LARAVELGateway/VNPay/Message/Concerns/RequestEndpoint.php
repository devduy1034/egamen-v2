<?php



namespace LARAVEL\LARAVELGateway\VNPay\Message\Concerns;

trait RequestEndpoint
{
    protected $productionEndpoint;
    protected $testEndpoint;
    protected function getEndpoint(): string
    {
        return $this->getTestMode() ? $this->testEndpoint : $this->productionEndpoint;
    }
}