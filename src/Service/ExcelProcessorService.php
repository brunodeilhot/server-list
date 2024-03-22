<?php

namespace App\Service;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Psr\Log\LoggerInterface;

class ExcelProcessorService
{
    public function __construct(private readonly string $upload_directory, private readonly LoggerInterface $logger)
    {
    }

    public function processFile(string $filename): void
    {
        $filePath = "$this->upload_directory/$filename";

        $spreadsheet = IOFactory::load($filePath);
        $worksheet = $spreadsheet->getActiveSheet();

        $highestRow = $worksheet->getHighestRow();

        $rowData = $worksheet->rangeToArray(
            "A1:E$highestRow",
            null,
            true,
            false
        );

        $processedRows = [];

        foreach ($rowData as $key => $row) {
            if ($key === 0)
                $processedRows[] = $this->processHeaders();
            else
                $processedRows[] = $this->processRow($row);
        }

        $this->logger->info('Processing complete');

        $this->writeToCsv($processedRows);
    }

    private function processHeaders(): array
    {
        return ['model', 'ramSize', 'ramType', 'hddSize', 'hddType', 'hddSizeRange', 'location', 'price'];
    }

    private function processRow(array $row): array
    {
        $processedRow = [];

        foreach ($row as $key => $field) {

            if ($key === 1) {
                $processedRow = array_merge($processedRow, $this->processRAM($field));
                continue;
            } else if ($key === 2) {
                $processedRow = array_merge($processedRow, $this->processHDD($field));
                continue;
            }

            $processedRow[] = $field;
        }

        return $processedRow;
    }

    private function processRAM(string $ram): array
    {
        preg_match('/(\d+GB)(.*)/', $ram, $matches);
        $ramSize = $matches[1] ?? '';
        $ramType = $matches[2] ?? '';

        return [$ramSize, $ramType];
    }

public function processHDD(string $hdd): array
{
    preg_match('/(\d+x\d+)(GB|TB)(.*)/', $hdd, $matches);
    $size = $matches[1] ?? '';
    $sizeUnit = $matches[2] ?? '';
    $type = $matches[3] ?? '';

    $sizeParts = explode('x', $size);
    $sizeTotal = $sizeParts[0] * $sizeParts[1];

    if ($sizeUnit === 'TB') {
        $sizeTotal *= 1024;
    }

    $sizeRange = $this->processHDDSize($sizeTotal);

    return ["$size$sizeUnit", $type, $sizeRange];
}

    function processHDDSize(int $hddSize): string
    {
        $size = $hddSize;
        return match (true) {
            $size <= 250 => '250GB',
            $size <= 500 => '500GB',
            $size <= 1024 => '1TB',
            $size <= 2048 => '2TB',
            $size <= 3072 => '3TB',
            $size <= 4096 => '4TB',
            $size <= 8192 => '8TB',
            $size <= 12288 => '12TB',
            $size <= 24576 => '24TB',
            $size <= 49152 => '48TB',
            $size <= 73728 => '72TB',
            default => '0'
        };
    }

    private function writeToCsv(array $rows): void
    {
        $csvSpreadsheet = new Spreadsheet();
        $csvWorksheet = $csvSpreadsheet->getActiveSheet();
        $csvWorksheet->fromArray($rows);

        $csvWriter = IOFactory::createWriter($csvSpreadsheet, 'Csv');
        $csvFilePath = "$this->upload_directory/processed-server-list.csv";
        $csvWriter->save($csvFilePath);
    }
}
