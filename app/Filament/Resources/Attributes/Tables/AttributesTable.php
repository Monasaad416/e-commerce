<?php

namespace App\Filament\Resources\Attributes\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AttributesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
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
                EditAction::make()
                    ->modalHeading(__('filament/admin/attribute_resource.edit_attribute'))
                    ->successNotification(
                        Notification::make()
                            ->title(__('filament/admin/attribute_resource.attribute_updated_successfully'))
                            ->color('success')
                    ),

                DeleteAction::make()
                    ->modalHeading(__('filament/admin/attribute_resource.delete_attribute'))
                    ->successNotification(
                        Notification::make()
                            ->title(__('filament/admin/attribute_resource.attribute_deleted_successfully'))
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
