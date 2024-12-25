<?php

namespace App\Application\Services\File\FileRequestValidator;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class FileRequestValidator
{
    public function validateFilePaths(Request $request): array
    {
        $data = json_decode($request->getContent(), true);

        if (!is_array($data)) {
            throw new BadRequestHttpException("Invalid JSON payload.");
        }

        $path1 = $data['path1'] ?? null;
        $path2 = $data['path2'] ?? null;

        if (empty($path1) || empty($path2)) {
            throw new BadRequestHttpException("Both file paths must be provided.");
        }

        $path1 = $this->handlePath($path1);
        $path2 = $this->handlePath($path2);

        return [$path1, $path2];
    }

    private function handlePath(string $path): string
    {
        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $this->downloadFile($path);
        }

        if (!file_exists($path) || !is_readable($path)) {
            throw new BadRequestHttpException("File does not exist or cannot be read: $path");
        }

        return $path;
    }

    private function downloadFile(string $url): string
    {
        $tempDir = sys_get_temp_dir();
        $fileName = $tempDir . '/' . basename($url);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_TIMEOUT, 3600);
        $fp = fopen($fileName, 'w+');
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_BUFFERSIZE, 128 * 1024); 
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_TIMEOUT, 300);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        $success = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);
        fclose($fp);

        if (!$success || !file_exists($fileName)) {
            throw new BadRequestHttpException("Failed to download file from URL: $url. Error: $error");
        }

        return $fileName;
    }

}
