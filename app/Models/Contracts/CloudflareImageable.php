<?php

declare(strict_types=1);

namespace App\Models\Contracts;

/**
 * A record with one image stored in Cloudflare Images.
 */
interface CloudflareImageable
{
    public function cloudflareImageKey(): string;

    public function hasCloudflareImage(): bool;

    public function markCloudflareImageImported(string $imageId): void;

    public function markCloudflareImageFailed(): void;
}
