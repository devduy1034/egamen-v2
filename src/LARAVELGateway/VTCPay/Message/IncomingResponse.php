<?php


namespace LARAVEL\LARAVELGateway\VTCPay\Message;

use Omnipay\Common\Message\RequestInterface;
class IncomingResponse extends Response
{
    use Concerns\ResponseSignatureValidation;

    /**
     * {@inheritdoc}
     * @throws \Omnipay\Common\Exception\InvalidResponseException
     */
    public function __construct(RequestInterface $request, $data)
    {
        parent::__construct($request, $data);

        $this->validateSignature();
    }
}
