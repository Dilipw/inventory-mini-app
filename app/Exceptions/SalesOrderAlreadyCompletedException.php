<?php

namespace App\Exceptions;

use Exception;

class SalesOrderAlreadyCompletedException extends Exception
{
    public function __construct()
    {
        parent::__construct(
            'Completed sales order cannot be updated.'
        );
    }
}