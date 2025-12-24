<?php

namespace App\Filament\Resources\Orders\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class OrderInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('user_id')
                    ->numeric(),
                TextEntry::make('subtotal')
                    ->numeric(),
                TextEntry::make('discount')
                    ->numeric(),
                TextEntry::make('shipping_fee')
                    ->numeric(),
                TextEntry::make('total')
                    ->numeric(),
                TextEntry::make('status'),
                TextEntry::make('payment_status'),
                TextEntry::make('shipping_status'),
                TextEntry::make('created_at')
                    ->dateTime(),
                TextEntry::make('updated_at')
                    ->dateTime(),
            ]);
    }
}
