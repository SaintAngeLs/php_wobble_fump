<?php

namespace App\Application\Services\File;

use App\Core\Exception\Files\FileNotFoundException;
use App\Core\File\FileReader\FileReaderInterface;
use Symfony\Component\Filesystem\Exception\RuntimeException;

class FileService
{
    private $fileReader;
    private const SIZE_THRESHOLD = 50 * 1024 * 1024; // 50MB

    public function __construct(FileReaderInterface $fileReader)
    {
        $this->fileReader = $fileReader;
    }

    public function processFiles(string $path1, string $path2): string
    {
        ini_set('max_execution_time', '18000');

        // Check file sizes
        $size1 = filesize($path1);
        $size2 = filesize($path2);

        if ($size1 > self::SIZE_THRESHOLD || $size2 > self::SIZE_THRESHOLD) {
            return $this->processLargeFilesWithCpp($path1, $path2);
        }

        return $this->processSmallFiles($path1, $path2);
    }

    private function processSmallFiles(string $path1, string $path2): string
    {
        $chunkSize = 512 * 1024; // 512KB chunk size
        $publicPath = $_SERVER['DOCUMENT_ROOT'] . 'public_data/';

        if (!file_exists($publicPath)) {
            mkdir($publicPath, 0777, true);
        }

        $differencePath = $publicPath . 'diff-' . md5($path1 . $path2) . '.bin';
        $diffHandle = fopen($differencePath, 'wb');

        if (!$diffHandle) {
            throw new RuntimeException("Failed to open file for writing differences: $differencePath");
        }

        $isDifferent = false;

        try {
            $handle1 = fopen($path1, 'rb');
            $handle2 = fopen($path2, 'rb');

            if (!$handle1 || !$handle2) {
                throw new RuntimeException("Failed to open one or both input files.");
            }

            while (!feof($handle1) || !feof($handle2)) {
                $chunk1 = fread($handle1, $chunkSize) ?: '';
                $chunk2 = fread($handle2, $chunkSize) ?: '';

                $length = max(strlen($chunk1), strlen($chunk2));

                for ($i = 0; $i < $length; $i++) {
                    $byte1 = $chunk1[$i] ?? "\0";
                    $byte2 = $chunk2[$i] ?? "\0";

                    $xorByte = $byte1 ^ $byte2;
                    if ($xorByte !== "\0") {
                        $isDifferent = true;
                    }

                    fwrite($diffHandle, $xorByte);
                }
            }

            fclose($handle1);
            fclose($handle2);

            if (!$isDifferent) {
                fclose($diffHandle);
                unlink($differencePath);
                return 'No differences found.';
            }
        } catch (\Exception $e) {
            fclose($diffHandle);
            unlink($differencePath);
            throw $e;
        } finally {
            if (is_resource($diffHandle)) {
                fclose($diffHandle);
            }
        }

        return $differencePath;
    }

    private function processLargeFilesWithCpp(string $path1, string $path2): string
    {
        $publicPath = $_SERVER['DOCUMENT_ROOT'] . 'public_data/';

        if (!file_exists($publicPath)) {
            mkdir($publicPath, 0777, true);
        }

        $differencePath = $publicPath . 'diff-' . md5($path1 . $path2) . '.bin';

        $cppBinary = __DIR__ . '/../../../../c_code/file_diff';
        $command = escapeshellcmd("$cppBinary '$path1' '$path2' '$differencePath'");
        $returnVar = 0;

        exec($command, $output, $returnVar);

        if ($returnVar !== 0) {
            throw new \RuntimeException("Failed to process large files with C++ program. Command: $command");
        }

        if (!file_exists($differencePath) || filesize($differencePath) === 0) {
            throw new \RuntimeException("Difference file is missing or empty after C++ processing.");
        }

        return $differencePath;
    }
}
