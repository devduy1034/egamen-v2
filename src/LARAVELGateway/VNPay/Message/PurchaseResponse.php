<?php


namespace LARAVEL\LARAVELGateway\VNPay\Message;
use Omnipay\Common\Message\RedirectResponseInterface;
use Omnipay\Common\Message\RequestInterface;
class PurchaseResponse extends Response implements RedirectResponseInterface
{
    private $redirectUrl;
    public function __construct(RequestInterface $request, array $data, string $redirectUrl)
    {
        $this->redirectUrl = $redirectUrl;
        parent::__construct($request, $data);
    }
    public function isSuccessful(): bool{
        return false;
    }
    public function isRedirect(): bool{
        return true;
    }
    public function getRedirectUrl(): string{
        return $this->redirectUrl;
    }
}