<?php

namespace App\Service;

class ServerListService
{

    private readonly array $storageSizes;
    private readonly array $ramSizes;
    private readonly array $hddTypes;

    public function __construct(private readonly string $upload_directory)
    {
        $this->storageSizes = ['0', '250GB', '500GB', '1TB', '2TB', '3TB', '4TB', '8TB', '12TB', '24TB', '48TB', '96TB'];
        $this->ramSizes = ['2GB', '4GB', '8GB', '12GB', '16GB', '24GB', '32GB', '48GB', '64GB', '96GB', '128GB'];
        $this->hddTypes = ['SAS', 'SATA', 'SSD'];
    }

    public function getServerList(array $queryParams = null): array
    {
        $serverList = $this->csvToArray();

        // TODO: Cache Server List
        if (!$queryParams) return $serverList;

        if (!empty($queryParams['location'])) {
            $serverList = array_filter($serverList, fn($server) => $server['location'] === $queryParams['location']);
        }

        if ($queryParams['storage']) {
            $serverList = array_filter($serverList, fn($server) => array_search($server['hddSizeRange'], $this->storageSizes) <= array_search($this->storageSizes[$queryParams['storage']], $this->storageSizes));
        }

        if (!empty($queryParams['ram'])) {
            $serverList = array_filter($serverList, function ($server) use ($queryParams) {
                foreach ($queryParams['ram'] as $ram) {
                    if ($server['ramSize'] === $ram) {
                        return true;
                    }
                }
                return false;
            });
        }

        if (!empty($queryParams['hddType'])) {
            $serverList = array_filter($serverList, fn($server) => str_contains($server['hddType'], $queryParams['hddType']));
        }

        return $serverList;
    }

    public function getServerFilters(): array
    {
        $serverList = $this->getServerList();

        $locations = array_unique(array_column($serverList, 'location'));
        sort($locations);

        return [
            'storage' => $this->storageSizes,
            'ram' => $this->ramSizes,
            'hddType' => $this->hddTypes,
            'location' => $locations,
        ];
    }

    private function csvToArray(): array
    {
        $file = fopen("$this->upload_directory/processed-server-list.csv", 'r');

        $header = null;
        $data = [];

        if ($file !== false) {
            while (($row = fgetcsv($file, 1000)) !== false) {
                if (!$header) {
                    $header = $row;
                } else {
                    $rowWithId = array_combine($header, $row);

                    if (!$rowWithId) continue;

                    $data[] = $rowWithId;
                }
            }
            fclose($file);
        }
        return $data;
    }
}
