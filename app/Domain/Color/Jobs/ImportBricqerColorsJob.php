<?php

declare(strict_types=1);

namespace App\Domain\Color\Jobs;

use App\Integrations\Bricqer\Facades\Bricqer;
use App\Models\Color;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;

class ImportBricqerColorsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public function handle(): void
    {
        $colors = Bricqer::inventory()->colors();

        /**
         * - Check if there's a part with a matching bricqer_color_id
         * - If not, check if there's a color with a matching name
         * - If not, create a new color
         */
        foreach ($colors as $color) {
            $existingColor = Color::where('bricqer_color_id', $color->id)->first();
            if ($existingColor) {
                continue;
            }

            $existingColor = Color::where('name', $color->name)->first();
            if ($existingColor) {
                if (! $existingColor->bricqer_color_id) {
                    $existingColor->bricqer_color_id = $color->id;
                    $existingColor->bricklink_color_id = $color->bricklinkId;
                    $existingColor->save();
                }

                continue;
            }

            Color::create([
                'name' => $color->name,
                'bricqer_color_id' => $color->id,
                'hex' => $color->rgb,
            ]);
        }
    }
}
