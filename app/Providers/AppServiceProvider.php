<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->scoped(\App\Services\Settings::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->isProduction()) { \Illuminate\Support\Facades\URL::forceScheme('https'); }
        \Illuminate\Support\Facades\Gate::define('access-admin', fn ($user) => $user->canAccessAdministration());
        foreach (['Property', 'Portfolio', 'Service', 'Testimonial', 'Page', 'Location', 'PropertyType', 'PortfolioCategory', 'Amenity', 'WebsiteSetting', 'TeamMember'] as $model) {
            \Illuminate\Support\Facades\Gate::policy('App\\Models\\'.$model, \App\Policies\AdminContentPolicy::class);
        }
        \Illuminate\Support\Facades\View::composer(['layouts.public', 'components.*', 'public.*'], function ($view) {
            $view->with('settings', app(\App\Services\Settings::class))->with('whatsapp', app(\App\Services\WhatsAppService::class));
        });
    }
}
