<?php

namespace App\Exceptions;

use Exception;

class PurchaseAlreadyCompletedException extends Exception
{
    public function __construct()
    {
        parent::__construct('Purchase order has already been completed.');
    }
}