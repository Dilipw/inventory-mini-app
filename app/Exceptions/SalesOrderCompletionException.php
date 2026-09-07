<?php

namespace App\Exceptions;

use Exception;

class SalesOrderCompletionException extends Exception
{
    public function __construct()
    {
        parent::__construct(
            'Sales order cannot be completed.'
        );
    }
}