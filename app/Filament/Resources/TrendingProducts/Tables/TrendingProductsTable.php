<?php

declare(strict_types=1);

namespace App\Filament\Resources\TrendingProducts\Tables;

use App\Filament\Resources\TrendingProducts\Support\ProductLabel;
use App\Models\Minifig;
use App\Models\Part;
use App\Models\TrendingProduct;
use App\Support\MediaUrl;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TrendingProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with([
                'product.color',
                'product.productable',
            ]))
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->columns([
                ImageColumn::make('product.image')
                    ->label('Afbeelding')
                    ->state(fn (TrendingProduct $record): ?string => self::image($record)),
                TextColumn::make('product_id')
                    ->label('Product')
                    ->state(fn (TrendingProduct $record): ?string => ProductLabel::for($record->product))
                    ->wrap(),
                TextColumn::make('product.stock')
                    ->label('Voorraad')
                    ->sortable(),
                TextColumn::make('sort_order')
                    ->label('Volgorde')
                    ->sortable(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    private static function image(TrendingProduct $record): ?string
    {
        $product = $record->product;

        if ($product === null) {
            return null;
        }

        $productable = $product->productable;

        if ($productable instanceof Part) {
            $productable->loadMissing('partColors.media');

            return MediaUrl::forPartAnyColor($productable, (int) $product->color_id);
        }

        if ($productable instanceof Minifig) {
            $productable->loadMissing('media');

            return MediaUrl::forMinifig($productable);
        }

        return null;
    }
}
