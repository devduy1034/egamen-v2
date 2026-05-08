<?php



namespace LARAVEL\LARAVELGateway\VNPay\Message\Concerns;

trait ResponseProperties
{
    public function __get($name)
    {
        $property = $this->propertyNormalize($name);

        if (isset($this->data[$property])) {
            return $this->data[$property];
        } else {
            trigger_error(sprintf('Undefined property: %s::%s', __CLASS__, '$'.$name), E_USER_NOTICE);
            return;
        }
    }

    public function __set($name, $value)
    {
        $property = $this->propertyNormalize($name);

        if (isset($this->data[$property])) {
            trigger_error(sprintf('Undefined property: %s::%s', __CLASS__, '$'.$name), E_USER_NOTICE);
        } else {
            $this->$name = $value;
        }
    }

    private function propertyNormalize(string $property): string
    {
        if (0 === strpos($property, 'vnp') && false === strpos($property, '_')) {
            return 'vnp_'.substr($property, 3);
        }

        return $property;
    }
}