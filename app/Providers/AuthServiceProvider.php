<?php

namespace App\Providers;

use App\Photo;
use App\User;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        // 'App\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();
        //
        Gate::define('access-to-photo', function (User $user, $photo_id) {
            /** @var Photo $photo */
            $photo = Photo::with('users_via_sharing')->find($photo_id);
            return ($photo->getAllAlowedUsers()->contains($user->id));
        });

    }
}
