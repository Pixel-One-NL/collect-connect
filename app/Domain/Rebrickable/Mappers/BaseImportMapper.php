<?php

declare(strict_types=1);

namespace App\Domain\Rebrickable\Mappers;

use App\Domain\Rebrickable\Contracts\RebrickableFieldTransformer;
use App\Domain\Rebrickable\Contracts\RebrickableMapper;

abstract class BaseImportMapper implements RebrickableMapper
{
    /**
     * @var array<string, string>
     */
    protected array $mapping = [];

    /**
     * @var array<string, class-string<RebrickableFieldTransformer>>
     */
    protected array $transformers = [];

    /**
     * @var array<string, mixed>
     */
    protected array $defaults = [];

    /**
     * @var string|list<string>
     */
    protected string|array $uniqueKey = 'rebrickable_id';

    public function map(array $row): array
    {
        $mapped = [];

        foreach ($this->mapping as $sourceField => $targetField) {
            $value = data_get($row, $sourceField);

            if (empty($value) && array_key_exists($sourceField, $row) && array_key_exists($sourceField, $this->defaults)) {
                $value = $this->defaults[$sourceField];
            }

            if (isset($this->transformers[$sourceField])) {
                $value = app($this->transformers[$sourceField])->transform($value);
            }

            $mapped[$targetField] = $value;
        }

        return $mapped;
    }

    public function getUniqueKey(): string|array
    {
        return $this->uniqueKey;
    }

    /**
     * @return array<string, string>
     */
    public function getMapping(): array
    {
        return $this->mapping;
    }
}
