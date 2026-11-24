<?php

declare(strict_types=1);

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Feature tests render the root Blade view, which references Vite assets.
     * CI runs the suite without a built manifest, so stub Vite out.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }
}
