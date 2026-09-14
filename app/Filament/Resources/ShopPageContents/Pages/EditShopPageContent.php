<?php

namespace App\Filament\Resources\ShopPageContents\Pages;

use App\Filament\Resources\ShopPageContents\ShopPageContentResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditShopPageContent extends EditRecord
{
    protected static string $resource = ShopPageContentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
