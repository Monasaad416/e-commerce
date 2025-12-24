<?php

namespace App\Filament\Resources\Admins\Pages;

use App\Filament\Resources\Admins\AdminResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateAdmin extends CreateRecord
{
    protected static string $resource = AdminResource::class;


    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->title(__('filament/admin/admin_resource.admin_created_successfully'))
            ->success()
            ->color('success') 
            ->send();
    }
}
