<?php

namespace App\Filament\Traits;

trait RedirectsToIndex
{
    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}
