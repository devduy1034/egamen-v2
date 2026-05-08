<?php



namespace LARAVEL\LARAVELGateway\VNPay\Message;

class QueryTransactionRequest extends AbstractSignatureRequest
{
    protected $productionEndpoint = 'https://merchant.vnpay.vn/merchant_webapi/merchant.html';
    protected $testEndpoint = 'https://sandbox.vnpayment.vn/merchant_webapi/api/transaction';
    public function initialize(array $parameters = [])
    {

        parent::initialize($parameters);
        $this->setParameter('vnp_Command', 'querydr');
        $this->setParameter('vnp_TmnCode', $parameters['vnp_TmnCode']??config('gateways.gateways.VNPay.options.vnp_TmnCode'));
        $this->setParameter('vnp_RequestId', time());
        $this->setParameter('vnp_TransactionDate', $parameters['vnp_TransactionDate']??date('YmdHis'));
        return $this;
    }
    public function sendData($datas): SignatureResponse
    {
        $data = $this->getDataQueryTransaction($datas);

        $response = $this->httpClient->request('POST', $this->getEndpoint(),['Content-Type: application/json'],json_encode($data));
        $responseRawData = $response->getBody()->getContents();
        parse_str($responseRawData, $responseData);
        return $this->response = new SignatureResponse($this, $responseData);
    }

    public function getVnpTransactionNo(): ?string
    {
        return $this->getParameter('vnp_TransactionNo');
    }
    public function setVnpTransactionNo(?string $no)
    {
        return $this->setParameter('vnp_TransactionNo', $no);
    }
    public function getTransactionReference(): ?string
    {
        return $this->getParameter('vnp_TransactionNo');
    }
    public function setTransactionReference($value)
    {
        return $this->setParameter('vnp_TransactionNo', $value);
    }
    public function getVnpTransDate(): ?string
    {
        return $this->getParameter('vnp_TransDate');
    }
    public function setVnpTransDate(?string $date)
    {
        return $this->setParameter('vnp_TransDate', $date);
    }

    protected function getSignatureParameters(): array {
        $parameters = [
            'vnp_RequestId','vnp_Version', 'vnp_Command','vnp_TmnCode', 'vnp_TxnRef', 'vnp_TransactionDate',
            'vnp_CreateDate', 'vnp_IpAddr','vnp_OrderInfo'
        ];
        if ($this->getVnpTransactionNo()) {
            $parameters[] = 'vnp_TransactionNo';
        }

        return $parameters;
    }
}
