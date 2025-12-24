<?php

namespace App\Filament\Resources\Categories\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\ToggleColumn;

class CategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('general.name')) 
                    ->searchable(),
                ImageColumn::make('img')
                    ->label(__('general.image'))
                    ->getStateUsing(fn ($record) =>
                        $record->img
                            ?  $record->img
                            : asset('storage/No_Image_Available.jpg')
                    ),
                ImageColumn::make('thumbnail')
                    ->label(__('general.thumbnail'))
                    ->getStateUsing(fn ($record) =>
                        $record->thumbnail
                            ?  $record->thumbnail
                            : asset('storage/No_Image_Available.jpg')
                    ),
                TextColumn::make('parent_id')
                    ->label(__('general.parent_id'))
                    ->numeric()
                    ->sortable(),

              ToggleColumn::make('is_active')
                ->label(__('general.status'))
                ->onColor('success')
                ->offColor('danger')
                ->afterStateUpdated(function ($record, $state) {
                    Notification::make()
                        ->title(__('general.status_updated'))
                        ->body(
                            $state
                                ? __('general.active')
                                : __('general.inactive')
                        )
                        ->color('success')
                        ->send();
                }),

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
                    ->modalHeading(__('filament/admin/category_resource.delete_category'))
                    ->successNotification(
                        Notification::make()
                            ->title(__('filament/admin/category_resource.category_deleted_successfully'))
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
