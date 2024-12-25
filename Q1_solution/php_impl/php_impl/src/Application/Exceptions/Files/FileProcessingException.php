<?php

namespace App\Application\Exception\Files;

use App\Application\Exception\AppException;
use Exception;

class FileProcessingException extends AppException
{
    public function __construct(string $message, int $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
