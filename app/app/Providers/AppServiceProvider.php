<?php

namespace App\Providers;

use App\Policies\ActivityLogPolicy;
use enshrined\svgSanitize\Sanitizer as SvgSanitizer;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Spatie\Activitylog\Models\Activity;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(SvgSanitizer::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Activity::class, ActivityLogPolicy::class);
    }
}
