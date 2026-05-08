<?php


namespace LARAVEL\LARAVELGateway\VTCPay\Support;
class Signature
{
    /**
     * Khóa bí mật dùng để tạo và kiểm tra chữ ký dữ liệu.
     *
     * @var string
     */
    protected $securityCode;

    /**
     * Khởi tạo đối tượng Signature.
     *
     * @param  string  $securityCode
     */
    public function __construct(string $securityCode)
    {
        $this->securityCode = $securityCode;
    }

    /**
     * Trả về chữ ký dữ liệu của dữ liệu truyền vào.
     *
     * @param  array  $data
     * @return string
     */
    public function generate(array $data): string
    {
        ksort($data);
        $dataSign = implode('|', $data).'|'.$this->securityCode;

        return strtoupper(hash('sha256', $dataSign));
    }

    /**
     * Kiểm tra tính hợp lệ của chữ ký dữ liệu so với dữ liệu truyền vào.
     *
     * @param  array  $data
     * @param  string  $expect
     * @return bool
     */
    public function validate(array $data, string $expect): bool
    {
        $actual = $this->generate($data);

        return 0 === strcasecmp($expect, $actual);
    }
}
