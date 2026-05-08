<?php



namespace LARAVEL\LARAVELGateway\Momo\Message\Concerns;
use LARAVEL\LARAVELGateway\MoMo\Support\Arr;
use LARAVEL\LARAVELGateway\MoMo\Support\Signature;
use Omnipay\Common\Exception\InvalidResponseException;
trait ResponseSignatureValidation
{
    protected function validateSignature(): void
    {
        $data = $this->getData();
        if (! isset($data['signature'])) {
            throw new InvalidResponseException(sprintf('Response from MoMo is invalid!'));
        }
        $dataSignature = [];
        $signature = new Signature(
            $this->getRequest()->getSecretKey()
        );
        foreach ($this->getSignatureParameters() as $pos => $parameter) {
            if (! is_string($pos)) {
                $pos = $parameter;
            }
            $dataSignature[$pos] = Arr::getValue($parameter, $data);
        }
        if (! $signature->validate($dataSignature, $data['signature'])) {
            throw new InvalidResponseException(sprintf('Data signature response from MoMo is invalid!'));
        }
    }
    abstract protected function getSignatureParameters(): array;
}