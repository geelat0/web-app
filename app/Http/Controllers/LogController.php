<?php

namespace App\Http\Controllers;

use App\Models\LoginModel;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Yajra\DataTables\Facades\DataTables;

class LogController extends Controller
{
    public function index()
    {
        $logPath = storage_path('logs');
        $files = File::files($logPath);

        $latestFile = collect($files)->sortByDesc(function ($file) {
            return $file->getCTime();
        })->first();

        $logContent = $latestFile ? File::get($latestFile) : '';

        return view('logs.index', [
            'logContent' => $logContent,
            'fileName' => $latestFile ? $latestFile->getFilename() : 'No log file found'
        ]);
    }

    public function login_in()
    {
        $user=Auth::user();
        return view('logs.login_in', compact('user')); 
    }


    public function clear()
    {
        $logPath = storage_path('logs');
        $files = File::files($logPath);

        foreach ($files as $file) {
            File::delete($file);
        }

        return redirect()->route('logs.index')->with('success', 'Logs cleared successfully.');
    }

    public function list(Request $request)
    {
        $query = LoginModel::with('user');

        if ($request->has('date_range') && !empty($request->date_range)) {
            [$startDate, $endDate] = explode(' to ', $request->date_range);
            $startDate = Carbon::createFromFormat('m/d/Y', $startDate)->startOfDay();
            $endDate = Carbon::createFromFormat('m/d/Y', $endDate)->endOfDay();
    
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = $request->search;
    
            if (strpos($searchTerm, ' ') !== false) {
                [$firstName, $lastName] = explode(' ', $searchTerm, 2);
                $query->whereHas('user', function($subQuery) use ($firstName, $lastName) {
                    $subQuery->where('first_name', 'like', "%{$firstName}%")
                            ->where('last_name', 'like', "%{$lastName}%");
                });
            } else {
                $query->whereHas('user', function($subQuery) use ($searchTerm) {
                    $subQuery->where('user_name', 'like', "%{$searchTerm}%");
                            
                });
            }
        }
    
        $login_in = $query->get();
        return DataTables::of($login_in)
            ->addColumn('id', function($data) {
                return Crypt::encrypt($data->id);
                
            })
            ->addColumn('user', function($data) {
                return ucfirst($data->user->user_name);
            })
            ->editColumn('created_at', function($data) {
                return $data->created_at->format('m/d/Y H:i:s');
            })

            ->make(true);
    }
}
