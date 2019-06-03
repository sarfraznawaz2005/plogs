<?php

Route::group(
    [
        'namespace' => 'Sarfraznawaz2005\PLogs\Http\Controllers',
        'prefix' => config('plogs.route', 'plogs')
    ],
    function () {
        Route::get('/', 'Controller@index');
        Route::get('/table', 'Controller@table')->name('__plogstable__');
    }
);

