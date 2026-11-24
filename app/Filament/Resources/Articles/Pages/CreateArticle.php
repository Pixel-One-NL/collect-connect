<?php

declare(strict_types=1);

namespace App\Filament\Resources\Articles\Pages;

use App\Filament\Resources\Articles\ArticleResource;
use App\Filament\Resources\Articles\Pages\Concerns\HandlesCoverUpload;
use Filament\Resources\Pages\CreateRecord;

class CreateArticle extends CreateRecord
{
    use HandlesCoverUpload;

    protected static string $resource = ArticleResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return $this->pullCoverFields($data);
    }

    protected function afterCreate(): void
    {
        $this->processCoverUpload();
    }
}
