<?php

namespace App\Filament\Resources\Admins\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AdminsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(fn () => app()->getLocale() === 'ar' ? 'الاسم' : 'Name')
                    ->searchable(),
                TextColumn::make('email')
                    ->label(fn () => app()->getLocale() === 'ar' ? 'البريد الالكتروني' : 'Email address')
                    ->searchable(),
                TextColumn::make('email_verified_at')
                    ->label(fn () => app()->getLocale() === 'ar' ? 'التحقق من البريد الالكتروني' : 'Email verified at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(fn () => app()->getLocale() === 'ar' ? 'الانشاء في' : 'Created at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(fn () => app()->getLocale() === 'ar' ? 'التعديل في' : 'Updated at')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                 DeleteAction::make()
                    ->modalHeading(__('filament/admin/admin_resource.delete_admin'))
                    ->successNotification(
                        Notification::make()
                            ->title(__('filament/admin/admin_resource.admin_deleted_successfully'))
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
