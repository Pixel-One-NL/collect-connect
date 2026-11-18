<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Named Cloudflare Images variants, defined in config/cloudflare.php.
 */
enum ImageVariant: string
{
    case Thumb = 'thumb';
    case Medium = 'medium';
    case Large = 'large';
}
