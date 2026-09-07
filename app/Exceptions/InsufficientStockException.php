<?php

namespace App\Exceptions;

use Exception;

class InsufficientStockException extends Exception
{
    public function __construct()
    {
        parent::__construct('Insufficient stock.');
    }
}