<?php

declare(strict_types=1);

namespace App\Integrations\Cloudflare\Resources;

use App\Integrations\Cloudflare\DataTransferObjects\CloudflareImage;
use App\Integrations\Cloudflare\Exceptions\ImageAlreadyExistsException;
use App\Integrations\Cloudflare\Exceptions\ImageSourceUnreachableException;
use App\Integrations\Cloudflare\Requests\Images\DeleteImageRequest;
use App\Integrations\Cloudflare\Requests\Images\GetImageRequest;
use App\Integrations\Cloudflare\Requests\Images\UploadImageFromUrlRequest;
use Saloon\Exceptions\Request\RequestException;

class ImageResource extends BaseResource
{
    /**
     * Ask Cloudflare to fetch and store the image at the given URL.
     *
     * @param  array<string, string>  $metadata
     *
     * @throws ImageAlreadyExistsException
     * @throws ImageSourceUnreachableException
     */
    public function uploadFromUrl(string $imageId, string $sourceUrl, array $metadata = []): CloudflareImage
    {
        try {
            return $this->connector
                ->send(new UploadImageFromUrlRequest($imageId, $sourceUrl, $metadata))
                ->dtoOrFail();
        } catch (RequestException $exception) {
            $errorCodes = $exception->getResponse()->collect('errors')
                ->pluck('code')
                ->map(fn (mixed $code): int => (int) $code);

            if ($errorCodes->intersect(config('cloudflare.error_codes.image_already_exists'))->isNotEmpty()) {
                throw new ImageAlreadyExistsException($exception->getMessage(), previous: $exception);
            }

            if ($errorCodes->intersect(config('cloudflare.error_codes.source_unreachable'))->isNotEmpty()) {
                throw new ImageSourceUnreachableException($exception->getMessage(), previous: $exception);
            }

            throw $exception;
        }
    }

    public function get(string $imageId): CloudflareImage
    {
        return $this->connector->send(new GetImageRequest($imageId))->dtoOrFail();
    }

    public function delete(string $imageId): void
    {
        $this->connector->send(new DeleteImageRequest($imageId));
    }
}
