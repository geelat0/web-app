<?php

namespace App\Http\Controllers;

use App\Models\Division;
use App\Models\SuccessIndicator;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class IndicatorController extends Controller
{
    public function index(){
        $user=Auth::user();
        return view('indicators.index', compact('user'));
    }

    public function create(){
        $user=Auth::user();
        return view('indicators.create', compact('user'));
    }

    public function edit(Request $request){
        $id = $request->query('id');

        $indicator = SuccessIndicator::find(Crypt::decrypt($id));
        $division_ids = json_decode($indicator->division_id);

        $division_targets = [];
        foreach ($division_ids as $division_id) {
            $division =Division::find($division_id);
            $cleanedDivisionName = preg_replace('/\s*PO$/', '', $division->division_name);
            $column_name = "{$cleanedDivisionName}_target";
            $division_targets[$division_id] = $indicator->$column_name ?? '';
        }

        $user=Auth::user();
        return view('indicators.edit', compact('indicator', 'division_ids', 'division_targets', 'user'));
    }

    public function view(Request $request){
        $id = $request->query('id');

        $indicator = SuccessIndicator::find(Crypt::decrypt($id));
        $division_ids = json_decode($indicator->division_id);

        $division_targets = [];
        foreach ($division_ids as $division_id) {
            $division =Division::find($division_id);
            $cleanedDivisionName = preg_replace('/\s*PO$/', '', $division->division_name);
            $column_name = "{$cleanedDivisionName}_target";
            $division_targets[$division_id] = $indicator->$column_name ?? '';
        }

        $user=Auth::user();
        return view('indicators.view', compact('indicator', 'division_ids', 'division_targets', 'user'));
    }

    public function list(Request $request){
        if(Auth::user()->role->name === 'IT' || Auth::user()->role->name === 'SAP'){
            $query = SuccessIndicator::whereNull('deleted_at')->with(['division', 'org']);
        }
        else{
            $query = SuccessIndicator::whereNull('deleted_at')->with(['division', 'org'])->where('updated_by', Auth::user()->user_name);
        }
       

        if ($request->has('date_range') && !empty($request->date_range)) {
            [$startDate, $endDate] = explode(' to ', $request->date_range);
            $startDate = Carbon::createFromFormat('m/d/Y', $startDate)->startOfDay();
            $endDate = Carbon::createFromFormat('m/d/Y', $endDate)->endOfDay();

            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = $request->search;

            $query->whereHas('org',function($subQuery) use ($searchTerm) {
                $subQuery->where('measures', 'like', "%{$searchTerm}%")
                         ->orWhere('organizational_outcome', 'like', "%{$searchTerm}%")
                         ->orWhere('target', 'like', "%{$searchTerm}%");
            });
        }

        $indicator = $query->get();

        return DataTables::of($indicator)
            ->addColumn('id', function($data) {
                return Crypt::encrypt($data->id);

            })
            ->editColumn('org_id', function($data) {
                return $data->org->organizational_outcome;
            })
            ->editColumn('created_at', function($data) {
                return $data->created_at->format('m/d/Y');
            })
            ->editColumn('updated_at', function($data) {
                return $data->updated_at->format('m/d/Y');
            })
            ->editColumn('alloted_budget', function($data) {
                return '₱' . number_format($data->alloted_budget, 2);
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
    
        $userDivisionIds = User::where('id', Auth::user()->id)
                                ->pluck('division_id')
                                ->first();

        $userDivisionIds = json_decode($userDivisionIds, true);
            $userDivisionIds = array_map('intval', $userDivisionIds);
    
        $query = Division::where('status', 'Active')
                          ->whereNull('deleted_at')
                          ->where('division_name', 'like', "%{$searchTerm}%");
    
        if (!empty($userDivisionIds)) {
            $query->whereIn('id', $userDivisionIds);
        }
    
        $data = $query->get(['id', 'division_name']);
        return response()->json($data);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'org_id' => 'required|exists:org_otc,id',
            'target.*' => 'required',
            'measures.*' => 'required',
            'alloted_budget.*' => 'required',
            'division_id.*' => 'required',
            'division_id.*.*' => 'exists:divisions,id',

        ],[
            'org_id.required' => 'The organizational outcome is required',
            'target.required' => 'The target is required',
            'measures.required' => 'The measure is required',
            'alloted_budget.required' => 'The alloted budget is required',
            'division_id.required' => 'The division is required',
        ]);

        $data = $request->all();
        // dd($data);

        $currentMonth = Carbon::now()->month;

        foreach ($request->measures as $index => $measure) {
            // dd($measure);

            SuccessIndicator::create([
                'org_id' => $request->org_id,
                'measures' => $measure,
                'target' => $request->target[$index] ?? 'Actual',
                'Albay_target' => $request->Albay_target[$index] ?? 0,
                'Camarines_Sur_target' =>  $request->Camarines_Sur_target[$index] ?? 0,
                'Camarines_Norte_target' => $request->Camarines_Norte_target[$index] ?? 0,
                'Catanduanes_target' =>  $request->Catanduanes_target[$index] ?? 0,
                'Masbate_target' =>  $request->Masbate_target[$index] ?? 0,
                'Sorsogon_target' => $request->Sorsogon_target[$index] ?? 0,
                'months' =>$request->months[$index] ?? $currentMonth,
                'division_id' => json_encode($request->division_id[$index]), 
                'alloted_budget' => $request->alloted_budget[$index],
                'created_by' => auth()->user()->user_name,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Indicator have been successfully saved.'
        ]);
    }

    public function destroy(Request $request)
    {
        $validator = Validator::make($request->all(), [
            // 'id' => 'required|exists:org_otc,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 200);
        }

        $role = SuccessIndicator::findOrFail(Crypt::decrypt($request->id));
        $role->delete();

        return response()->json(['success' => true, 'message' => 'Indicator deleted successfully']);
    }

    public function update(Request $request)
    {

        $request->validate([
            'org_id' => 'required|exists:org_otc,id',
            // 'target' => 'required',
            'measures' => 'required|string',
            'alloted_budget' => 'required|numeric',
            'division_id' => 'nullable|array',
            'division_id.*' => 'exists:divisions,id',
            'status' => 'nullable|string|in:Active,Inactive',
        ]);
    
        // Find the success indicator by ID
        $indicator = SuccessIndicator::findOrFail($request->id);

        $currentMonth = Carbon::now()->month;
       
    
        // Update the record with the new data
        $indicator->org_id = $request->input('org_id');
        $indicator->target = $request->input('target') ?? 'Actual';
        $indicator->Albay_target = str_replace(['[', ']', '"'], '', json_encode($request->input('Albay_target'))) ?? '0';
        $indicator->Camarines_Sur_target = str_replace(['[', ']', '"'], '', json_encode($request->input('Camarines_Sur_target'))) ?? '0';
        $indicator->Camarines_Norte_target = str_replace(['[', ']', '"'], '', json_encode($request->input('Camarines_Norte_target'))) ?? '0';
        $indicator->Catanduanes_target = str_replace(['[', ']', '"'], '', json_encode($request->input('Catanduanes_target'))) ?? '0';
        $indicator->Masbate_target = str_replace(['[', ']', '"'], '', json_encode($request->input('Masbate_target'))) ?? '0';
        $indicator->Sorsogon_target = str_replace(['[', ']', '"'], '', json_encode($request->input('Sorsogon_target'))) ?? '0';
        $indicator->measures = $request->input('measures');
        $indicator->alloted_budget = $request->input('alloted_budget');
        $indicator->division_id = json_encode($request->input('division_id')); // Save as JSON
        $indicator->months = $currentMonth;
        $indicator->status = $request->input('status', 'Active'); // Default to 'Active' if not provided
        $indicator->updated_by = Auth::user()->user_name; // Assuming you store the username of the creator
        $indicator->updated_at =now(); // Assuming you store the username of the creator
    
        // Save the updated indicator
        $indicator->save();

        return response()->json([
            'success' => true,
            'message' => 'Indicator have been successfully updated'
        ]);

    }
       
}

    
