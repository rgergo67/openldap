<?php

namespace Rgergo67\Openldap;

use Illuminate\Support\ServiceProvider;

class OpenldapServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadTranslationsFrom(__DIR__.'/resources/lang', 'openldap');
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->publishes([
            __DIR__.'/config/openldap.php' => config_path('openldap.php'),
        ]);
    }
}
