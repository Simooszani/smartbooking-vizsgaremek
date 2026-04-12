<?php

namespace App\Http\Controllers;

use App\Models\Warning;
use App\Models\User;
use App\Models\Report;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class WarningController extends Controller
{
    // Admin: issue a warning
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'reason' => 'required|string|max:500',
            'report_id' => 'nullable|exists:reports,id',
        ]);

        $admin = Auth::user();
        $targetUser = User::findOrFail($request->user_id);

        // Create warning
        $warning = Warning::create([
            'user_id' => $targetUser->id,
            'admin_id' => $admin->id,
            'report_id' => $request->report_id,
            'reason' => $request->reason,
        ]);

        // Update report status if linked
        if ($request->report_id) {
            Report::where('id', $request->report_id)->update(['status' => 'warned']);
        }

        // Calculate suspension based on warning count
        $warningCount = Warning::where('user_id', $targetUser->id)->count();
        $suspendedUntil = null;

        if ($warningCount >= 5) {
            // 5+ warnings: 1 year suspension
            $suspendedUntil = Carbon::now()->addYear();
        } elseif ($warningCount >= 4) {
            // 4 warnings: 1 month suspension
            $suspendedUntil = Carbon::now()->addMonth();
        } elseif ($warningCount >= 3) {
            // 3 warnings: 2 weeks suspension
            $suspendedUntil = Carbon::now()->addWeeks(2);
        }

        if ($suspendedUntil) {
            $targetUser->update(['suspended_until' => $suspendedUntil]);
        }

        // Notification to the warned user
        Notification::create([
            'user_id' => $targetUser->id,
            'type' => 'warning',
            'message' => json_encode([
                'reason' => $request->reason,
                'warning_count' => $warningCount,
                'suspended_until' => $suspendedUntil ? $suspendedUntil->toIso8601String() : null,
                'manual' => true,
            ]),
        ]);

        return response()->json([
            'warning' => $warning,
            'warning_count' => $warningCount,
            'suspended_until' => $suspendedUntil,
        ], 201);
    }

    // Admin: list warnings for a user
    public function userWarnings($userId)
    {
        $warnings = Warning::where('user_id', $userId)
            ->with(['admin:id,name', 'report:id,reason,description'])
            ->orderByDesc('created_at')
            ->get();

        $user = User::select('id', 'name', 'email', 'suspended_until')->findOrFail($userId);

        return response()->json([
            'user' => $user,
            'warnings' => $warnings,
            'count' => $warnings->count(),
        ]);
    }

    // Admin: list all warned users overview (excluding auto-expired warnings)
    public function index()
    {
        // Auto-expire: only count warnings from last 3 months
        $threeMonthsAgo = Carbon::now()->subMonths(3);

        $warnedUsers = Warning::select('user_id')
            ->where('created_at', '>=', $threeMonthsAgo)
            ->selectRaw('COUNT(*) as warning_count')
            ->selectRaw('MAX(created_at) as last_warning_at')
            ->groupBy('user_id')
            ->with('user:id,name,email,suspended_until')
            ->orderByDesc('warning_count')
            ->get();

        return response()->json($warnedUsers);
    }

    // Admin: delete a specific warning
    public function destroy($id)
    {
        $warning = Warning::findOrFail($id);
        $userId = $warning->user_id;
        $warning->delete();

        // Recalculate suspension based on remaining active warnings (last 3 months)
        $threeMonthsAgo = Carbon::now()->subMonths(3);
        $activeCount = Warning::where('user_id', $userId)
            ->where('created_at', '>=', $threeMonthsAgo)
            ->count();

        $user = User::findOrFail($userId);
        if ($activeCount < 3) {
            $user->update(['suspended_until' => null]);
        }

        return response()->json([
            'message' => 'Figyelmeztetés törölve.',
            'remaining_count' => $activeCount,
        ]);
    }
}
