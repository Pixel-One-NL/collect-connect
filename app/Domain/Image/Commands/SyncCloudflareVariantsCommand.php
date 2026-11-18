<?php

declare(strict_types=1);

namespace App\Domain\Image\Commands;

use App\Integrations\Cloudflare\Facades\Cloudflare;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('cloudflare:sync-variants')]
class SyncCloudflareVariantsCommand extends Command
{
    protected $description = 'Create or update the Cloudflare Images variants defined in config/cloudflare.php';

    public function handle(): int
    {
        $existingVariants = Cloudflare::variants()->list();

        foreach (config('cloudflare.variants') as $variantId => $settings) {
            $options = [...$settings, 'metadata' => 'none'];
            $existingVariant = $existingVariants->get($variantId);

            if ($existingVariant === null) {
                Cloudflare::variants()->create($variantId, $options);
                $this->info("Created variant {$variantId}.");

                continue;
            }

            if ($existingVariant->options == $options && $existingVariant->neverRequireSignedURLs) {
                $this->line("Variant {$variantId} is up to date.");

                continue;
            }

            Cloudflare::variants()->update($variantId, $options);
            $this->info("Updated variant {$variantId}.");
        }

        return self::SUCCESS;
    }
}
