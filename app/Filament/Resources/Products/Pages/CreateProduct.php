<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use App\Filament\Traits\RedirectsToIndex;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateProduct extends CreateRecord
{
    use RedirectsToIndex;
    protected static string $resource = ProductResource::class;
    protected static bool $canCreateAnother = false;
    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->title(__('filament/admin/product_resource.product_created_successfully'))
            ->success()
            ->color('success') 
            ->send();
    }
}
