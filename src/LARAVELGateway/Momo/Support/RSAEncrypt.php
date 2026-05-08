<?php



namespace LARAVEL\LARAVELGateway\Momo\Support;

class RSAEncrypt
{
    protected $publicKey;
    public function __construct(string $publicKey)
    {
        $this->publicKey = $publicKey;
    }
    public function encrypt(array $data): string
    {
        $data = json_encode($data);
        openssl_public_encrypt($data, $dataEncrypted, $this->publicKey, OPENSSL_PKCS1_PADDING);
        return base64_encode($dataEncrypted);
    }
}