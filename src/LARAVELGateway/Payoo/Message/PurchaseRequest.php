<?php


namespace LARAVEL\LARAVELGateway\Payoo\Message;
use Carbon\Carbon;
use Omnipay\Common\Exception\InvalidRequestException;

class PurchaseRequest extends AbstractRequest
{
    const RULE_DES_MIN_LENGTH = 50;

    const TIME_ZONE = 'Asia/Ho_Chi_Minh';

    /**
     * @throws InvalidRequestException
     */
    public function getData()
    {
        $this->validate(
            'apiUsername',
            'secretKey',
            'shopId',
            'shopTitle',
            'shopDomain',
            'transactionId',
            'refer',
            'amount',
            'description',
            'returnUrl'
        );
        $this->guardDescription();
        $orderXml = $this->buildOrderXml();
        $secretKey = $this->getSecretKey();
        return [
            'data' => $orderXml,
            'refer' => $this->getShopDomain(),
            'checksum' => hash('sha512', $secretKey . $orderXml),
        ];
    }

    public function sendData($data)
    {
        return new PurchaseResponse($this, $data);
    }

    protected function buildOrderXml()
    {
        $validityTime = Carbon::now(self::TIME_ZONE)->addDay()->format('YmdHis');

        return '<shops><shop>' .
            '<session>' . $this->getTransactionId() . '</session>' .
            '<username>' . $this->getApiUserName() . '</username>' .
            '<shop_id>' . $this->getShopId() . '</shop_id>' .
            '<shop_title>' . $this->getShopTitle() . '</shop_title>' .
            '<shop_domain>' . $this->getShopDomain() . '</shop_domain>' .
            '<shop_back_url>' . $this->getReturnUrl() . '</shop_back_url>' .
            '<order_no>' . $this->getTransactionId() . '</order_no>' .
            '<order_cash_amount>' . $this->getAmountInteger() . '</order_cash_amount>' .
            '<order_description>' . urlencode($this->getDescription()) . '</order_description>' .
            '<validity_time>' . $validityTime . '</validity_time>' .
            '<notify_url>' . $this->getNotifyUrl() . '</notify_url>' .
            '<customer>' .
            '<name>' . $this->getCustomerName() . '</name>' .
            '<phone>' . $this->getCustomerPhone() . '</phone>' .
            '<address>' . $this->getCustomerAddress() . '</address>' .
            '<email>' . $this->getCustomerEmail() . '</email>' .
            '</customer>' .
            '<customer_identifier></customer_identifier>' .
            '<jsonresponse>TRUE</jsonresponse>' .
            '<count_down></count_down>' .
            '<direct_return_time></direct_return_time>'.
            '</shop></shops>';
    }

    private function guardDescription()
    {
        if (strlen($this->getDescription()) <= self::RULE_DES_MIN_LENGTH) {
            throw new InvalidRequestException("The description parameter must be larger than 50 characters");
        }
    }

    public function getCustomerName(){
        return $this->getParameter('customerName');
    }
    public function setCustomerName($customerName)
    {
        return $this->setParameter('customerName',$customerName);
    }
    public function getCustomerPhone(){
        return $this->getParameter('customerPhone');
    }
    public function setCustomerPhone($customerPhone){
        return $this->setParameter('customerPhone',$customerPhone);
    }
    public function getCustomerEmail(){
        return $this->getParameter('customerEmail');
    }
    public function setCustomerEmail($customerEmail){
        return $this->setParameter('customerEmail',$customerEmail);
    }
    public function getCustomerAddress(){
        return $this->getParameter('customerAddress');
    }
    public function setCustomerAddress($customerAddress){
        return $this->setParameter('customerAddress',$customerAddress);
    }

}