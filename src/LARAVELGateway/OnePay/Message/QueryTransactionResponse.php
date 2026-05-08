<?php


namespace LARAVEL\LARAVELGateway\OnePay\Message;
class QueryTransactionResponse extends Response
{
    /**
     * {@inheritdoc}
     */
    public function isSuccessful(): bool
    {
        return parent::isSuccessful() && 0 === strcasecmp('y', $this->data['vpc_DRExists']);
    }
}
