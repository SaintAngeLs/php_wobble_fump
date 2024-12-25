<?php

namespace App\Infrastructure\ExternalProcessMemoryCollector;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;

class ExternalProcessMemoryCollector extends DataCollector
{
    private float $peakMemory;

    public function __construct()
    {
        $this->peakMemory = 0;
        $this->data['peak_memory'] = 0; 
    }

    public function collect(Request $request, Response $response, \Throwable $exception = null): void
    {
        $this->data['peak_memory'] = $this->peakMemory;
    }

    public function setPeakMemory(float $peakMemory): void
    {
        $this->peakMemory = $peakMemory;
        $this->data['peak_memory'] = $peakMemory;
    }

    public function getPeakMemory(): float
    {
        return $this->data['peak_memory'] ?? 0;
    }

    public function reset(): void
    {
        $this->data = ['peak_memory' => 0]; 
    }

    public function getName(): string
    {
        return 'external_process_memory_collector';
    }
}
