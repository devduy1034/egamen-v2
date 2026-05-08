<?php



namespace LARAVEL\LARAVELGateway\VNPay\Message\Concerns;
use LARAVEL\LARAVELGateway\VNPay\Support\Signature;
use Omnipay\Common\Exception\InvalidResponseException;
trait ResponseSignatureValidation
{
    protected function validateSignature(): void
    {
        $data = $this->getData();

        if (! isset($data['vnp_SecureHash'])) {
            throw new InvalidResponseException('Response from VNPay is invalid!');
        }

        $dataSignature = array_filter($this->getData(), function ($parameter) {
            return 0 === strpos($parameter, 'vnp_')
                && 'vnp_SecureHash' !== $parameter
                && 'vnp_SecureHashType' !== $parameter;
        }, ARRAY_FILTER_USE_KEY);

        $signature = new Signature(
            $this->getRequest()->getVnpHashSecret(),
            $data['vnp_SecureHashType'] ?? 'sha512'
        );

        if (! $signature->validate($dataSignature, $data['vnp_SecureHash'])) {
            throw new InvalidResponseException(sprintf('Data signature response from VNPay is invalid!'));
        }
    }
}