<?php

namespace App\Filament\Resources\Categories\Pages;

use App\Filament\Resources\Categories\CategoryResource;
use App\Filament\Traits\RedirectsToIndex;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditCategory extends EditRecord
{
    use RedirectsToIndex;
    protected static string $resource = CategoryResource::class;

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
            ->title(__('filament/admin/category_resource.category_updated_successfully'))
            ->success()
            ->color('success') 
            ->send();
    }
}
