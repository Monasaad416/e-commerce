<?php

namespace App\Providers;

use AbdulmajeedJamaan\FilamentTranslatableTabs\TranslatableTabs;
use App\Models\PageContent;
use App\Observers\PageContentObserver;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
        {
            RateLimiter::for('api', function (Request $request): Limit {
                return Limit::perMinute(120)->by($request->user()?->id ?: $request->ip());
            });

            TranslatableTabs::configureUsing(function (TranslatableTabs $component) {
                $component
                    // locales labels
                    ->localesLabels([
                        'ar' => __('عربي'),
                        'en' => __('English')
                    ])
                    // default locales
                    ->locales(['ar', 'en']);
            });


            PageContent::observe(PageContentObserver::class);
    }
}
