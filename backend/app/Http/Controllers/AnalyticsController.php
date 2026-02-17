<?php

namespace App\Http\Controllers;

use App\Models\Request as RequestModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        if (!$user || !in_array($user->role, ['admin', 'editor'], true)) {
            return response()->json(['error' => 'Unauthorized. Admin or Editor role required.'], 403);
        }

        $total = RequestModel::count();
        $pending = RequestModel::where('status', 'Under Review')->orWhere('status', 'Submitted')->count();
        $approved = RequestModel::where('status', 'Approved')->count();
        $rejected = RequestModel::where('status', 'Rejected')->count();

        // Monthly Trends (MySQL)
        $monthly = RequestModel::select(
            DB::raw('count(id) as count'),
            DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month_name")
        )
            ->groupBy('month_name')
            ->orderBy('month_name', 'asc')
            ->limit(6)
            ->get();

        $monthlyTrends = $monthly->map(function ($row) {
            return ['month' => $row->month_name, 'count' => $row->count];
        });

        // Status Distribution
        $distribution = RequestModel::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get()
            ->map(function ($row) {
                return ['status' => $row->status, 'count' => $row->count];
            });

        return response()->json([
            'summary' => [
                'total' => $total,
                'pending' => $pending,
                'approved' => $approved,
                'rejected' => $rejected
            ],
            'monthlyTrends' => $monthlyTrends,
            'statusDistribution' => $distribution
        ]);
    }
}
