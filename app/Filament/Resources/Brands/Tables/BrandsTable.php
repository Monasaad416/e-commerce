<?php

namespace App\Filament\Resources\Brands\Tables;

use Illuminate\Database\Eloquent\Builder;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BrandsTable
{
    public static function configure(Table $table): Table
    {


    
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('general.name'))
                    ->searchable()
                    ->sortable(),
                
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
               
                EditAction::make()
                    ->modalHeading(__('filament/admin/brand_resource.edit_brand'))
                    ->successNotification(
                        Notification::make()
                            ->title(__('filament/admin/brand_resource.brand_updated_successfully'))
                            ->color('success')
                    ),
                DeleteAction::make()
                    ->modalHeading(__('filament/admin/brand_resource.delete_brand'))
                    ->successNotification(
                        Notification::make()
                            ->title(__('filament/admin/brand_resource.brand_deleted_successfully'))
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
