<?php

namespace App\Filament\Resources\HomePageContents\Pages;

use App\Filament\Resources\HomePageContents\HomePageContentResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewHomePageContent extends ViewRecord
{
    protected static string $resource = HomePageContentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
