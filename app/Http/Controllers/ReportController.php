<?php

namespace App\Http\Controllers;

use App\Models\Division;
use App\Models\Entries;
use App\Models\Organizational;
use App\Models\Report;
use App\Models\SuccessIndicator;
use PDF;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


class ReportController extends Controller
{
    public function index(){
       
        $user=Auth::user();

        $currentYear = Carbon::now()->format('Y');
        $currentUser = Auth::user();
        $entriesCount = SuccessIndicator::whereNull('deleted_at')->whereYear('created_at', $currentYear);

        $indicators = $entriesCount->get();
        
        $userDivisionIds = json_decode($currentUser->division_id, true);
        $filteredIndicators = $indicators->filter(function($indicator) use ($userDivisionIds) {
            $indicatorDivisionIds = json_decode($indicator->division_id, true);
            
            return !empty(array_intersect($userDivisionIds, $indicatorDivisionIds));
        });

        $currentMonth = Carbon::now()->format('m');
        $current_Year = Carbon::now()->format('Y');

        $currentDate = Carbon::now();

        if ($currentDate->day > 5) {
            $targetMonth = $currentDate->month;
            // $targetMonth = $currentDate->addMonth()->month;
        } else {
            $targetMonth = $currentDate->subMonth()->month;
        }

        $filteredIndicators = $filteredIndicators->filter(function($indicator) use ($targetMonth, $current_Year) {
            $completedEntries = Entries::where('indicator_id', $indicator->id)
                                    ->where('months', $targetMonth)
                                    ->whereYear('created_at', $current_Year)
                                    ->where('status', 'Completed')
                                    ->where('user_id',  Auth::user()->id)
                                    ->exists();
            return !$completedEntries;
        });
          
            // $entriesCount = Entries::whereNull('deleted_at')->with('indicator')->where('status', 'Pending')->count();
        $entriesCount = $filteredIndicators->count();
        return view('generate.index', compact('user', 'entriesCount'));
    }

    public function pdf(Request $request){
        return view('generate.pdf',);

    }

    public function generatePDF(Request $request)
    {
        $year = $request->input('year');
        $period = $request->input('period');
        $semiannual = $request->input('semiannual');
        $divisionIds = $request->input('division_id');
        $province = $request->input('province');
    
        // Fetch all indicators
        $indicators = SuccessIndicator::all();
    
        // Initialize the collection of indicator IDs
        $indicatorIds = collect();
    
        if (!empty($divisionIds)) {
            // Filter indicators based on the divisionIds
            $filteredIndicators = $indicators->filter(function ($indicator) use ($divisionIds) {
                $indicatorDivisionIds = json_decode($indicator->division_id, true);
                return !empty(array_intersect($divisionIds, $indicatorDivisionIds));
            });
    
            $indicatorIds = $filteredIndicators->pluck('id');
        } else {
            $indicatorIds = $indicators->pluck('id');
        }
    
        // Fetch organizational outcomes with their success indicators based on filters
        $orgOutcomes = Organizational::with(['successIndicators' => function($query) use ($year, $period, $indicatorIds) {
            if ($indicatorIds->isNotEmpty()) {
                $query->whereIn('id', $indicatorIds); 
            }
            if ($year) {
                $query->whereYear('created_at', $year);
            }
            if ($period) {
                $months = $this->getMonthsForPeriod($period);
                $query->whereIn(DB::raw('MONTH(created_at)'), $months);
            }
        }])
        ->where(function($query) use ($year, $period, $indicatorIds) {
            $query->whereHas('successIndicators', function($query) use ($year, $period, $indicatorIds) {
                if ($indicatorIds->isNotEmpty()) {
                    $query->whereIn('id', $indicatorIds); 
                }
                if ($year) {
                    $query->whereYear('created_at', $year);
                }
                if ($period) {
                    $months = $this->getMonthsForPeriod($period);
                    $query->whereIn(DB::raw('MONTH(created_at)'), $months);
                }
            });
            // ->orWhereDoesntHave('successIndicators');
        })
        ->orderBy('order','ASC') 
        ->get();
    
        // Fetch entries based on filters
        if (Auth::user()->role->name === 'IT' || Auth::user()->role->name === 'SAP') {
            $entries = Entries::whereYear('created_at', $year)
                        ->when($period, function($query) use ($period) {
                            $months = $this->getMonthsForPeriod($period);
                            $query->whereIn(DB::raw('MONTH(created_at)'), $months);
                        })
                        ->when($province, function($query) use ($province) {
                            $query->whereHas('user', function($query) use ($province) {
                                $query->where('province', $province);
                            });
                        })
                        ->get()
                        ->groupBy('indicator_id');
        }else{
            $entries = Entries::whereYear('created_at', $year)
                ->where('created_by', Auth::user()->user_name)
                ->when($period, function($query) use ($period) {
                    $months = $this->getMonthsForPeriod($period);
                    $query->whereIn(DB::raw('MONTH(created_at)'), $months);
                })
                ->when($province, function($query) use ($province) {
                    $query->whereHas('user', function($query) use ($province) {
                        $query->where('province', $province);
                    });
                })
                ->get()
                ->groupBy('indicator_id');

        }
                    
        $pdf = PDF::loadView('generate.pdf', compact('orgOutcomes', 'entries', 'divisionIds'))
                ->setPaper('a4', 'landscape');
    
        return $pdf->stream('OPCR-RO5.pdf');
    }
    

    private function getMonthsForPeriod($period)
    {
        switch ($period) {
            case 'Q1':
                return [1, 2, 3];
            case 'Q2':
                return [4, 5, 6];
            case 'Q3':
                return [7, 8, 9];
            case 'Q4':
                return [10, 11, 12];
            case 'H1':
                return [1, 2, 3, 4, 5, 6];
            case 'H2':
                return [7, 8, 9, 10, 11, 12];
        }
    }

}
