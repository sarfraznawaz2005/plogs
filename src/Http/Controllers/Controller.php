<?php

namespace Sarfraznawaz2005\PLogs\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\DB;

class Controller extends BaseController
{
    public function __construct()
    {
        if (config('plogs.http_authentication')) {
            $this->middleware('auth.basic');
        }
    }

    /**
     * Displays all visitor information in table.
     */
    public function index()
    {
        // delete old records
        $days = config('plogs.delete_old_days');

        if ($days) {
            DB::table('plogs')->where('created_at', '<', Carbon::now()->subDays($days))->delete();
        }

        if (config('plogs.clean_log')) {
            file_put_contents(storage_path('logs/laravel.log'), '');
        }

        // get all records
        $logs = DB::table('plogs')->get();

        return view('plogs::view', compact('logs'));
    }
}
