<?php

namespace App\Tests\Service;

use App\Service\UploadService;
use App\Service\ExcelProcessorService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class UploadServiceTest extends TestCase
{
    private ExcelProcessorService|MockObject $excelProcessorService;
    private UploadService $uploadService;

    protected function setUp(): void
    {
        $this->excelProcessorService = $this->createMock(ExcelProcessorService::class);
        $this->uploadService = new UploadService($this->excelProcessorService, '/tmp');
    }

    public function testFileIsSavedAndProcessed(): void
    {
        $file = $this->createMock(UploadedFile::class);
        $file->method('guessExtension')->willReturn('xlsx');
        $file->expects($this->once())->method('move');

        $this->excelProcessorService->expects($this->once())->method('processFile');

        $this->uploadService->saveFile($file);
    }

    public function testPreviousFileIsMovedToPreviousVersionsDirectory(): void
    {
        $file = $this->createMock(UploadedFile::class);
        $file->method('guessExtension')->willReturn('xlsx');
        $file->expects($this->once())->method('move');

        touch('/tmp/server-list.xlsx');

        $this->uploadService->saveFile($file);

        $files = glob('/tmp/previous-versions/server-list-*');
        $this->assertNotEmpty($files, 'No files found that match the pattern "/tmp/previous-versions/server-list-*"');
    }
}
