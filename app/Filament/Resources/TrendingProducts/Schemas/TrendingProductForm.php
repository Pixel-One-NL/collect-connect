<?php

declare(strict_types=1);

namespace App\Filament\Resources\TrendingProducts\Schemas;

use App\Filament\Resources\TrendingProducts\Support\ProductLabel;
use App\Models\Minifig;
use App\Models\Part;
use App\Models\Product;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class TrendingProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('product_id')
                    ->label('Product')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->searchable()
                    ->getSearchResultsUsing(fn (string $search): array => Product::query()
                        ->with(['color', 'productable'])
                        ->whereHasMorph(
                            'productable',
                            [Part::class, Minifig::class],
                            function (Builder $productable) use ($search): void {
                                $productable
                                    ->where('name', 'like', "%{$search}%")
                                    ->orWhere('bricklink_id', 'like', "%{$search}%");
                            },
                        )
                        ->orderByDesc('stock')
                        ->limit(50)
                        ->get()
                        ->mapWithKeys(fn (Product $product): array => [
                            $product->id => ProductLabel::for($product),
                        ])
                        ->all())
                    ->getOptionLabelUsing(fn ($value): ?string => ProductLabel::for(
                        Product::query()->with(['color', 'productable'])->whereKey($value)->first(),
                    )),
                TextInput::make('sort_order')
                    ->label('Volgorde')
                    ->helperText('Lager komt eerst. Slepen in de lijst werkt ook.')
                    ->numeric()
                    ->default(0)
                    ->required(),
            ]);
    }
}
