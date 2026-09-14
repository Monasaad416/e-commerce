<?php

namespace App\Filament\Resources\ShopPageContents\Pages;

use App\Filament\Resources\ShopPageContents\ShopPageContentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListShopPageContents extends ListRecords
{
    protected static string $resource = ShopPageContentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
