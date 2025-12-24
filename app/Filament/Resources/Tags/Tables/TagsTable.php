<?php

namespace App\Filament\Resources\Tags\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TagsTable
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
                    ->modalHeading(__('filament/admin/tag_resource.edit_tag'))
                    ->successNotification(
                        Notification::make()
                            ->title(__('filament/admin/tag_resource.tag_updated_successfully'))
                            ->color('success')
                    ),

                DeleteAction::make()
                    ->modalHeading(__('filament/admin/tag_resource.delete_tag'))
                    ->successNotification(
                        Notification::make()
                            ->title(__('filament/admin/tag_resource.tag_deleted_successfully'))
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
