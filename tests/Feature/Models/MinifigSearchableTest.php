<?php

declare(strict_types=1);

namespace Tests\Feature\Models;

use App\Models\Minifig;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Scout\Searchable;
use Tests\TestCase;

class MinifigSearchableTest extends TestCase
{
    use RefreshDatabase;

    public function test_minifig_uses_the_searchable_trait(): void
    {
        $this->assertContains(Searchable::class, class_uses_recursive(Minifig::class));
    }

    public function test_to_searchable_array_includes_ids_and_name(): void
    {
        $minifig = Minifig::factory()->create([
            'rebrickable_id' => 'fig-000123',
            'bricklink_id' => 'sw0001',
            'name' => 'Luke Skywalker',
        ]);

        $document = $minifig->toSearchableArray();

        $this->assertSame((string) $minifig->id, $document['id']);
        $this->assertSame('fig-000123', $document['rebrickable_id']);
        $this->assertSame('sw0001', $document['bricklink_id']);
        $this->assertSame('Luke Skywalker', $document['name']);
    }

    public function test_make_all_searchable_is_available_for_scout_import(): void
    {
        Minifig::factory()->count(2)->create();

        // scout:import ultimately calls this static method from the Searchable trait.
        Minifig::makeAllSearchable();

        $this->assertTrue(true);
    }
}
