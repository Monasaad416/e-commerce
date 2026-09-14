<?php

namespace App\Filament\Resources\HomePageContents\Pages;

use Filament\Actions\ViewAction;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;
use App\Filament\Resources\HomePageContents\HomePageContentResource;

class EditHomePageContent extends EditRecord
{
    protected static string $resource = HomePageContentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->title(__('filament/admin/page_content_resource.page_content_updated_successfully'))
            ->success()
            ->color('success')
            ->send();
    }
}
