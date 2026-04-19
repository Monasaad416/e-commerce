<?php

namespace App\Filament\Resources\Languages\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LanguagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('code')
                    ->searchable(),
                IconColumn::make('is_default')
                    ->boolean(),
                TextColumn::make('direction')
                    ->searchable(),
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
                    ->modalHeading(__('filament/admin/language_resource.edit_language'))
                    ->successNotification(
                        Notification::make()
                            ->title(__('filament/admin/language_resource.language_updated_successfully'))
                            ->color('success')
                    ),

                DeleteAction::make()
                    ->modalHeading(__('filament/admin/language_resource.delete_language'))
                    ->successNotification(
                        Notification::make()
                            ->title(__('filament/admin/language_resource.language_deleted_successfully'))
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
