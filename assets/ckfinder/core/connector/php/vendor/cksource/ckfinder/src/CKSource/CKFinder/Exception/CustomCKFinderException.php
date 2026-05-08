<?php

namespace CKSource\CKFinder\Exception;

use CKSource\CKFinder\Error;

class CustomCKFinderException extends CKFinderException
{
    public function __construct($message = 'Max files in dir', $parameters = [], \Exception $previous = null)
    {
        parent::__construct($message, Error::UNKNOWN, $parameters, $previous);
    }
}