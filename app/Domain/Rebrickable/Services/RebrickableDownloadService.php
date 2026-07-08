<?php

declare(strict_types=1);

namespace App\Domain\Rebrickable\Services;

use App\Domain\Rebrickable\Contracts\RebrickableDownloader;
use Generator;
use Illuminate\Support\Facades\Http;
use ZipArchive;

class RebrickableDownloadService implements RebrickableDownloader
{
    public function retrieveRebrickableDataFromUrl(string $url): Generator
    {
        $response = Http::get($url);

        if ($response->failed()) {
            return; // TODO: Handle error
        }

        $tempZip = tmpfile();
        fwrite($tempZip, $response->body());
        $zipPath = data_get(stream_get_meta_data($tempZip), 'uri');

        if (! $zipPath) {
            return; // TODO: Handle error
        }

        $zip = new ZipArchive;
        $zip->open($zipPath);

        $csvName = $zip->getNameIndex(0);

        if (! $csvName) {
            return; // TODO: Handle error
        }

        $csvStream = $zip->getStream($csvName);

        if (! $csvStream) {
            return; // TODO: Handle error
        }

        $header = fgetcsv($csvStream);

        if (! $header) {
            return; // TODO: Handle error
        }

        $header = array_filter($header);

        while (($row = fgetcsv($csvStream)) !== false) {
            yield array_combine($header, $row);
        }

        fclose($csvStream);
        $zip->close();
    }
}
