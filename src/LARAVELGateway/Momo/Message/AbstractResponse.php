<?php



namespace LARAVEL\LARAVELGateway\Momo\Message;
use Omnipay\Common\Message\AbstractResponse as BaseAbstractResponse;

abstract class AbstractResponse extends BaseAbstractResponse
{
    use Concerns\ResponseProperties;
    public function isSuccessful(): bool
    {
        return '0' === $this->getCode();
    }
    public function isCancelled(): bool
    {
        return '49' === $this->getCode();
    }
    public function getMessage(): ?string
    {
        return $this->data['message'] ?? null;
    }
}