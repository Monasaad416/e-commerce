<?php

namespace App\Filament\Resources\Orders\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user_id')
                    ->label(__('filament/admin/order_resource.user_id'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('subtotal')
                    ->label(__('filament/admin/order_resource.subtotal'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('discount')
                    ->label(__('filament/admin/order_resource.discount'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('shipping_fee')
                    ->label(__('filament/admin/order_resource.shipping_fee'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('total')
                    ->label(__('filament/admin/order_resource.total'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('status')
                    ->label(__('filament/admin/order_resource.status')),
                TextColumn::make('payment_status')
                    ->label(__('filament/admin/order_resource.payment_status')),
                TextColumn::make('shipping_status')
                    ->label(__('filament/admin/order_resource.shipping_status')),
                TextColumn::make('created_at')
                    ->label(__('filament/admin/order_resource.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('filament/admin/order_resource.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                // EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
