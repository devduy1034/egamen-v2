<?php


namespace LARAVEL\LARAVELGateway\VTCPay\Message\Concerns;

use LARAVEL\LARAVELGateway\VTCPay\Support\Signature;
use Omnipay\Common\Exception\InvalidResponseException;
trait ResponseSignatureValidation
{
    /**
     * Kiểm tra tính hợp lệ của dữ liệu do VTCPay phản hồi.
     *
     * @throws InvalidResponseException
     */
    protected function validateSignature(): void
    {
        $data = $dataSignature = $this->getData();

        if (! isset($data['signature'])) {
            throw new InvalidResponseException(sprintf('Response from VTCPay is invalid!'));
        }

        $signature = new Signature(
            $this->getRequest()->getSecurityCode()
        );

        unset($dataSignature['signature']);

        if (! $signature->validate($dataSignature, $data['signature'])) {
            throw new InvalidResponseException(sprintf('Data signature response from VTCPay is invalid!'));
        }
    }
}
