<?php

declare(strict_types=1);

namespace App\Domain\Rebrickable\Commands;

use App\Domain\Rebrickable\Contracts\ImportsRebrickableEntity;
use App\Domain\Rebrickable\Jobs\ImportRebrickableEntityJob;
use App\Domain\Rebrickable\Services\Imports\ColorImportService;
use App\Domain\Rebrickable\Services\Imports\InventoryImportService;
use App\Domain\Rebrickable\Services\Imports\InventoryMinifigImportService;
use App\Domain\Rebrickable\Services\Imports\InventoryPartImportService;
use App\Domain\Rebrickable\Services\Imports\InventorySetImportService;
use App\Domain\Rebrickable\Services\Imports\MinifigImportService;
use App\Domain\Rebrickable\Services\Imports\PartCategoryImportService;
use App\Domain\Rebrickable\Services\Imports\PartImportService;
use App\Domain\Rebrickable\Services\Imports\SetImportService;
use Illuminate\Console\Command;

class ImportRebrickableEntityCommand extends Command
{
    protected $signature = 'rebrickable:import-entity {--entity=}';

    /**
     * @var array<class-string<ImportsRebrickableEntity>, string>
     */
    protected array $importServices = [
        PartCategoryImportService::class => 'part_categories',
        PartImportService::class => 'parts',
        ColorImportService::class => 'colors',
        InventoryImportService::class => 'inventories',
        InventoryPartImportService::class => 'inventory_parts',
        MinifigImportService::class => 'minifigs',
        InventoryMinifigImportService::class => 'inventory_minifigs',
        InventorySetImportService::class => 'inventory_sets',
        SetImportService::class => 'sets',
    ];

    public function handle(): void
    {
        foreach ($this->getImportServices() as $importService) {
            ImportRebrickableEntityJob::dispatch($importService);
        }
    }

    /**
     * @return list<class-string<ImportsRebrickableEntity>>
     */
    protected function getImportServices(): array
    {
        if (empty($this->option('entity'))) {
            return array_keys($this->importServices);
        }

        return array_keys(array_filter(
            $this->importServices,
            fn (string $serviceName): bool => str($this->option('entity'))->lower()->is($serviceName)
        ));
    }
}
