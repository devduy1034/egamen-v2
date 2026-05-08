<?php


namespace LARAVEL\LARAVELGateway\Momo\Message\Concerns;
trait ResponseProperties
{
    public function __get($name)
    {
        if (isset($this->data[$name])) {
            return $this->data[$name];
        } else {
            trigger_error(sprintf('Undefined property: %s::%s', __CLASS__, '$'.$name), E_USER_NOTICE);
            return;
        }
    }
    public function __set($name, $value)
    {
        if (isset($this->data[$name])) {
            trigger_error(sprintf('Undefined property: %s::%s', __CLASS__, '$'.$name), E_USER_NOTICE);
        } else {
            $this->$name = $value;
        }
    }
}