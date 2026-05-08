<?php


namespace LARAVEL\LARAVELGateway\VNPay\Support;
use InvalidArgumentException;

class Signature
{
    protected $hashSecret;
    protected $hashType;
    public function __construct(string $hashSecret, string $hashType = 'sha256')
    {
        if (! $this->isSupportHashType($hashType)) {
            throw new InvalidArgumentException(sprintf('Hash type: `%s` is not supported by VNPay', $hashType));
        }
        $this->hashType = $hashType;
        $this->hashSecret = $hashSecret;
    }
    public function generate(array $data): string
    {
        unset($data['vnp_SecureHashType']);
        ksort($data);
        $i = 0;
        $dataSign = "";
        foreach ($data as $key => $value) {
            if ($i == 1) {
                $dataSign .= '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $dataSign .= urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
        }
        return hash_hmac($this->hashType, $dataSign, $this->hashSecret);
    }
    public function generateQueryTransaction(array $data): string
    {
        unset($data['vnp_SecureHashType']);
        ksort($data);
        $format = '%s|%s|%s|%s|%s|%s|%s|%s|%s';
        $dataHash = sprintf(
            $format,
            $data['vnp_RequestId'],
            $data['vnp_Version'],
            $data['vnp_Command'],
            $data['vnp_TmnCode'],
            $data['vnp_TxnRef'],
            $data['vnp_TransactionDate'],
            $data['vnp_CreateDate'],
            $data['vnp_IpAddr'],
            $data['vnp_OrderInfo']
        );
        return hash_hmac($this->hashType, $dataHash, $this->hashSecret);
    }
    public function validate(array $data, string $expect): bool
    {
        $actual = $this->generate($data);
        return 0 === strcasecmp($expect, $actual);
    }
    protected function isSupportHashType(string $type): bool
    {
        return 0 === strcasecmp($type, 'md5') || 0 === strcasecmp($type, 'sha512');
    }
}