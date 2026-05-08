<?php



namespace LARAVEL\LARAVELGateway\Momo\Message;

use Omnipay\Common\Message\RequestInterface;
abstract class AbstractSignatureResponse extends AbstractResponse
{
    use Concerns\ResponseSignatureValidation;
    public function __construct(RequestInterface $request, $data)
    {
        parent::__construct($request, $data);
        if ('0' === $this->getCode()) {
            $this->validateSignature();
        }
    }
}