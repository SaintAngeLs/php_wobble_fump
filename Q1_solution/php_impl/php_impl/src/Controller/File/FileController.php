<?php

namespace App\Controller\File;

use App\Application\Services\File\FileService;
use App\Application\Services\File\FileRequestValidator\FileRequestValidator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;

class FileController
{
    private $fileService;
    private $fileRequestValidator;

    public function __construct(FileService $fileService, FileRequestValidator $fileRequestValidator)
    {
        $this->fileService = $fileService;
        $this->fileRequestValidator = $fileRequestValidator;
    }

    /**
     * @Route("/process-files", name="process_files", methods={"POST"})
     */
    public function process(Request $request): Response
    {
        try {
            $initialMemory = memory_get_usage(true);

            [$path1, $path2] = $this->fileRequestValidator->validateFilePaths($request);

            $result = $this->fileService->processFiles($path1, $path2);

            $peakMemory = memory_get_peak_usage(true);

            if ($result === 'No differences found.') {
                return new Response(
                    '<html><body>'
                    . '<p>' . $result . '</p>'
                    . '<p>Initial Memory: ' . $initialMemory . ' bytes</p>'
                    . '<p>Peak Memory: ' . $peakMemory . ' bytes</p>'
                    . '</body></html>',
                    Response::HTTP_OK
                );
            }

            return new Response(
                '<html><body>'
                . '<p>Differences saved to: ' . htmlspecialchars($result) . '</p>'
                . '<p>Initial Memory: ' . $initialMemory . ' bytes</p>'
                . '<p>Peak Memory: ' . $peakMemory . ' bytes</p>'
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
}
