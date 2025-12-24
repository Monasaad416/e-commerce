<?php

namespace App\Filament\Resources\Brands\Pages;

use App\Filament\Resources\Brands\BrandResource;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListBrands extends ListRecords
{
    protected static string $resource = BrandResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->modalHeading(__('filament/admin/brand_resource.create_brand'))
                ->createAnother(false)
                ->successNotification(
                    Notification::make()
                        ->title(__('filament/admin/brand_resource.brand_created_successfully'))
                        ->color('success')
                ),
        ];
    }
}
