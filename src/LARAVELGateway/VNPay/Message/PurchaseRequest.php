<?php



namespace LARAVEL\LARAVELGateway\VNPay\Message;

class PurchaseRequest extends AbstractSignatureRequest
{

    protected $productionEndpoint = 'https://pay.vnpay.vn/vpcpay.html';
    protected $testEndpoint = 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html';
    public function initialize(array $parameters = [])
    {
        parent::initialize($parameters);
        $this->setParameter('vnp_Command', 'pay');
        $this->setVnpLocale(
            $this->getVnpLocale() ?? 'vn'
        );
        $this->setVnpCurrCode(
            $this->getVnpCurrCode() ?? 'VND'
        );

        return $this;
    }
    public function sendData($data): PurchaseResponse
    {
        $query = http_build_query($data);
        $redirectUrl = $this->getEndpoint().'?'.$query;
        return $this->response = new PurchaseResponse($this, $data, $redirectUrl);
    }
    public function getVnpLocale(): ?string {
        return $this->getParameter('vnp_Locale');
    }
    public function setVnpLocale(?string $locale): PurchaseRequest
    {
        return $this->setParameter('vnp_Locale', $locale);
    }
    public function getVnpCurrCode(): ?string
    {
        return $this->getCurrency();
    }
    public function setVnpCurrCode(?string $code) {
        return $this->setCurrency($code);
    }
    public function getCurrency(): ?string
    {
        return $this->getParameter('vnp_CurrCode');
    }

    public function setCurrency($value)
    {
        return $this->setParameter('vnp_CurrCode', $value);
    }

    public function getVnpBankCode(): ?string
    {
        return $this->getParameter('vnp_BankCode');
    }

    public function setVnpBankCode(?string $code)
    {
        return $this->setParameter('vnp_BankCode', $code);
    }

    public function getVnpOrderType(): ?string
    {
        return $this->getParameter('vnp_OrderType');
    }
    public function setVnpOrderType(?string $type)
    {
        return $this->setParameter('vnp_OrderType', $type);
    }

    public function getVnpAmount(): ?string
    {
        return $this->getAmount();
    }
    public function setVnpAmount(?string $number)
    {
        return $this->setAmount($number);
    }
    public function getAmount(): ?string
    {
        return $this->getParameter('vnp_Amount');
    }
    public function setAmount($value)
    {
        return $this->setParameter('vnp_Amount', $value);
    }
    public function getVnpReturnUrl(): ?string
    {
        return $this->getReturnUrl();
    }

    public function setVnpReturnUrl(?string $url)
    {
        return $this->setReturnUrl($url);
    }
    public function getReturnUrl(): ?string
    {
        return $this->getParameter('vnp_ReturnUrl');
    }
    public function setReturnUrl($value)
    {
        return $this->setParameter('vnp_ReturnUrl', $value);
    }
    protected function getSignatureParameters(): array
    {
        $parameters = [
            'vnp_CreateDate', 'vnp_IpAddr', 'vnp_ReturnUrl', 'vnp_Amount', 'vnp_OrderType', 'vnp_OrderInfo',
            'vnp_TxnRef', 'vnp_CurrCode', 'vnp_Locale', 'vnp_TmnCode', 'vnp_Command', 'vnp_Version',
        ];
        if ($this->getVnpBankCode()) {
            $parameters[] = 'vnp_BankCode';
        }
        return $parameters;
    }
}
