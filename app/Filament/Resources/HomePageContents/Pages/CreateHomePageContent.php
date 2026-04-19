<?php

namespace App\Filament\Resources\HomePageContents\Pages;

use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\HomePageContents\HomePageContentResource;

class CreateHomePageContent extends CreateRecord
{
    protected static string $resource = HomePageContentResource::class;

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->title(__('filament/admin/page_content_resource.page_content_created_successfully'))
            ->success()
            ->color('success')
            ->send();
    }
}
