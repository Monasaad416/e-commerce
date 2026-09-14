<?php

namespace App\Filament\Resources\ShopPageContents\Pages;

use App\Filament\Resources\ShopPageContents\ShopPageContentResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewShopPageContent extends ViewRecord
{
    protected static string $resource = ShopPageContentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
