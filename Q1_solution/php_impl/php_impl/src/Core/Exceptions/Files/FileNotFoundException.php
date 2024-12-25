<?php

namespace App\Core\Exception\Files;

use App\Core\Exception\DomainException;

class FileNotFoundException extends DomainException
{
    public function __construct(string $path)
    {
        parent::__construct("File not found: $path", 404);
    }
}
