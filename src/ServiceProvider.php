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

        $versionArray = explode('.', app()->version());
        $version = $versionArray[0] . $versionArray[1];

        if ($version < 54) {
            Event::listen('illuminate.log', function ($level, $message, $context) {
                $this->saveRecord($level, $message, $context);
            });
        } else {
            Event::listen(MessageLogged::class, function (MessageLogged $e) {
                $this->saveRecord($e->level, $e->message, $e->context);
            });
        }
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

    protected function saveRecord($level, $message, $context)
    {
        if (!config('plogs.enabled')) {
            return false;
        }

        $levels = config('plogs.levels');

        $stack = '';
        $levelClass = self::$levelsClasses[$level];
        $levelImg = self::$levelsImgs[$level];

        if ($context) {
            $errorObject = collect($context)->first();

            if ($errorObject && $errorObject instanceof \Exception) {
                $stack = $errorObject->getTraceAsString();
            }
        }

        $extraInfo = $this->logExtrainfo();

        if (strpos($stack, "\n")) {
            $stack = preg_replace("/\n/", "\n" . $extraInfo . "\n\n", $stack, 1);
        } else {
            $stack .= $extraInfo . "\n\n";
        }

        if (\in_array('all', $levels, true) || \in_array($level, $levels, true)) {
            if ($message) {
                DB::table('plogs')->insert([
                    'level' => $level,
                    'message' => $message,
                    'stack' => trim($stack),
                    'level_class' => $levelClass,
                    'level_img' => $levelImg,
                    'created_at' => Carbon::now()
                ]);
            }
        }
    }

    protected function logExtrainfo()
    {
        $info = '';

        if (isset($_SERVER['REMOTE_ADDR'])) {
            $info .= 'IP: ' . $_SERVER['REMOTE_ADDR'];
        }

        if (isset($_SERVER['REQUEST_URI'])) {
            $info .= "\n" . $_SERVER['REQUEST_METHOD'] . ' ' . url($_SERVER['REQUEST_URI']);
        }

        if (isset($_SERVER['HTTP_REFERER'])) {
            $info .= "\nReferer: " . $_SERVER['HTTP_REFERER'];
        }

        if (\Auth::check()) {
            $info .= "\n" . 'User:' . \Auth::user()->id . ' (' . \Auth::user()->email . ')';
        }

        if ($info) {
            $dots = str_repeat('=', 50);

            $info = "\n$dots\n$info\n$dots";
        }

        return $info;
    }
}
