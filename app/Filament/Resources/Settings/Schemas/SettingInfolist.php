<?php

namespace App\Filament\Resources\Settings\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Storage;

class SettingInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('key')
                    ->label(__('filament/admin/setting_resource.fields.key'))
                    ->formatStateUsing(static function (?string $state): ?string {
                        if ($state === null || $state === '') {
                            return $state;
                        }

                        $translationKey = "filament/admin/setting_resource.keys.{$state}";

                        return Lang::has($translationKey) ? __($translationKey) : $state;
                    }),
                ImageEntry::make('value_image')
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
                            return asset('storage/س' . $normalized);
                        }

                        return asset($path);
                    }),
                TextEntry::make('value')
                    ->label(__('filament/admin/setting_resource.fields.value'))
                    ->getStateUsing(static function ($record): string {
                        if (in_array($record?->key, ['light_logo', 'dark_logo', 'favicon'], true)) {
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
                        $imageKeys = ['light_logo', 'dark_logo', 'favicon'];

                        if (in_array($key, $imageKeys, true)) {
                            $path = is_string($decoded) ? trim($decoded) : '';
                            if ($path === '') {
                                return '';
                            }

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

                            return "<img src=\"{$escapedUrl}\" alt=\"\" style=\"height:80px;max-width:220px;object-fit:contain;\" />";
                        }

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
                    }),
                TextEntry::make('created_at')
                    ->label(__('filament/admin/setting_resource.fields.created_at'))
                    ->dateTime(),
                TextEntry::make('updated_at')
                    ->label(__('filament/admin/setting_resource.fields.updated_at'))
                    ->dateTime(),
            ]);
    }
}
