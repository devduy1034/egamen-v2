<?php


namespace LARAVEL\LARAVELGateway\OnePay\Message;

use Omnipay\Common\Message\RedirectResponseInterface;
use Omnipay\Common\Message\RequestInterface;
class PurchaseResponse extends Response implements RedirectResponseInterface
{
    private $redirectUrl;
    public function __construct(RequestInterface $request, $data, $redirectUrl)
    {
        parent::__construct($request, $data);
        $this->redirectUrl = $redirectUrl;
    }
    public function isSuccessful(): bool
    {
        return false;
    }
    public function getRedirectUrl(): string
    {
        return $this->redirectUrl;
    }
    public function isRedirect(): bool
    {
        return true;
    }
}
