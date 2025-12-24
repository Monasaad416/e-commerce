<?php

namespace App\Providers\Filament;

use App\Filament\Resources\Brands\Pages\CreateBrand;
use CraftForge\FilamentLanguageSwitcher\FilamentLanguageSwitcherPlugin;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use LaraZeus\SpatieTranslatable\SpatieTranslatablePlugin;
use Pest\Plugin;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {

    
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->colors([
                'primary' => Color::Slate[900],
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->brandLogo(asset('assets/imgs/logo/logo.jfif'))
            ->brandLogoHeight('50px')
            ->favicon(asset('assets/imgs/logo/logo.jfif'))
            ->plugins([
                FilamentLanguageSwitcherPlugin::make()
                    ->locales([
                        ['code' => 'en', 'name' => 'English', 'flag' => 'gb'],
                        ['code' => 'ar', 'name' => 'Arabic', 'flag' => 'sa'],
                    ]),
                    
            ])
            ->plugin(SpatieTranslatablePlugin::make()
                ->persist()
                ->defaultLocales(['en', 'ar']))
                ->navigationGroups([
                    NavigationGroup::make('administration')
                        ->label(fn () => __('general.administration'))
                        ->icon('heroicon-o-user-group'),
                ]);




    }
}
