<?php


namespace LARAVEL\LARAVELGateway\VNPay\Message;
use Omnipay\Common\Message\RequestInterface;
class SignatureResponse extends Response
{
    use Concerns\ResponseSignatureValidation;
    public function __construct(RequestInterface $request, $data)
    {
        parent::__construct($request, $data);

        if ($this->isSuccessful()) {
            $this->validateSignature();
        }
    }
}