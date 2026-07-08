<?php

declare(strict_types=1);

namespace App\Domain\Minifig\Jobs;

use App\Models\Minifig;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class ImportMinifigBricklinkNumbersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    /**
     * @var int<1, max>
     */
    protected int $batchSize = 1000;

    public function __construct(
        public ?string $filter = null,
    ) {}

    public function handle(): void
    {
        $data = $this->retrieveBricklinkData();

        foreach (array_chunk($data, $this->batchSize) as $chunk) {
            DB::beginTransaction();

            foreach ($chunk as $row) {
                Minifig::query()
                    ->where('rebrickable_id', data_get($row, 'id'))
                    ->update(['bricklink_id' => data_get($row, 'bricklink')]);
            }

            DB::commit();
        }
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function retrieveBricklinkData(): array
    {
        $response = Http::withOptions(['stream' => true])->get(config('minifig.bricklink_number_database_url'));

        if ($response->failed()) {
            return []; // TODO: Handle error
        }

        $temporaryCsv = tmpfile();
        fwrite($temporaryCsv, $response->body());
        $csvPath = data_get(stream_get_meta_data($temporaryCsv), 'uri');

        if (! $csvPath) {
            return []; // TODO: Handle error
        }

        $csvStream = fopen($csvPath, 'r');
        if (! $csvStream) {
            return []; // TODO: Handle error
        }

        $header = fgetcsv($csvStream);

        if (! $header) {
            return []; // TODO: Handle error
        }

        $header = array_filter($header);

        $results = [];

        while (($row = fgetcsv($csvStream)) !== false) {
            $results[] = array_combine($header, $row);
        }

        fclose($csvStream);

        return $results;
    }
}
