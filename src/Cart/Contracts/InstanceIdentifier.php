<?php



namespace LARAVEL\Cart\Contracts;

interface InstanceIdentifier
{
    public function getInstanceIdentifier($options = null);
    public function getInstanceGlobalDiscount($options = null);
}