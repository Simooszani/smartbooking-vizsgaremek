<?php

namespace App\Http\Controllers;

use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    // Hotel admin creates a report
    public function store(Request $request)
    {
        $request->validate([
            'reported_user_id' => 'required|exists:users,id',
            'reason' => 'required|string',
            'description' => 'nullable|string|max:1000',
        ]);

        $user = Auth::user();

        if (!$user->isHotelAdmin() && !$user->isAdmin()) {
            return response()->json(['message' => 'Nincs jogosultságod!'], 403);
        }

        $report = Report::create([
            'reporter_id' => $user->id,
            'reported_user_id' => $request->reported_user_id,
            'hotel_id' => $user->managed_hotel_id ?? 0,
            'reason' => $request->reason,
            'description' => $request->description,
        ]);

        $report->load(['reporter:id,name', 'reportedUser:id,name,email', 'hotel:id,name']);

        return response()->json($report, 201);
    }

    // Admin: list all reports
    public function index()
    {
        $reports = Report::with(['reporter:id,name', 'reportedUser:id,name,email', 'hotel:id,name'])
            ->orderByDesc('created_at')
            ->get();

        return response()->json($reports);
    }

    // Admin: update report status
    public function updateStatus(Request $request, $id)
    {
        $request->validate(['status' => 'required|in:pending,reviewed,warned,dismissed']);

        $report = Report::findOrFail($id);
        $report->update(['status' => $request->status]);

        return response()->json($report);
    }
}
