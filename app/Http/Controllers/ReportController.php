<?php

namespace App\Http\Controllers;

use App\Models\Division;
use App\Models\Report;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class ReportController extends Controller
{
    public function index(){
        $user=Auth::user();
        return view('entries.index', compact('user'));
    }

    public function create(){
        $user=Auth::user();
        return view('entries.create', compact('user'));
    }

    public function list(Request $request){
        $query = Report::whereNull('deleted_at')->with(['division', 'org']);

        if ($request->has('date_range') && !empty($request->date_range)) {
            [$startDate, $endDate] = explode(' - ', $request->date_range);
            $startDate = Carbon::createFromFormat('m/d/Y', $startDate)->startOfDay();
            $endDate = Carbon::createFromFormat('m/d/Y', $endDate)->endOfDay();
    
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = $request->search;
            
            $query->where(function($subQuery) use ($searchTerm) {
                $subQuery->where('name', 'like', "%{$searchTerm}%")
                        //  ->orWhere('created_by', 'like', "%{$searchTerm}%")
                         ->orWhere('status', 'like', "%{$searchTerm}%");
                        
            });
        }
    
        $entries = $query->get();
       
        return DataTables::of($entries)
            ->addColumn('id', function($data) {
                return Crypt::encrypt($data->id);
                
            })
            ->editColumn('org_id', function($data) {
                return $data->org->organizational_outcome;
            })
            ->editColumn('created_at', function($data) {
                return $data->created_at->format('m/d/Y');
            })
            ->editColumn('division_id', function($data) {
                // Decode the JSON array of division IDs
                $divisionIds = json_decode($data->division_id, true);
                if (is_array($divisionIds)) {
                    $divisions = Division::whereIn('id', $divisionIds)->pluck('division_name')->toArray();
                    return implode(', ', $divisions);
                }
                return '';
            })
            
            ->make(true);
    }

    public function getDivision(Request $request){
        $searchTerm = $request->input('q'); // Capture search term
        $data = Division::where('status', 'Active')
                              ->whereNull('deleted_at')
                              ->where('division_name', 'like', "%{$searchTerm}%")
                              ->get(['id', 'division_name']);
        return response()->json($data);

    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'org_id' => 'required|exists:org_otc,id',
            'target.*' => 'required',
            'measures.*' => 'required',
            'division_id.*.*' => 'exists:divisions,id',
            'actual_accomplishment.*' => 'nullable',
            'rating.*' => 'nullable|integer|min:0|max:5',
            'remarks.*' => 'nullable',
        ],[
            'org_id.required' => 'The organizational outcome is required',
            'target.required' => 'The target is required',
            'measures.required' => 'The measure is required',

        ]);

        foreach ($validated['target'] as $index => $target) {
            Report::create([
                'org_id' => $validated['org_id'],
                'target' => $target,
                'measures' => $validated['measures'][$index],
                'division_id' => json_encode($validated['division_id'][$index]),
                'alloted_budget' => $validated['alloted_budget'][$index] ?? null,
                'actual_accomplishment' => $validated['actual_accomplishment'][$index] ?? null,
                'rating' => $validated['rating'][$index] ?? null,
                'remarks' => $validated['remarks'][$index] ?? null,
                'created_by' => auth()->user()->user_name,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Entries have been successfully saved.'
        ]);
    }
}
