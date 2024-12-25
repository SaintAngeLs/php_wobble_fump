<?php

namespace App\Tests\Controller\File\FileController;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class FileControllerTest extends WebTestCase
{
    private static $filePath1;
    private static $filePath2;

    public static function setUpBeforeClass(): void
    {
        self::$filePath1 = self::getTestFile('http://212.183.159.230/10MB.zip', '10MB.zip');
        self::$filePath2 = self::getTestFile('http://212.183.159.230/20MB.zip', '20MB.zip');
    }

    private static function getTestFile(string $url, string $filename): string
    {
        $path = __DIR__ . '/files/' . $filename;
        if (!file_exists($path)) {
            if (!is_dir(dirname($path))) {
                mkdir(dirname($path), 0777, true);
            }
            file_put_contents($path, fopen($url, 'r'));
        }
        return $path;
    }

    public function testIdenticalFiles()
    {
        $client = static::createClient();
        $crawler = $client->request(
            'POST',
            '/process-files',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['path1' => self::$filePath1, 'path2' => self::$filePath1])
        );

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertStringContainsString('No differences found.', $client->getResponse()->getContent());
    }

    public function testDifferentFiles()
    {
        $client = static::createClient();
        $crawler = $client->request(
            'POST',
            '/process-files',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['path1' => self::$filePath1, 'path2' => self::$filePath2])
        );

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $responseContent = $client->getResponse()->getContent();
        $this->assertStringContainsString('Differences saved to: ', $responseContent);

        preg_match('/Differences saved to: (.+)<\/p>/', $responseContent, $matches);
        $this->assertNotEmpty($matches, 'Difference file path not found in response.');

        $differenceFilePath = html_entity_decode($matches[1]);
        $this->assertFileExists($differenceFilePath);

        @unlink($differenceFilePath);
    }

    public function testMissingParameters()
    {
        $client = static::createClient();

        $crawler = $client->request(
            'POST',
            '/process-files',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['path1' => self::$filePath1])
        );
        $this->assertEquals(400, $client->getResponse()->getStatusCode());

        $crawler = $client->request(
            'POST',
            '/process-files',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['path2' => self::$filePath2])
        );
        $this->assertEquals(400, $client->getResponse()->getStatusCode());

        $crawler = $client->request(
            'POST',
            '/process-files',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([])
        );
        $this->assertEquals(400, $client->getResponse()->getStatusCode());
    }

    public function testMemoryUsage()
    {
        gc_collect_cycles();

        $client = static::createClient();
    
        $crawler = $client->request(
            'POST',
            '/process-files',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['path1' => self::$filePath1, 'path2' => self::$filePath2])
        );
    
        unset($crawler);
    
        gc_collect_cycles();
    
        $content = $client->getResponse()->getContent();
        preg_match('/Peak Memory: (\d+) bytes/', $content, $matches);
    
        $this->assertNotEmpty($matches, 'Memory usage information not found in response.');
        $peakMemory = (int)$matches[1];
    
        $this->assertLessThanOrEqual(8 * 1024 * 1024, $peakMemory, 'Peak memory usage exceeds 8MB.');
    
        gc_collect_cycles();
    }
    

    public static function tearDownAfterClass(): void
    {
        @unlink(self::$filePath1);
        @unlink(self::$filePath2);
    }
}
