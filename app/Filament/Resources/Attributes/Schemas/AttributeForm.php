<?php

namespace App\Filament\Resources\Attributes\Schemas;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class AttributeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()->translatableTabs()->columnSpanFull(),

                Select::make('type')
                ->options([
                    'select' => 'Select',
                    'radio' => 'Radio',
                    'checkbox' => 'Checkbox',
                ])
                ->required()
                ->columnSpanFull(),

             Repeater::make('options')
    ->relationship('values')
    ->schema([
        TextInput::make('value')
            ->label('Option Value')
            ->required()
            ->translatableTabs(),
    ])
    ->columnSpanFull()
    ->createItemButtonLabel('Add Option')

            ]);
    }
}
