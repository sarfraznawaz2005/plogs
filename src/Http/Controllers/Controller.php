<?php

namespace Sarfraznawaz2005\PLogs\Http\Controllers;

use Carbon\Carbon;
use Freshbitsweb\Laratables\Laratables;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\DB;
use Sarfraznawaz2005\PLogs\Models\Plog;

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
            DB::table('plogs')->where('created_at', '<=', Carbon::now()->subDays($days))->delete();
        }

        if (config('plogs.clean_log')) {
            file_put_contents(storage_path('logs/laravel.log'), '');
        }

        $levels = DB::table('plogs')->get();
        $levels = collect($levels)->pluck('level');

        if (!is_string($levels)) {
            $levels = $levels->unique();
        } else {
            $levels = (array)$levels;
        }


        $dates = DB::table('plogs')->get();
        $dates = collect($dates)->pluck('created_at');

        if (!is_string($dates)) {
            $dates = $dates->map(function ($date) {
                return date('Y-m-d', strtotime($date));
            })->unique();
        } else {
            $dates = (array)$dates;
        }

        return view('plogs::view', compact('levels', 'dates'));
    }

    public function table(Request $request)
    {
        $columns = [
            0 => 'level',
            1 => 'created_at',
            2 => 'message',
        ];

        $totalData = Plog::count();

        $totalFiltered = $totalData;

        $limit = $request->input('length');
        $start = $request->input('start');
        $order = $columns[$request->input('order.0.column')];
        $dir = $request->input('order.0.dir');

        if (empty($request->input('search.value'))) {

            // all by default
            $entries = Plog::offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();

            // first see if this is filter request
            $cols = $request->all()['columns'];
            $q = Plog::query();
            $isFilter = false;

            foreach ($cols as $col) {
                if ($query = $col['search']['value']) {
                    $field = $col['data'];
                    $isFilter = true;

                    $q->where($field, 'LIKE', "%{$query}%");
                }
            }

            if ($isFilter) {
                $totalFiltered = $q->count();

                $entries = $q
                    ->orderBy($order, $dir)
                    ->offset($start)
                    ->limit($limit)
                    ->get();
            }

        } else {
            $search = $request->input('search.value');

            $entries = Plog::where('message', 'LIKE', "%{$search}%")
                ->orWhere('stack', 'LIKE', "%{$search}%")
                ->offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();

            $totalFiltered = Plog::where('message', 'LIKE', "%{$search}%")
                ->orWhere('stack', 'LIKE', "%{$search}%")
                ->count();
        }

        $data = [];

        if (!empty($entries)) {
            foreach ($entries as $key => $entry) {
                $date = $entry->created_at;

                $nestedData['created_at'] = <<< HTML
                <div align="center" class="date toggleDetails" data-display="stack$key">
                    $date
                </div>
HTML;

                $level = ucfirst($entry->level);

                $nestedData['level'] = <<< HTML
                <div align="center" class="toggleDetails" data-display="stack$key">
                    <span class="label label-{$entry->level_class}">
                        <span class="glyphicon glyphicon-{$entry->level_img}-sign"></span>
                        &nbsp;$level
                    </span>
                </div>
HTML;

                $message = $entry->message ?: '';
                $stack = trim($entry->stack);

                if ($stack) {
                    $nestedData['message'] = <<< HTML
                    <a class="pull-right toggleDetails btn btn-success btn-xs"
                       data-display="stack$key">
                        <span class="glyphicon glyphicon-zoom-in"></span>
                    </a>

                    <span class="toggleDetails" data-display="stack$key">$message</span>                    
                    
                    <div class="stack toggleDetails" data-display="stack$key" id="stack$key"
                         style="display: none;">$stack
                    </div>
HTML;
                } else {
                    $nestedData['message'] = $message;
                }

                $nestedData['message'] = '<div class="text">' . $nestedData['message'] . '</div>';

                $data[] = $nestedData;
            }
        }

        return [
            'draw' => (int)$request->input('draw'),
            'recordsTotal' => (int)$totalData,
            'recordsFiltered' => (int)$totalFiltered,
            'data' => $data
        ];
    }
}
