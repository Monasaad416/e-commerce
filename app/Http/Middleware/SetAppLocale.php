<?php

namespace App\Http\Middleware;

use App\Models\Language;
use Closure;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class SetAppLocale
{
    public function handle($request, Closure $next)
    {
        $locale = $request->route('lang') ?? Language::where('is_default', 1)->value('code');

        $language = Language::where('code', $locale)->first();

        if (!$language) {
            return response()->json(['message' => 'Invalid language code'], 400);
        }


        App::setLocale($locale);


        Session::put('lang_id', $language->id);

        return $next($request);
    }

}