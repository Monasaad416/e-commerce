<?php

namespace App\Filament\Resources\Tags\Pages;

use App\Filament\Resources\Tags\TagResource;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListTags extends ListRecords
{
    protected static string $resource = TagResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->modalHeading(__('filament/admin/tag_resource.create_tag'))
                ->successNotification(
                    Notification::make()
                        ->title(__('filament/admin/tag_resource.tag_created_successfully'))
                        ->color('success')
                ),
        ];
    }
}
