<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class UploadService
{

    public function __construct(private readonly ExcelProcessorService $excelProcessorService, private readonly string $upload_directory)
    {
    }

    public function saveFile(UploadedFile $file): void
    {
        $newFilename = "server-list.{$file->guessExtension()}";
        $targetPath = "$this->upload_directory/$newFilename";

        if (file_exists($targetPath)) {
            $previousVersionsDir = "$this->upload_directory/previous-versions";

            if (!is_dir($previousVersionsDir)) {
                mkdir($previousVersionsDir);
            }

            $date = date('Y-m-d_H-i-s');

            $previousVersionFilename = "server-list-$date.{$file->guessExtension()}";
            $previousVersionPath = "$previousVersionsDir/$previousVersionFilename";

            rename($targetPath, $previousVersionPath);
        }

        $file->move(
            $this->upload_directory,
            $newFilename
        );

        $this->excelProcessorService->processFile($newFilename);

    }

}
