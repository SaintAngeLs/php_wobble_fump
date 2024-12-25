<?php

namespace App\Infrastructure\File;

use App\Core\File\FileReader\FileReaderInterface;
use App\Core\Exception\Files\FileNotFoundException;
use RuntimeException;

class FileReader implements FileReaderInterface
{
    public function streamReadFile(string $path, callable $callback)
    {
        if (!file_exists($path)) {
            throw new FileNotFoundException("File not found: $path");
        }

        $handle = fopen($path, 'rb');
        if (!$handle) {
            throw new RuntimeException("Failed to open file: $path");
        }

        try {
            while (!feof($handle)) {
                $buffer = fread($handle, 4096); 
                call_user_func($callback, $buffer);
            }
        } finally {
            fclose($handle);
        }
    }
}
