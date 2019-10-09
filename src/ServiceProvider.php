<?php

namespace Sarfraznawaz2005\PLogs;

use Carbon\Carbon;
use Illuminate\Log\Events\MessageLogged;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Symfony\Component\Debug\Exception\FlattenException;
use Symfony\Component\Debug\ExceptionHandler as SymfonyExceptionHandler;

class ServiceProvider extends BaseServiceProvider
{
    private static $levelsClasses = [
        'debug' => 'default',
        'info' => 'primary',
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
        $errorObject = null;
        $levelClass = self::$levelsClasses[$level];
        $levelImg = self::$levelsImgs[$level];

        if ($context) {
            if (isset($context['exception'])) {
                $errorObject = $context['exception'];
            } else {
                $errorObject = collect($context)->first();
            }
        }

        if (is_object($message)) {
            $errorObject = $message;
            $message = $message->getMessage();
        }

        if (is_object($errorObject)) {
            $e = FlattenException::create($errorObject);
            $handler = new SymfonyExceptionHandler();
            $stack = $handler->getHtml($e);
        }

        if (config('plogs.extra_info')) {
            $stack = $this->logExtrainfo() . $stack;
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
            $info .= 'IP: ' . $_SERVER['REMOTE_ADDR'] . '<br>';
        }

        if (isset($_SERVER['REQUEST_URI'])) {
            $info .= $_SERVER['REQUEST_METHOD'] . ' ' . url($_SERVER['REQUEST_URI']) . '<br>';
        }

        if (isset($_SERVER['HTTP_REFERER'])) {
            $info .= "Referer: " . $_SERVER['HTTP_REFERER'] . '<br>';
        }

        if (\Auth::check()) {
            $info .= 'User:' . \Auth::user()->id . ' (' . \Auth::user()->email . ')' . '<br>';
        }

        if ($info) {
            $dots = str_repeat('-', 150) . '<br>';

            $info = $dots . $info . $dots;
        }

        return "<div>$info</div><br>";
    }
}
