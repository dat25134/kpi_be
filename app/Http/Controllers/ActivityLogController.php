<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;
use App\Http\Resources\ActivityLogResource;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $query = Activity::query();

        if ($request->filled('causer_id')) {
            $query->where('causer_id', $request->causer_id);
        }
        if ($request->filled('causer_type')) {
            $query->where('causer_type', $request->causer_type);
        }
        if ($request->filled('subject_type')) {
            $query->where('subject_type', $request->subject_type);
        }
        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }
        if ($request->filled('event')) {
            $query->where('event', $request->event);
        }
        if ($request->filled('log_name')) {
            $query->where('log_name', $request->log_name);
        }
        if ($request->filled('description')) {
            $query->where('description', 'like', '%'.$request->description.'%');
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $logs = $query->latest()->paginate($request->get('per_page', 20));
        return response()->json([
            'success' => true, 
            'message' => 'Lấy danh sách log thành công',
            'data' => [
                'logs' => ActivityLogResource::collection($logs),
                'pagination' => [
                    'currentPage' => $logs->currentPage(),
                    'totalPages' => $logs->lastPage(),
                    'totalItems' => $logs->total(),
                    'itemsPerPage' => $logs->perPage(),
                    'create_count' => Activity::where('event', 'created')->count(),
                    'update_count' => Activity::where('event', 'updated')->count(),
                    'delete_count' => Activity::where('event', 'deleted')->count(),
                ],
            ],
        ]);
    }
} 