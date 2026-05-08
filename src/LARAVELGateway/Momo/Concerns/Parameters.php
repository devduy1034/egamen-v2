<?php



namespace LARAVEL\LARAVELGateway\Momo\Concerns;

trait Parameters
{
    public function getAccessKey(): ?string
    {
        return $this->getParameter('accessKey');
    }
    public function setAccessKey(?string $key)
    {
        return $this->setParameter('accessKey', $key);
    }
    public function getSecretKey(): ?string
    {
        return $this->getParameter('secretKey');
    }
    public function setSecretKey(?string $key)
    {
        return $this->setParameter('secretKey', $key);
    }
    public function getPartnerCode(): ?string
    {
        return $this->getParameter('partnerCode');
    }
    public function setPartnerCode(?string $code)
    {
        return $this->setParameter('partnerCode', $code);
    }
    public function getPublicKey(): ?string
    {
        return $this->getParameter('publicKey');
    }
    public function setPublicKey(?string $key)
    {
        return $this->setParameter('publicKey', $key);
    }
}