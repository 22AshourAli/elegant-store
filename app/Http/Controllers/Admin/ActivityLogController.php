<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;

class ActivityLogController extends Controller
{
    public function index()
    {
        $query = ActivityLog::with('user')->latest();

        if (request()->filled('module')) {
            $query->module(request('module'));
        }

        if (request()->filled('action')) {
            $query->action(request('action'));
        }

        $logs = $query->paginate(30);

        $modules = \App\Enums\ActivityAction::modules();
        $actions = \App\Enums\ActivityAction::values();

        return view('admin.activity-logs.index', compact('logs', 'modules', 'actions'));
    }
}
