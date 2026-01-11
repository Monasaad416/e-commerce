<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class MyCustomDashboard extends BaseDashboard
{
    protected static string $routePath = '/dashboard';
    
    //protected string $view = 'filament.pages.my-custom-dashboard';

    public function getColumns(): int | array
    {
        return 4;
    }
}
