<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index()
    {
        $logs = AuditLog::with('user')->orderBy('created_at', 'desc')->take(100)->get();

        $mapped = $logs->map(function ($log) {
            return [
                'id' => $log->id,
                'action' => $log->action,
                'details' => $log->details,
                'ipAddress' => $log->ip_address,
                'adminName' => $log->user ? $log->user->name : 'System',
                'createdAt' => $log->created_at
            ];
        });

        return response()->json($mapped);
    }
}
