<?php

namespace App\Filament\Resources\Orders\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('user_id')
                    ->numeric(),
                TextInput::make('subtotal')
                    ->required()
                    ->numeric(),
                TextInput::make('discount')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('shipping_fee')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('total')
                    ->required()
                    ->numeric(),
                Select::make('status')
                    ->options([
            'pending' => 'Pending',
            'processing' => 'Processing',
            'shipped' => 'Shipped',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
        ])
                    ->default('pending')
                    ->required(),
                Select::make('payment_status')
                    ->options(['unpaid' => 'Unpaid', 'paid' => 'Paid', 'failed' => 'Failed'])
                    ->default('unpaid')
                    ->required(),
                Select::make('shipping_status')
                    ->options(['not_shipped' => 'Not shipped', 'shipped' => 'Shipped', 'delivered' => 'Delivered'])
                    ->default('not_shipped')
                    ->required(),
                Textarea::make('notes')
                    ->columnSpanFull(),
            ]);
    }
}
