<?php

use App\Http\Controllers\CompressImageController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\SeoController;
use App\Http\Controllers\ToolController;
use App\Tools\ToolRegistry;
use Illuminate\Support\Facades\Route;

Route::get('/', [ToolController::class, 'show'])
    ->defaults('tool', config('tools.primary'))
    ->name('home');

Route::prefix(config('tools.prefix'))->group(function () {
    $slugs = ToolRegistry::routeSlugs();

    foreach (array_diff(config('tools.pages'), [config('tools.primary')]) as $tool) {
        Route::get($slugs[$tool] ?? $tool, [ToolController::class, 'show'])
            ->defaults('tool', $tool)
            ->name("tools.{$tool}");
    }

    // Tools added in the admin, resolved from the database per request.
    Route::get('{slug}', [ToolController::class, 'showCustom'])
        ->where('slug', '[a-z0-9]+(?:-[a-z0-9]+)*')
        ->name('tools.custom');
});

Route::post('process/compress', CompressImageController::class)
    ->middleware('throttle:compress')
    ->name('process.compress');

Route::get('about-us', [PageController::class, 'about'])->name('pages.about');
Route::get('editorial-policy', [PageController::class, 'editorial'])->name('pages.editorial');
Route::get('faq', [PageController::class, 'faq'])->name('pages.faq');

foreach (array_keys(PageController::LEGAL_PAGES) as $page) {
    Route::get($page, [PageController::class, 'legal'])
        ->defaults('page', $page)
        ->name("pages.{$page}");
}

Route::get('contact-us', [ContactController::class, 'show'])->name('pages.contact');
Route::post('contact-us', [ContactController::class, 'store'])
    ->middleware('throttle:contact')
    ->name('pages.contact.store');

Route::get('sitemap.xml', [SeoController::class, 'sitemap'])->name('seo.sitemap');
Route::get('robots.txt', [SeoController::class, 'robots'])->name('seo.robots');
Route::get('llms.txt', [SeoController::class, 'llms'])->name('seo.llms');
Route::get('site.webmanifest', [SeoController::class, 'manifest'])->name('seo.manifest');

/*
| Language versions of tools, created as child tools in the admin: /{locale} for the homepage, /{locale}/{slug} otherwise.
| Resolved from the database per request, so new languages and slug changes need no route cache refresh.
*/
Route::get('{locale}/{slug?}', [ToolController::class, 'showLocalized'])
    ->whereIn('locale', array_keys(config('tools.languages')))
    ->where('slug', '[a-z0-9]+(?:-[a-z0-9]+)*')
    ->name('tools.localized');
