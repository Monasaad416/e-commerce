<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use App\Filament\Traits\RedirectsToIndex;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditProduct extends EditRecord
{
    use RedirectsToIndex;
    protected static string $resource = ProductResource::class;

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
            ->title(__('filament/admin/product_resource.product_edited_successfully'))
            ->success()
            ->color('success') 
            ->send();
    }
}
