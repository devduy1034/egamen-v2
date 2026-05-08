<?php



namespace LARAVEL\LARAVELGateway\VNPay\Message;

class RefundRequest extends AbstractSignatureRequest
{
    protected $productionEndpoint = 'https://merchant.vnpay.vn/merchant_webapi/merchant.html';
    protected $testEndpoint = 'https://sandbox.vnpayment.vn/merchant_webapi/merchant.html';
    public function initialize(array $parameters = [])
    {
        parent::initialize($parameters);
        $this->setParameter('vnp_Command', 'refund');
        return $this;
    }
    public function sendData($data): SignatureResponse
    {
        $query = http_build_query($data, null, '&', PHP_QUERY_RFC3986);
        $requestUrl = $this->getEndpoint().'?'.$query;
        $response = $this->httpClient->request('GET', $requestUrl);
        $responseRawData = $response->getBody()->getContents();
        parse_str($responseRawData, $responseData);

        return $this->response = new SignatureResponse($this, $responseData);
    }
    public function getVnpAmount(): ?string
    {
        return $this->getAmount();
    }
    public function setVnpAmount(?string $number)
    {
        return $this->setAmount($number);
    }
    public function getVnpTransactionType(): ?string
    {
        return $this->getParameter('vnp_TransactionType');
    }
    public function setVnpTransactionType(?string $type)
    {
        return $this->setParameter('vnp_TransactionType', $type);
    }
    public function getAmount(): ?string
    {
        return $this->getParameter('vnp_Amount');
    }
    public function setAmount($value)
    {
        return $this->setParameter('vnp_Amount', $value);
    }
    public function getVnpTransDate(): ?string
    {
        return $this->getParameter('vnp_TransDate');
    }
    public function setVnpTransDate(?string $date)
    {
        return $this->setParameter('vnp_TransDate', $date);
    }
    protected function getSignatureParameters(): array
    {
        return [
            'vnp_Version', 'vnp_Command', 'vnp_TmnCode', 'vnp_TxnRef', 'vnp_OrderInfo', 'vnp_Amount',
            'vnp_TransDate', 'vnp_TransactionType',
        ];
    }
}