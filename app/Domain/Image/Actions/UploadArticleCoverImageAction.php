<?php

declare(strict_types=1);

namespace App\Domain\Image\Actions;

use App\Domain\Image\Jobs\DeleteCloudflareImageJob;
use App\Integrations\Cloudflare\Facades\Cloudflare;
use App\Models\Article;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Replaces or removes an article's cover image from an admin upload. Uploads
 * synchronously so the editor sees the result right after saving.
 */
class UploadArticleCoverImageAction
{
    /**
     * @param  string|null  $uploadPath  Path on the local disk of the uploaded file.
     */
    public function handle(Article $article, ?string $uploadPath, bool $removeCover = false): void
    {
        $previousImageId = $article->cloudflare_image_id;

        if ($uploadPath !== null && $uploadPath !== '') {
            try {
                // A random suffix: the previous id is deleted asynchronously and Cloudflare refuses existing ids.
                $imageId = $article->cloudflareImageKey().'-'.Str::lower(Str::random(8));
                $image = Cloudflare::images()->upload($imageId, Storage::disk('local')->path($uploadPath), [
                    'article_id' => (string) $article->getKey(),
                ]);
            } finally {
                Storage::disk('local')->delete($uploadPath);
            }

            $article->markCloudflareImageImported($image->id);
            $article->cloudflare_image_id = $image->id;
        } elseif ($removeCover && $previousImageId !== null) {
            $article->newQuery()->whereKey($article->getKey())->update(['cloudflare_image_id' => null]);
            $article->cloudflare_image_id = null;
        } else {
            return;
        }

        $article->syncOriginalAttribute('cloudflare_image_id');

        if ($previousImageId !== null && $previousImageId !== $article->cloudflare_image_id) {
            DeleteCloudflareImageJob::dispatch($previousImageId);
        }
    }
}
