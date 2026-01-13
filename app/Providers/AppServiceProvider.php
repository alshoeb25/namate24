<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Tutor;
use App\Models\StudentRequirement;
use App\Observers\TutorObserver;
use App\Observers\RequirementObserver;

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
        // Register authorization policies
        \Illuminate\Support\Facades\Gate::policy(
            \App\Models\Tutor::class,
            \App\Policies\TutorPolicy::class
        );
        \Illuminate\Support\Facades\Gate::policy(
            \App\Models\Student::class,
            \App\Policies\StudentPolicy::class
        );
        \Illuminate\Support\Facades\Gate::policy(
            \App\Models\StudentRequirement::class,
            \App\Policies\StudentRequirementPolicy::class
        );
        \Illuminate\Support\Facades\Gate::policy(
            \App\Models\CoinTransaction::class,
            \App\Policies\CoinTransactionPolicy::class
        );
        \Illuminate\Support\Facades\Gate::policy(
            \App\Models\Review::class,
            \App\Policies\ReviewPolicy::class
        );
        \Illuminate\Support\Facades\Gate::policy(
            \App\Models\Subject::class,
            \App\Policies\SubjectPolicy::class
        );

        // Define authorization gates
        \Illuminate\Support\Facades\Gate::define('access-admin-panel', function (\App\Models\User $user) {
            return $user->hasRole('admin');
        });

        // Register Elasticsearch observers for automatic sync
        Tutor::observe(TutorObserver::class);
        StudentRequirement::observe(RequirementObserver::class);
    }
}
