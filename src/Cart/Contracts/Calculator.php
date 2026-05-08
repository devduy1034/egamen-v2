<?php


namespace LARAVEL\Cart\Contracts;
use LARAVEL\Cart\CartItem;

interface Calculator
{
    public static function getAttribute(string $attribute, CartItem $cartItem);
}