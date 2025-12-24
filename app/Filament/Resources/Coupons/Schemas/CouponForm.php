<?php

namespace App\Filament\Resources\Coupons\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CouponForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')
                ->label(__('filament/admin/coupon_resource.code'))
                    ->required(),
                TextInput::make('type')
                ->label(__('filament/admin/coupon_resource.type'))
                    ->required(),
                TextInput::make('value')
                ->label(__('filament/admin/coupon_resource.value'))
                    ->required()
                    ->numeric(),
                TextInput::make('min_purchase')
                ->label(__('filament/admin/coupon_resource.min_purchase'))
                    ->numeric(),
                TextInput::make('usage_limit')
                ->label(__('filament/admin/coupon_resource.usage_limit'))
                    ->numeric(),
                TextInput::make('per_user_limit')
                ->label(__('filament/admin/coupon_resource.per_user_limit'))
                    ->numeric(),
                DatePicker::make('start_date')
                ->label(__('filament/admin/coupon_resource.start_date')),
                DatePicker::make('end_date')
                ->label(__('filament/admin/coupon_resource.end_date')),
                Toggle::make('is_active')
                ->label(__('filament/admin/coupon_resource.is_active'))
                ->default(true)
                    ->required(),
            ]);
    }
}
