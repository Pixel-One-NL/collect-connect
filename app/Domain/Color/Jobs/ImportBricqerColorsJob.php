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

    /**
     * @return array{bricqer_colors: int, matched: int, unmatched: int, created: int}
     */
    public function handle(): array
    {
        $colors = Bricqer::inventory()->colors();

        $stats = [
            'bricqer_colors' => 0,
            'matched' => 0,
            'unmatched' => 0,
            'created' => 0,
        ];

        foreach ($colors as $color) {
            $stats['bricqer_colors']++;

            $existingColor = Color::query()
                ->where('bricqer_color_id', $color->id)
                ->first();

            if ($existingColor) {
                $stats['matched']++;

                continue;
            }

            $existingColor = Color::query()
                ->whereRaw('LOWER(name) = ?', [mb_strtolower($color->name)])
                ->first();

            if ($existingColor) {
                if (! $existingColor->bricqer_color_id) {
                    $existingColor->bricqer_color_id = (string) $color->id;
                    $existingColor->bricklink_color_id = $color->bricklinkId;
                    $existingColor->save();
                }

                $stats['matched']++;

                continue;
            }

            Color::create([
                'name' => $color->name,
                'bricqer_color_id' => (string) $color->id,
                'bricklink_color_id' => $color->bricklinkId,
                'hex' => $color->rgb,
            ]);

            $stats['created']++;
            $stats['unmatched']++;
        }

        return $stats;
    }
}
