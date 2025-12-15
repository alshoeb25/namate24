<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\Tutor;
use App\Models\Review;
use App\Models\PayoutRequest;
use App\Models\StudentRequirement;
use App\Policies\TutorPolicy;
use App\Policies\ReviewPolicy;
use App\Policies\PayoutRequestPolicy;
use App\Policies\RequirementPolicy;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Tutor::class => TutorPolicy::class,
        Review::class => ReviewPolicy::class,
        PayoutRequest::class => PayoutRequestPolicy::class,
        StudentRequirement::class => RequirementPolicy::class,
    ];

    public function boot()
    {
        $this->registerPolicies();

        // extra gates if needed
        Gate::define('moderate-tutors', fn($user) => $user->hasRole('admin'));
    }
}