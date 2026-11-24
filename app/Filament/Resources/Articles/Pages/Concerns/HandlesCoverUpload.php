<?php

declare(strict_types=1);

namespace App\Filament\Resources\Articles\Pages\Concerns;

use App\Domain\Image\Actions\UploadArticleCoverImageAction;
use App\Models\Article;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Keeps the cover form fields off the model and hands them to the upload action
 * once the record is saved.
 */
trait HandlesCoverUpload
{
    protected ?string $coverUploadPath = null;

    protected bool $shouldRemoveCover = false;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function pullCoverFields(array $data): array
    {
        $upload = $data['cover_upload'] ?? null;
        $this->coverUploadPath = is_array($upload) ? (array_values($upload)[0] ?? null) : $upload;
        $this->shouldRemoveCover = (bool) ($data['remove_cover'] ?? false);

        unset($data['cover_upload'], $data['remove_cover']);

        return $data;
    }

    protected function processCoverUpload(): void
    {
        /** @var Article $article */
        $article = $this->getRecord();

        try {
            app(UploadArticleCoverImageAction::class)->handle($article, $this->coverUploadPath, $this->shouldRemoveCover);
        } catch (Throwable $exception) {
            Log::error('Article cover upload failed.', ['article_id' => $article->getKey(), 'exception' => $exception]);
            Notification::make()->danger()->title('Cover image upload failed')->body('The article was saved, but the cover image could not be uploaded. Please try again.')->send();
        }

        $this->coverUploadPath = null;
        $this->shouldRemoveCover = false;
    }
}
