<?php



namespace LARAVEL\LARAVELGateway\Momo\Support;

class Signature
{
    protected $secretKey;
    public function __construct(string $secretKey)
    {
        $this->secretKey = $secretKey;
    }
    public function generate(array $data): string
    {
        $data = urldecode(http_build_query($data));
        return hash_hmac('sha256', $data, $this->secretKey);
    }
    public function validate(array $data, string $expect): bool
    {
        $actual = $this->generate($data);

        return 0 === strcasecmp($expect, $actual);
    }
}