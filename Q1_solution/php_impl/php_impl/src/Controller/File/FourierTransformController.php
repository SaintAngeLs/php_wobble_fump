<?php

namespace App\Controller\File;

use App\Application\Services\File\FileService;
use App\Application\Services\File\FileRequestValidator\FileRequestValidator;
use App\Infrastructure\ExternalProcessMemoryCollector\ExternalProcessMemoryCollector;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Stopwatch\Stopwatch;

class FourierTransformController
{
    private $fileService;
    private $fileRequestValidator;
    private $memoryCollector;

    public function __construct(
        FileService $fileService,
        FileRequestValidator $fileRequestValidator,
        ExternalProcessMemoryCollector $memoryCollector
    ) {
        $this->fileService = $fileService;
        $this->fileRequestValidator = $fileRequestValidator;
        $this->memoryCollector = $memoryCollector;
    }

    /**
     * @Route("/process-files-fourier", name="process_files_fourier", methods={"POST"})
     */
    public function processFourier(Request $request): Response
    {
        $stopwatch = new Stopwatch();
        $stopwatch->start('processFilesFourier');

        try {
            $initialMemory = memory_get_usage(true);

            [$path1, $path2] = $this->fileRequestValidator->validateFilePaths($request);

            $differenceFilePath = $this->fileService->processFiles($path1, $path2);

            if (!file_exists($differenceFilePath) || filesize($differenceFilePath) === 0) {
                throw new \RuntimeException("Difference file is missing or empty: $differenceFilePath");
            }

            $fourierResultFilePath = $this->calculateFourierTransform($differenceFilePath);

            $peakMemory = memory_get_peak_usage(true);

            $event = $stopwatch->stop('processFilesFourier');

            return new Response(
                '<html><body>'
                . '<p>Fourier transform results saved to: <a href="/public_data/fourier_results.txt">View Results</a></p>'
                . '<p>Initial Memory: ' . $initialMemory . ' bytes</p>'
                . '<p>Peak Memory: ' . $peakMemory . ' bytes</p>'
                . '<p>External Process Peak Memory: ' . $this->memoryCollector->getPeakMemory() . ' MB</p>'
                . '</body></html>',
                Response::HTTP_OK
            );
        } catch (BadRequestHttpException $e) {
            return new Response(
                '<html><body><p>Bad Request: ' . htmlspecialchars($e->getMessage()) . '</p></body></html>',
                Response::HTTP_BAD_REQUEST
            );
        } catch (\Exception $e) {
            return new Response(
                '<html><body><p>Error processing files: ' . htmlspecialchars($e->getMessage()) . '</p></body></html>',
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    private function calculateFourierTransform(string $inputFilePath): string
    {
        $publicPath = $_SERVER['DOCUMENT_ROOT'] . 'public_data/';
        $outputFilePath = $publicPath . 'fourier_results.txt';

        if (!file_exists($inputFilePath) || filesize($inputFilePath) === 0) {
            throw new \RuntimeException("Input file for Fourier transform is missing or empty: $inputFilePath");
        }

        if (!file_exists(filename: $publicPath)) {
            mkdir($publicPath, 0777, true);
        }

        $fftlibPath = __DIR__ . '/../../../c_code/fftlib';
        $command = escapeshellcmd("$fftlibPath '$inputFilePath' '$outputFilePath'");

        $descriptors = [
            0 => ["pipe", "r"], // stdin
            1 => ["pipe", "w"], // stdout
            2 => ["pipe", "w"], // stderr
        ];

        $process = proc_open($command, $descriptors, $pipes);
        if (!is_resource($process)) {
            throw new \RuntimeException("Failed to start the FFT process.");
        }

        $status = proc_get_status($process);
        $pid = $status['pid'];

        $maxMemoryUsage = 0;

        while ($status['running']) {
            if (file_exists("/proc/$pid/status")) {
                $statusInfo = file_get_contents("/proc/$pid/status");
                if (preg_match('/VmRSS:\s+(\d+)\s+kB/', $statusInfo, $matches)) {
                    $currentMemoryUsage = (int)$matches[1]; 
                    $maxMemoryUsage = max($maxMemoryUsage, $currentMemoryUsage);
                }
            }
            usleep(50000); 
            $status = proc_get_status($process);
        }

        fclose($pipes[0]);
        fclose($pipes[1]);
        fclose($pipes[2]);

        proc_close($process);

        if (!file_exists($outputFilePath)) {
            throw new \RuntimeException("Fourier transform calculation failed. Output file not created.");
        }

        $this->memoryCollector->setPeakMemory($maxMemoryUsage / 1024);

        return $outputFilePath;
    }
}
