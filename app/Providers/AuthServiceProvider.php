<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        'App\Post' => 'App\Policies\PostPolicy',
        'App\Workshop' => 'App\Policies\WorkshopPolicy',
        'App\Survey' => 'App\Policies\SurveyPolicy',
        'App\Email' => 'App\Policies\EmailPolicy',
        'App\Course' => 'App\Policies\CoursePolicy'
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }
}
