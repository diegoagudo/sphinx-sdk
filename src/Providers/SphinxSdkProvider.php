<?php

namespace Plataforma13\SphinxSdk\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Plataforma13\SphinxSdk\Services\CodeChallengeService;
use Plataforma13\SphinxSdk\Services\CodeStateService;
use Plataforma13\SphinxSdk\Services\SessionService;
use Plataforma13\SphinxSdk\SphinxSdk;

class SphinxSdkProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(CodeStateService::class, function ($app) {
            return new CodeStateService(
                $app->make(SessionService::class),
            );
        });

        $this->app->bind(SphinxSdk::class, function ($app) {
            return new SphinxSdk(
                $app->make(CodeStateService::class),
                $app->make(CodeChallengeService::class)
            );
        });

        $this->app->make(
            'Plataforma13\SphinxSdk\Http\Controllers\SphinxController'
        );
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        Route::get(
            'auth/callback',
            [
                'middleware' => 'web',
                'uses'       => 'Plataforma13\SphinxSdk\Http\Controllers\SphinxController@callback',
                'as'         => 'sphinxsdk.callback',
            ]
        );
    }
}
