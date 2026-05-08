<?php



namespace LARAVEL\LARAVELGateway\VNPay\Concerns;

trait Parameters
{
    public function getVnpVersion(): ?string
    {
        return $this->getParameter('vnp_Version');
    }
    public function setVnpVersion(?string $code)
    {
        return $this->setParameter('vnp_Version', $code);
    }
    public function getVnpTmnCode(): ?string
    {
        return $this->getParameter('vnp_TmnCode');
    }
    public function setVnpTmnCode(?string $code)
    {
        return $this->setParameter('vnp_TmnCode', $code);
    }
    public function getVnpHashSecret(): ?string
    {
        return $this->getParameter('vnp_HashSecret');
    }
    public function setVnpHashSecret(?string $secret)
    {
        return $this->setParameter('vnp_HashSecret', $secret);
    }
}