<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ProductInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('category_id')
                    ->numeric(),
                TextEntry::make('sku')
                    ->label('SKU'),
                TextEntry::make('thumbnail'),
                TextEntry::make('purchase_price')
                    ->numeric(),
                TextEntry::make('selling_price')
                    ->numeric(),
                TextEntry::make('qty')
                    ->numeric(),
                TextEntry::make('discount_price')
                    ->numeric(),
                TextEntry::make('slug'),
                IconEntry::make('is_active')
                    ->boolean(),
                IconEntry::make('is_featured')
                    ->boolean(),
            ]);
    }
}
