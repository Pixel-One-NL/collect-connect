<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class TrustedProxyTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Route::get('/_proxy-test', fn (Request $request): array => [
            'secure' => $request->isSecure(),
            'url' => url('/'),
        ]);
    }

    public function test_forwarded_https_from_reverse_proxy_is_trusted(): void
    {
        $this->withHeaders([
            'X-Forwarded-Proto' => 'https',
            'X-Forwarded-Host' => 'coco-staging.pixelonehosting.nl',
            'X-Forwarded-Port' => '443',
        ])->getJson('/_proxy-test')
            ->assertOk()
            ->assertJson([
                'secure' => true,
                'url' => 'https://coco-staging.pixelonehosting.nl',
            ]);
    }

    public function test_plain_requests_without_forwarded_headers_stay_http(): void
    {
        $this->getJson('/_proxy-test')
            ->assertOk()
            ->assertJson(['secure' => false]);
    }
}
