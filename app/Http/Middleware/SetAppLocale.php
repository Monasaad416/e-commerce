<?php

namespace App\Http\Middleware;

use App\Models\Language;
use Closure;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;

class SetAppLocale
{
    public function handle($request, Closure $next)
    {
        $defaultLocale = Cache::remember('default_language_code', 3600, static function (): ?string {
            return Language::where('is_default', 1)->value('code');
        });

        $locale = $request->route('locale') ?? $defaultLocale ?? config('app.locale', 'en');

        $language = Language::where('code', $locale)->first();

        if (!$language) {
            return response()->json(['message' => 'Invalid language code'], 400);
        }


        App::setLocale($locale);


        //Session::put('lang_id', $language->id);

        return $next($request);
    }

}
