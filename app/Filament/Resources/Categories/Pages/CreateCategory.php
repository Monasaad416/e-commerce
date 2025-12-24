<?php

namespace App\Filament\Resources\Categories\Pages;

use App\Filament\Resources\Categories\CategoryResource;
use App\Filament\Traits\RedirectsToIndex;

use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreateCategory extends CreateRecord
{
    use RedirectsToIndex;
    protected static string $resource = CategoryResource::class;
    protected static bool $canCreateAnother = false;

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->title(__('filament/admin/category_resource.category_created_successfully'))
            ->success()
            ->color('success') 
            ->send();
    }


}
