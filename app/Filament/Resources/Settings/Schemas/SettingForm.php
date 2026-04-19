<?php

namespace App\Filament\Resources\Settings\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Storage;

class SettingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('key')
                    ->label(__('filament/admin/setting_resource.fields.key'))
                    ->disabled()
                    ->dehydrated()
                    ->required(),

                Hidden::make('value')
                    ->afterStateHydrated(static function (callable $set, callable $get, $state): void {
                        $decoded = $state;

                        if (is_string($state)) {
                            $tmp = json_decode($state, true);
                            if (json_last_error() === JSON_ERROR_NONE) {
                                $decoded = $tmp;
                            }
                        }

                        $set('value', $decoded);

                        $imageKeys = ['light_logo', 'dark_logo', 'favicon'];
                        if (in_array($get('key'), $imageKeys, true)) {
                            // Don't hydrate FileUpload with the stored string path
                            // because it may fail image/* validation on edit.
                            $set('value_file', null);
                        } else {
                            $set('value_text', is_scalar($decoded) || $decoded === null ? $decoded : json_encode($decoded));
                        }
                    })
                    ->dehydrateStateUsing(static fn($state): string => json_encode($state)),

                Placeholder::make('current_image')
                    ->label(__('filament/admin/setting_resource.fields.current_image'))
                    ->visible(static function (callable $get): bool {
                        return in_array($get('key'), ['light_logo', 'dark_logo', 'favicon'], true);
                    })
                    ->html()
                    ->content(static function (callable $get): string {
                        $value = $get('value');
                        if (!is_string($value) || trim($value) === '') {
                            return '';
                        }

                        $path = trim($value);

                        if (str_starts_with($path, 'http')) {
                            $url = $path;
                        } else {
                            $normalized = ltrim($path, '/');

                            if (str_starts_with($normalized, 'storage/')) {
                                $url = asset('/' . $normalized);
                            } elseif (Storage::disk('public')->exists($normalized)) {
                                $url = asset('storage/' . $normalized);
                            } else {
                                $url = asset($path);
                            }
                        }

                        $escapedUrl = e($url);

                        return "<img src=\"{$escapedUrl}\" alt=\"\" style=\"height:120px;max-width:320px;object-fit:contain;\" />";
                    }),

                FileUpload::make('value_file')
                    ->label(__('filament/admin/setting_resource.fields.value'))
                    ->disk('public')
                    ->directory('settings')
                    ->visibility('public')
                    ->acceptedFileTypes(['image/*'])
                    ->imagePreviewHeight(120)
                    ->openable()
                    ->downloadable()
                    ->live()
                    ->visible(static function (callable $get): bool {
                        return in_array($get('key'), ['light_logo', 'dark_logo', 'favicon'], true);
                    })
                    ->afterStateUpdated(static function (callable $set, $state): void {
                        // Only overwrite when user uploads a new image
                        if ($state !== null && $state !== '') {
                            $set('value', $state);
                        }
                    })
                    ->dehydrated(false),

                TextInput::make('value_text')
                    ->label(__('filament/admin/setting_resource.fields.value'))
                    ->live()
                    ->visible(static function (callable $get): bool {
                        return !in_array($get('key'), ['light_logo', 'dark_logo', 'favicon'], true);
                    })
                    ->required(static function (callable $get): bool {
                        return !in_array($get('key'), ['light_logo', 'dark_logo', 'favicon'], true);
                    })
                    ->numeric(static fn(callable $get): bool => in_array($get('key'), ['tax_value', 'shipping_cost'], true))
                    ->suffix(static fn(callable $get): ?string => $get('key') === 'tax_value' ? '%' : null)
                    ->afterStateUpdated(static function (callable $set, $state): void {
                        $set('value', $state);
                    })
                    ->dehydrated(false),
            ]);
    }
}
