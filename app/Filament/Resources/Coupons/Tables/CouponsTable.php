<?php

namespace App\Filament\Resources\Coupons\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CouponsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label(__('filament/admin/coupon_resource.code'))
                    ->searchable(),
                TextColumn::make('type')
                    ->label(__('filament/admin/coupon_resource.type'))
                    ->searchable(),
                TextColumn::make('value')
                    ->label(__('filament/admin/coupon_resource.value'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('min_purchase')
                    ->label(__('filament/admin/coupon_resource.min_purchase'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('usage_limit')
                    ->label(__('filament/admin/coupon_resource.usage_limit'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('per_user_limit')
                    ->label(__('filament/admin/coupon_resource.per_user_limit'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('start_date')
                    ->label(__('filament/admin/coupon_resource.start_date'))
                    ->date()
                    ->sortable(),
                TextColumn::make('end_date')
                    ->label(__('filament/admin/coupon_resource.end_date'))
                    ->date()
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label(__('filament/admin/coupon_resource.is_active'))
                    ->boolean(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make()
                    ->modalHeading(__('filament/admin/coupon_resource.delete_coupon'))
                    ->successNotification(
                        Notification::make()
                            ->title(__('filament/admin/coupon_resource.coupon_deleted_successfully'))
                            ->color('success')
                    ),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
