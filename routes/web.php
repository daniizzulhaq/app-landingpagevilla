<?php
use App\Http\Controllers\{HomeController, AboutController, ServiceController, PropertyController, PortfolioController, ContactController, PageController, SeoController};
use App\Http\Controllers\Admin;
use App\Support\Resources;
use Illuminate\Support\Facades\{Route, Storage};

Route::get('/media/{path}', function (string $path) {
    abort_unless(preg_match('~^images/[a-f0-9-]{36}\.webp$~', $path) && Storage::disk('public')->exists($path), 404);
    return Storage::disk('public')->response($path, null, ['Content-Type' => 'image/webp', 'Cache-Control' => 'public, max-age=31536000, immutable', 'X-Content-Type-Options' => 'nosniff']);
})->where('path', 'images/[a-f0-9-]+\.webp')->name('media');

Route::middleware(\App\Http\Middleware\SlugRedirect::class)->group(function () {
    Route::get('/', HomeController::class)->name('home');
    Route::get('/about-us', AboutController::class)->name('about');
    Route::get('/company-profile', \App\Http\Controllers\CompanyProfileController::class)->name('company.profile');
    Route::get('/favorites', [\App\Http\Controllers\PropertySelectionController::class,'favorites'])->name('favorites');
    Route::get('/compare', [\App\Http\Controllers\PropertySelectionController::class,'compare'])->name('compare');
    Route::get('/property-selection', [\App\Http\Controllers\PropertySelectionController::class,'data'])->middleware('throttle:90,1')->name('property-selection');
    Route::get('/services', [ServiceController::class, 'index'])->name('services.index');
    Route::get('/services/{slug}', [ServiceController::class, 'show'])->name('services.show');
    foreach (['invest-build', 'list-sell-rent', 'property-management'] as $slug) { Route::redirect('/'.$slug, '/services/'.$slug, 301); }
    Route::redirect('/investment', '/services/invest-build', 301);
    Route::get('/buy-rent', [PropertyController::class, 'index'])->name('buy-rent');
    Route::get('/properties', [PropertyController::class, 'index'])->name('properties.index');
    Route::get('/properties/{slug}', [PropertyController::class, 'show'])->name('properties.show');
    Route::get('/portfolio', [PortfolioController::class, 'index'])->name('portfolio.index');
    Route::get('/portfolio/{slug}', [PortfolioController::class, 'show'])->name('portfolio.show');
    Route::get('/contact', ContactController::class)->name('contact');
    Route::get('/sitemap.xml', [SeoController::class, 'sitemap'])->name('sitemap');
    Route::get('/robots.txt', [SeoController::class, 'robots']);
});

Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('/login', [Admin\AuthController::class, 'create'])->name('login');
        Route::post('/login', [Admin\AuthController::class, 'store'])->middleware('throttle:10,1')->name('login.store');
        Route::get('/forgot-password', [Admin\AuthController::class, 'forgot'])->name('password.request');
        Route::post('/forgot-password', [Admin\AuthController::class, 'email'])->middleware('throttle:3,1')->name('password.email');
        Route::post('/reset-password', [Admin\AuthController::class, 'updatePassword'])->middleware('throttle:5,1')->name('password.update');
    });
    Route::middleware(['auth', 'can:access-admin'])->group(function () {
        Route::get('/', Admin\DashboardController::class)->name('dashboard');
        Route::post('/logout', [Admin\AuthController::class, 'destroy'])->name('logout');
        Route::resource('properties', Admin\PropertyController::class)->except('show');
        foreach (Resources::all() as $resource => [$model, $title]) {
            $controller = 'App\\Http\\Controllers\\Admin\\'.class_basename($model).'Controller';
            Route::get('/'.$resource, [$controller, 'index'])->defaults('resource', $resource)->name($resource.'.index');
            Route::get('/'.$resource.'/create', [$controller, 'create'])->defaults('resource', $resource)->name($resource.'.create');
            Route::post('/'.$resource, [$controller, 'store'])->defaults('resource', $resource)->name($resource.'.store');
            Route::get('/'.$resource.'/{record}/edit', [$controller, 'edit'])->whereNumber('record')->defaults('resource', $resource)->name($resource.'.edit');
            Route::put('/'.$resource.'/{record}', [$controller, 'update'])->whereNumber('record')->defaults('resource', $resource)->name($resource.'.update');
            Route::delete('/'.$resource.'/{record}', [$controller, 'destroy'])->whereNumber('record')->defaults('resource', $resource)->name($resource.'.destroy');
        }
        Route::get('/settings/{group?}', [Admin\WebsiteSettingController::class, 'edit'])->where('group', 'company|homepage|seo|company-id|homepage-id|seo-id')->name('settings.edit');
        Route::put('/settings/{group?}', [Admin\WebsiteSettingController::class, 'update'])->where('group', 'company|homepage|seo|company-id|homepage-id|seo-id')->name('settings.update');
        Route::get('/profile', [Admin\ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profile', [Admin\ProfileController::class, 'update'])->name('profile.update');
    });
});
Route::get('/admin/reset-password/{token}', [Admin\AuthController::class, 'reset'])->middleware('guest')->name('password.reset');
Route::get('/{slug}', [PageController::class, 'show'])->middleware(\App\Http\Middleware\SlugRedirect::class)->where('slug', '[a-z0-9-]+')->name('pages.show');
