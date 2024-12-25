<?php

namespace App\Core\File\FileReader;

interface FileReaderInterface
{
    public function streamReadFile(string $path, callable $callback);
}
