<?php

namespace Sarfraznawaz2005\PLogs;

use Carbon\Carbon;
use Illuminate\Log\Events\MessageLogged;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    private static $levelsClasses = [
        'debug' => 'info',
        'info' => 'info',
        'notice' => 'info',
        'warning' => 'warning',
        'error' => 'danger',
        'critical' => 'danger',
        'alert' => 'danger',
        'emergency' => 'danger',
        'processed' => 'success',
    ];

    private static $levelsImgs = [
        'debug' => 'info',
        'info' => 'info',
        'notice' => 'info',
        'warning' => 'warning',
        'error' => 'warning',
        'critical' => 'warning',
        'alert' => 'warning',
        'emergency' => 'warning',
        'processed' => 'info'
    ];

    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        // routes
        if (!$this->app->routesAreCached()) {
            require __DIR__ . '/Http/routes.php';
        }

        // views
        $this->loadViewsFrom(__DIR__ . '/Views', 'plogs');

        // Publishing is only necessary when using the CLI.
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/Config/config.php' => config_path('plogs.php'),
                __DIR__ . '/Views' => base_path('resources/views/vendor/plogs'),
                __DIR__ . '/Migrations' => database_path('migrations')
            ], 'plogs.config');
        }
    }

    /**
     * Register package services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/Config/config.php', 'plogs');

        // register event handler
        Event::listen(MessageLogged::class, function (MessageLogged $e) {

            if (!config('plogs.enabled')) {
                return false;
            }

            $levels = config('plogs.levels');

            if (\in_array('all', $levels, true) || \in_array($e->level, $levels, true)) {

                $stack = '';
                $level = $e->level;
                $message = $e->message;
                $levelClass = self::$levelsClasses[$level];
                $levelImg = self::$levelsImgs[$level];

                if ($e->context) {
                    $errorObject = collect($e->context)->first();

                    if ($errorObject && $errorObject instanceof \Exception) {
                        $stack = $errorObject->getTraceAsString();
                    }
                }

                DB::table('plogs')->insert([
                    'level' => $level,
                    'message' => $message,
                    'stack' => $stack,
                    'level_class' => $levelClass,
                    'level_img' => $levelImg,
                    'created_at' => Carbon::now()
                ]);

            }

        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['plogs'];
    }
}
