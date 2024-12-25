<?php

namespace App\Application\Exception;

use Exception;

class AppException extends Exception
{
    public function __construct(string $message = "", int $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
