<?php

namespace App\Filament\Resources\Taxes\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class TaxInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('created_at')
                    ->dateTime(),
                TextEntry::make('updated_at')
                    ->dateTime(),
            ]);
    }
}
