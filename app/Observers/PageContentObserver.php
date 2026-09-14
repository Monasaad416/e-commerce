<?php

namespace App\Observers;

use App\Models\PageContent;
use Illuminate\Support\Facades\Cache;

class PageContentObserver
{
    public function updated(PageContent $pageContent): void
    {
            \Log::info('PageContentObserver: updated triggered', ['page' => $pageContent->page]);
        $locales = ['en', 'ar']; 

        foreach ($locales as $locale) {
            Cache::forget("page_content_{$locale}_{$pageContent->page}");
        }
    }

    public function deleted(PageContent $pageContent): void
    {
        $locales = ['en', 'ar'];

        foreach ($locales as $locale) {
            Cache::forget("page_content_{$locale}_{$pageContent->page}");
        }
    }
}
