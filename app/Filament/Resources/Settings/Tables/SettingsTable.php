<?php

namespace App\Filament\Resources\Settings\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Storage;

class SettingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('key')
                    ->label(__('filament/admin/setting_resource.fields.key'))
                    ->formatStateUsing(static function (?string $state): ?string {
                        if ($state === null || $state === '') {
                            return $state;
                        }

                        $translationKey = "filament/admin/setting_resource.keys.{$state}";

                        return Lang::has($translationKey) ? __($translationKey) : $state;
                    })
                    ->searchable(),
                ImageColumn::make('value_image')
                    ->label(__('filament/admin/setting_resource.fields.value'))
                    ->getStateUsing(static function ($record): ?string {
                        if (! in_array($record?->key, ['light_logo', 'dark_logo', 'favicon'], true)) {
                            return null;
                        }

                        $state = $record?->value;
                        $decoded = $state;

                        if (is_string($state)) {
                            $tmp = json_decode($state, true);
                            if (json_last_error() === JSON_ERROR_NONE) {
                                $decoded = $tmp;
                            }
                        }

                        $path = is_string($decoded) ? trim($decoded) : '';
                        if ($path === '') {
                            return null;
                        }

                        if (str_starts_with($path, 'http')) {
                            return $path;
                        }

                        $normalized = ltrim($path, '/');

                        if (str_starts_with($normalized, 'storage/')) {
                            return asset('/' . $normalized);
                        }

                        if (Storage::disk('public')->exists($normalized)) {
                            return asset('storage/' . $normalized);
                        }

                        return asset($path);
                    })
                    ->square()
                    ->extraImgAttributes(['class' => 'object-contain']),
                TextColumn::make('value')
                    ->label(__('filament/admin/setting_resource.fields.value'))
                    ->getStateUsing(static function ($record): string {
                        if (in_array($record?->key, ['light_logo', 'dark_logo', 'favicon'], true)) {
                            // Rendered via ImageColumn above.
                            return '';
                        }

                        $state = $record?->value;
                        $decoded = $state;

                        if (is_string($state)) {
                            $tmp = json_decode($state, true);
                            if (json_last_error() === JSON_ERROR_NONE) {
                                $decoded = $tmp;
                            }
                        }

                        $key = $record?->key;

                        if ($key === 'tax_value') {
                            $value = is_scalar($decoded) ? (string) $decoded : '';
                            return $value . '%';
                        }

                        if (is_bool($decoded)) {
                            return $decoded ? 'true' : 'false';
                        }

                        if (is_scalar($decoded) || $decoded === null) {
                            return (string) $decoded;
                        }

                        return json_encode($decoded);
                    })
                    ->searchable(),
                TextColumn::make('created_at')
                    ->label(__('filament/admin/setting_resource.fields.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('filament/admin/setting_resource.fields.updated_at'))
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
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
