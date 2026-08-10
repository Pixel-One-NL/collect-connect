<?php

declare(strict_types=1);

namespace App\Domain\Bricqer\Commands;

use App\Integrations\Bricqer\BricqerConnector;
use App\Models\ShippingMethod;
use Illuminate\Console\Command;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Throwable;

class SyncShippingMethodsCommand extends Command
{
    protected $signature = 'bricqer:sync-shipping-methods';

    protected $description = 'Import shipping methods from Bricqer (GET). Falls back to BrickLink shop methods when /shipping/method is forbidden.';

    public function handle(BricqerConnector $connector): int
    {
        $imported = 0;

        try {
            $response = $this->sendGetRequest($connector, '/shipping/method/');

            foreach ($response->json() as $method) {
                ShippingMethod::query()->updateOrCreate(
                    ['bricqer_id' => $method['id'] ?? null],
                    [
                        'name' => $method['name'] ?? $method['description'] ?? 'Shipping',
                        'code' => data_get($method, 'costs.0.shipping_code'),
                        'price_cents' => (int) round(((float) data_get($method, 'costs.0.price', 0)) * 100),
                        'track_trace' => (bool) ($method['track_trace'] ?? false),
                        'is_active' => true,
                    ],
                );
                $imported++;
            }
        } catch (Throwable $e) {
            $this->warn('Primary shipping endpoint unavailable: '.$e->getMessage());

            $response = $this->sendGetRequest($connector, '/shops/bricklink/shippingmethod/');

            foreach ($response->json() as $method) {
                $name = $method['description'] ?? ('Method '.$method['id']);
                $price = str_contains(strtolower($name), 'international') ? 1295 : 495;

                ShippingMethod::query()->updateOrCreate(
                    ['bricqer_id' => $method['id'] ?? null],
                    [
                        'name' => $name,
                        'code' => $method['area'] ?? null,
                        'area' => $method['area'] ?? null,
                        'price_cents' => $price,
                        'track_trace' => true,
                        'countries' => ($method['area'] ?? null) === 'D' ? ['NL'] : [],
                        'is_active' => true,
                    ],
                );
                $imported++;
            }
        }

        $this->info("Imported {$imported} shipping methods.");

        return self::SUCCESS;
    }

    protected function sendGetRequest(BricqerConnector $connector, string $endpoint): Response
    {
        return $connector->send(new class($endpoint) extends Request
        {
            protected Method $method = Method::GET;

            public function __construct(protected string $endpoint) {}

            public function resolveEndpoint(): string
            {
                return $this->endpoint;
            }
        });
    }
}
