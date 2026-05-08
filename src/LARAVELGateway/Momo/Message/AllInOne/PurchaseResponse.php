<?php



namespace LARAVEL\LARAVELGateway\Momo\Message\AllInOne;
use Omnipay\Common\Message\RedirectResponseInterface;
class PurchaseResponse extends AbstractSignatureResponse implements RedirectResponseInterface
{
    public function isSuccessful(): bool
    {
        return false;
    }
    public function isRedirect(): bool
    {
        return isset($this->data['payUrl']);
    }
    public function getRedirectUrl(): string
    {
        return $this->data['payUrl'];
    }
    protected function getSignatureParameters(): array
    {
        return [
            'requestId', 'orderId', 'message', 'localMessage', 'payUrl', 'errorCode', 'requestType',
        ];
    }
}