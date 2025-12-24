<?php

namespace App\Filament\Resources\Attributes\Pages;

use App\Filament\Resources\Attributes\AttributeResource;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListAttributes extends ListRecords
{
    protected static string $resource = AttributeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->modalHeading(__('filament/admin/attribute_resource.create_attribute'))
                ->successNotification(
                    Notification::make()
                        ->title(__('filament/admin/attribute_resource.attribute_created_successfully'))
                        ->color('success')
                ),
        ];
    }
}
