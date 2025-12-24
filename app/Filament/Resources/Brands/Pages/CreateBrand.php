<?php

namespace App\Filament\Resources\Brands\Pages;

use App\Filament\Resources\Brands\BrandResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateBrand extends CreateRecord
{
    protected static string $resource = BrandResource::class;

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->title(__('filament/admin/brand_resource.brand_created_successfully'))
            ->success()
            ->color('success') 
            ->send();
    }



}
