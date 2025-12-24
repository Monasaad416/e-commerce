<?php

namespace App\Filament\Resources\Attributes\Pages;

use App\Filament\Resources\Attributes\AttributeResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAttribute extends CreateRecord
{
    protected static bool $canCreateAnother = false;
    protected static string $resource = AttributeResource::class;
}
