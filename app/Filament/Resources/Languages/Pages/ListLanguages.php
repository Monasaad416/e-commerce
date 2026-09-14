<?php

namespace App\Filament\Resources\Languages\Pages;

use App\Filament\Resources\Languages\LanguageResource;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListLanguages extends ListRecords
{
    protected static string $resource = LanguageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->modalHeading(__('filament/admin/language_resource/create'))
                ->successNotification(
                    Notification::make()
                        ->title(__('filament/admin/language_resource/language_created_successfully'))
                        ->color('success')
                ),
        ];
    }
}
