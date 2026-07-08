<?php

declare(strict_types=1);

namespace App\Domain\Part\Jobs;

use App\Integrations\Rebrickable\DataTransferObjects\Part as RebrickablePart;
use App\Integrations\Rebrickable\Facades\Rebrickable;
use App\Models\Part;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Support\Facades\DB;

class ImportPartBricklinkNumbersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    /**
     * We can only process 1.000 parts at a time since the Rebrickable API
     * has a limit of 1.000 results per page.
     *
     * @see https://rebrickable.com/api/v3/docs (Standard Parameters)
     *
     * @var int<1, 1000>
     */
    protected int $batchSize = 180;

    public function __construct(
        public ?string $filter = null,
        public ?int $lastProcessedPartId = null,
    ) {}

    public function handle(): void
    {

        $parts = $this->retrievePartsToUpdate();
        $rebrickableParts = $this->retrieveRebrickableParts($parts);

        DB::transaction(fn () => $rebrickableParts->each(fn (RebrickablePart $rebrickablePart) => Part::query()
            ->where('rebrickable_id', $rebrickablePart->partNum)
            ->update([
                'bricklink_id' => $rebrickablePart->bricklinkIds?->first(),
            ])
        ));

        if ($parts->count() === $this->batchSize) {
            if (app()->isLocal() && ! app()->runningUnitTests() && config('queue.default') === 'sync') {
                sleep(1);
            }

            $job = new ImportPartBricklinkNumbersJob(
                filter: $this->filter,
                lastProcessedPartId: $parts->last()?->id,
            );

            dispatch($job)->delay(1);
        }
    }

    /**
     * @return Collection<int, Part>
     */
    protected function retrievePartsToUpdate(): Collection
    {
        return Part::query()
            ->orderBy('id')
            ->whereNull('bricklink_id')
            ->where('id', '>', $this->lastProcessedPartId ?? 0)
            ->limit($this->batchSize)
            ->get();
    }

    /**
     * @param  Collection<int, Part>  $parts
     * @return SupportCollection<int, RebrickablePart>
     */
    protected function retrieveRebrickableParts(Collection $parts): SupportCollection
    {
        /** @var list<string> $rebrickableIds */
        $rebrickableIds = $parts->pluck('rebrickable_id')->toArray();

        return Rebrickable::parts()->list()
            ->setPageSize($this->batchSize)
            ->partNumbers($rebrickableIds)
            ->get();
    }
}
