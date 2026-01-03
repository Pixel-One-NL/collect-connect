<?php

declare(strict_types=1);

namespace App\Domain\Rebrickable\Services;

use App\Domain\Rebrickable\Contracts\RebrickableDownloader;
use Illuminate\Support\Facades\Http;
use ZipArchive;

class RebrickableDownloadService implements RebrickableDownloader
{
    public function retrieveRebrickableDataFromUrl(string $url): array
    {
        $csvContents = $this->getCsvContentsFromUrl($url);

        if (empty($csvContents)) {
            return [];
        }

        $data = [];

        $lines = explode("\n", $csvContents);
        $header = array_filter(str_getcsv(array_shift($lines)));

        while ($line = array_shift($lines)) {
            if (empty(trim($line))) {
                continue;
            }

            $row = str_getcsv($line);
            $data[] = array_combine($header, $row);
        }

        return $data;
    }

    /**
     * Use this method to retrieve the contents of a zipped .csv file
     * from the rebrickable server. It will be unpacked and return the
     * file as a resource
     */
    protected function getCsvContentsFromUrl(string $url): false|string
    {
        $response = Http::withOptions(['stream' => true])->get($url);

        if (! $response->successful()) {
            // TODO: Handle error
            return false;
        }

        // Store the zip in memory temporarily
        $temporaryPath = tempnam(sys_get_temp_dir(), 'rebrickable_');
        file_put_contents($temporaryPath, $response->body());

        // Unzip the file and retrieve the first .csv file
        $zip = new ZipArchive;
        $zip->open($temporaryPath);

        return $zip->getFromIndex(0);
    }
}
