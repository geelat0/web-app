<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
            $userCount = User::count();
            $roleCount = Role::count();
            $user = Auth::user();
            return view('dashboard', compact('user', 'userCount', 'roleCount'));
    }

    public function filter(Request $request)
    {
        $startDate = $request->input('start_date') ? Carbon::parse($request->input('start_date')) : null;
        $endDate = $request->input('end_date') ? Carbon::parse($request->input('end_date')) : null;
        $month = $request->input('month');
        $year = $request->input('year');

        $userQuery = User::query();
        $roleQuery = Role::query();

        if ($startDate && $endDate) {
            $userQuery->whereBetween('created_at', [$startDate, $endDate]);
            $roleQuery->whereBetween('created_at', [$startDate, $endDate]);
        }

        if ($month) {
            $userQuery->whereMonth('created_at', $month);
            $roleQuery->whereMonth('created_at', $month);
        }

        if ($year) {
            $userQuery->whereYear('created_at', $year);
            $roleQuery->whereYear('created_at', $year);
        }

        $userCount = $userQuery->count();
        $roleCount = $roleQuery->count();

        return response()->json([
            'userCount' => $userCount,
            'roleCount' => $roleCount
        ]);
    }
}
