<?php

namespace App\Controller\File;

use App\Application\Services\File\FileService;
use App\Application\Services\File\FileRequestValidator\FileRequestValidator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Stopwatch\Stopwatch;

class FourierTransformController
{
    private $fileService;
    private $fileRequestValidator;

    public function __construct(FileService $fileService, FileRequestValidator $fileRequestValidator)
    {
        $this->fileService = $fileService;
        $this->fileRequestValidator = $fileRequestValidator;
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
                . '</body></html>',
                Response::HTTP_OK
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

        if (!file_exists($publicPath)) {
            mkdir($publicPath, 0777, true);
        }

        $fftlibPath = __DIR__ . '/../../../c_code/fftlib';
        $command = escapeshellcmd("$fftlibPath '$inputFilePath' '$outputFilePath'");
        $returnVar = 0;

        exec($command, $output, $returnVar);

        if ($returnVar !== 0) {
            throw new \RuntimeException("Fourier transform calculation failed. Command: $command");
        }

        return $outputFilePath;
    }
}
