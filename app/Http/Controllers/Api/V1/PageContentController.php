<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\PageContent;
use App\Helpers\ApiResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Cache;
use Throwable;

class PageContentController extends Controller
{
    public function __invoke(string $locale = 'en', string $page)
    {
        try {
            app()->setLocale($locale);

            $content = Cache::remember(
                "page_content_{$locale}_{$page}",
                now()->addHours(6),
                fn () => PageContent::where('page', $page)->firstOrFail()
            );

            //var_dump(Cache::get("page_content_en_home"));

            return ApiResponse::success(
                $content->content,
                __('general.request_sent_successfully'),
                200
            );

        } catch (ModelNotFoundException $e) {
            return ApiResponse::error(
                __('general.not_found'),
                404
            );

        } catch (Throwable $e) {
            return ApiResponse::error(
                __('general.an_error_occurred'),
                500
            );
        }
    }
}
