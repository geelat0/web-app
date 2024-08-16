<?php

namespace App\Http\Controllers;

use App\Models\Division;
use App\Models\Entries;
use App\Models\History;
use App\Models\Role;
use App\Models\SuccessIndicator;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

use function Symfony\Component\VarDumper\Dumper\esc;

class IndicatorController extends Controller
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
        return view('indicators.index', compact('user', 'entriesCount'));
    }

    public function create(){
        $user=Auth::user();

        if(Auth::user()->role->name === 'IT' || Auth::user()->role->name === 'SAP'){
            
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

            return view('indicators.create', compact('user', 'entriesCount'));
        }else{

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

            return view('indicators.createv2', compact('user', 'entriesCount'));
        }
    
    }

    public function getMeasureDetails(Request $request)
    {
        $id = $request->input('id');
        
        $user = User::find(Auth::id());
        $userDivisionIds = json_decode($user->division_id, true); // Get the user's division IDs
        $measure = SuccessIndicator::findOrFail($id);
        
        $measureDivisionIds = json_decode($measure->division_id, true); // Get the measure's division IDs
        
        // Filter measureDivisionIds to only include those in userDivisionIds
        $filteredDivisionIds = array_intersect($measureDivisionIds, $userDivisionIds);

        $filteredDivisionIds = array_values($filteredDivisionIds);
        
        $division_targets = [];
        $division_budget = [];
        
        foreach ($filteredDivisionIds as $division_id) {
            $division = Division::find($division_id);
            $cleanedDivisionName = preg_replace('/\s*PO$/', '', $division->division_name);
        
            $column_name = "{$cleanedDivisionName}_target";
            $division_targets[$division_id] = $measure->$column_name ?? '';
        
            $column_name_budget = "{$cleanedDivisionName}_budget";
            $division_budget[$division_id] = $measure->$column_name_budget ?? '';
            $division_name[$division_id] = $division->division_name;
        }
    
        $divisions = [];
        if (is_array($userDivisionIds)) {
            $divisions = Division::whereIn('id', $filteredDivisionIds)->get(['id', 'division_name']);
            
            $divisionData = $divisions->map(function ($division) {
                return [
                    'id' => $division->id,
                    'division_name' => $division->division_name
                ];
            });
        }
        
        $data = [
            'measure' => $measure,
            'division_ids' => $filteredDivisionIds, // Return the filtered division IDs
            'division_targets' => $division_targets,
            'division_budget' => $division_budget,
            'divisions' => $divisionData ?? [],
            'division_name' => $division_name,
        ];
        
        return response()->json($data);
    }


    public function edit(Request $request){
        $id = $request->query('id');
    
        // Get the current user's division IDs
        $userDivisionIds = User::where('id', Auth::user()->id)
            ->pluck('division_id')
            ->first();
        $userDivisionIds = json_decode($userDivisionIds, true);
        $userDivisionIds = array_map('intval', $userDivisionIds);
    
        // Decrypt the indicator ID
        $indicator = SuccessIndicator::find(Crypt::decrypt($id));
    
        // Variables for all roles
        $division_targets = [];
        $division_ids = [];

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
    
        // If user is IT or SAP, show all divisions
        if (Auth::user()->role->name === 'IT' || Auth::user()->role->name === 'SAP') {
           

            $division_ids = json_decode($indicator->division_id);
    
            foreach ($division_ids as $division_id) {
                $division = Division::find($division_id);
                $cleanedDivisionName = preg_replace('/\s*PO$/', '', $division->division_name);
                $column_name = "{$cleanedDivisionName}_target";
                $division_targets[$division_id] = $indicator->$column_name ?? '';

                $column_name_budget = "{$cleanedDivisionName}_budget";
                $division_budget[$division_id] = $indicator->$column_name_budget ?? '';
            }

        } else {

            // Filter divisions based on user's divisions
            $indicatorDivisionIds = json_decode($indicator->division_id, true);
            $indicatorDivisionIds = array_map('intval', $indicatorDivisionIds);
    
            // Keep only the divisions that match the user's divisions
            $filteredDivisionIds = array_intersect($userDivisionIds, $indicatorDivisionIds);
    
            foreach ($filteredDivisionIds as $division_id) {
                $division = Division::find($division_id);
                $cleanedDivisionName = preg_replace('/\s*PO$/', '', $division->division_name);
                $column_name = "{$cleanedDivisionName}_target";
                $division_targets[$division_id] = $indicator->$column_name ?? '';

                $column_name_budget = "{$cleanedDivisionName}_budget";
                $division_budget[$division_id] = $indicator->$column_name_budget ?? '';
            }
    
            $division_ids = $filteredDivisionIds;
        }
    
        $user=Auth::user();
        return view('indicators.edit', compact('indicator', 'division_ids', 'division_targets', 'user', 'division_budget', 'entriesCount'));
    }

    public function view(Request $request){
        $id = $request->query('id');
    
        // Get the current user's division IDs
        $userDivisionIds = User::where('id', Auth::user()->id)
            ->pluck('division_id')
            ->first();
        $userDivisionIds = json_decode($userDivisionIds, true);
        $userDivisionIds = array_map('intval', $userDivisionIds);
    
        // Decrypt the indicator ID
        $indicator = SuccessIndicator::find(Crypt::decrypt($id));
    
        // Variables for all roles
        $division_targets = [];
        $division_ids = [];


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
    
        // If user is IT or SAP, show all divisions
        if (Auth::user()->role->name === 'IT' || Auth::user()->role->name === 'SAP') {
            $division_ids = json_decode($indicator->division_id);

            foreach ($division_ids as $division_id) {
                $division = Division::find($division_id);
                $cleanedDivisionName = preg_replace('/\s*PO$/', '', $division->division_name);
                $column_name = "{$cleanedDivisionName}_target";
                $division_targets[$division_id] = $indicator->$column_name ?? '';

                $column_name_budget = "{$cleanedDivisionName}_budget";
                $division_budget[$division_id] = $indicator->$column_name_budget ?? '';
            }

        } else {

            // Filter divisions based on user's divisions
            $indicatorDivisionIds = json_decode($indicator->division_id, true);
            $indicatorDivisionIds = array_map('intval', $indicatorDivisionIds);

            // Keep only the divisions that match the user's divisions
            $filteredDivisionIds = array_intersect($userDivisionIds, $indicatorDivisionIds);

            foreach ($filteredDivisionIds as $division_id) {
                $division = Division::find($division_id);
                $cleanedDivisionName = preg_replace('/\s*PO$/', '', $division->division_name);
                $column_name = "{$cleanedDivisionName}_target";
                $division_targets[$division_id] = $indicator->$column_name ?? '';

                $column_name_budget = "{$cleanedDivisionName}_budget";
                $division_budget[$division_id] = $indicator->$column_name_budget ?? '';
            }

            $division_ids = $filteredDivisionIds;
        }
    
        $user=Auth::user();
        return view('indicators.view', compact('indicator', 'division_ids', 'division_targets', 'user', 'division_budget', 'entriesCount'));
    }

    public function list(Request $request){
        if(Auth::user()->role->name === 'IT' || Auth::user()->role->name === 'SAP'){
            $query = SuccessIndicator::whereNull('deleted_at')->with(['division', 'org']) ->orderBy('created_at', 'desc');
        }
        else{
            $userId = Auth::id();
            $historyRecords = History::where('user_id', $userId)->get();
            $indicatorIds = $historyRecords->pluck('indicator_id');
            
            $query = SuccessIndicator::whereNull('deleted_at')
            ->whereIn('id', $indicatorIds)
            ->with(['division', 'org'])
            ->orderBy('created_at', 'desc');
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
            // 'alloted_budget.*' => 'required',
            'division_id.*' => 'required',
            'division_id.*.*' => 'exists:divisions,id',

        ],[
            'org_id.required' => 'The organizational outcome is required',
            'target.required' => 'The target is required',
            'measures.required' => 'The measure is required',
            // 'alloted_budget.required' => 'The alloted budget is required',
            'division_id.required' => 'The division is required',
        ]);

        $data = $request->all();

        $successIndicatorIds = [];

        foreach ($request->measures as $index => $measure) {

            $successIndicator = SuccessIndicator::create([
                'org_id' => $request->org_id,
                'measures' => $measure,
                'target' => $request->target[$index] ?? 'Actual',
                'Albay_target' => $request->Albay_target[$index] ?? 0,
                'Camarines_Sur_target' =>  $request->Camarines_Sur_target[$index] ?? 0,
                'Camarines_Norte_target' => $request->Camarines_Norte_target[$index] ?? 0,
                'Catanduanes_target' =>  $request->Catanduanes_target[$index] ?? 0,
                'Masbate_target' =>  $request->Masbate_target[$index] ?? 0,
                'Sorsogon_target' => $request->Sorsogon_target[$index] ?? 0,
                'Albay_budget' => $request->Albay_budget[$index] ?? 0,
                'Camarines_Sur_budget' =>  $request->Camarines_Sur_budget[$index] ?? 0,
                'Camarines_Norte_budget' => $request->Camarines_Norte_budget[$index] ?? 0,
                'Catanduanes_budget' =>  $request->Catanduanes_budget[$index] ?? 0,
                'Masbate_budget' =>  $request->Masbate_budget[$index] ?? 0,
                'Sorsogon_budget' => $request->Sorsogon_budget[$index] ?? 0,
                'division_id' => json_encode($request->division_id[$index]), 
                'alloted_budget' => $request->alloted_budget[$index] ?? 0,
                'created_by' => Auth::user()->user_name,
            ]);

            $successIndicatorIds[] = $successIndicator->id;
        }


        // $indicators = SuccessIndicator::all();
        // $matchingUserIds = [];
        
        // // Get all success indicator IDs
        // foreach ($indicators as $indicator) {
        //     $indicatorDivisionIds = json_decode($indicator->division_id, true);
        
        //     if (is_array($indicatorDivisionIds)) {

        //         $excludedRoles = Role::whereIn('name', ['IT', 'SAP'])
        //         ->pluck('id');
        //         // Fetch all users
        //         $users = User::whereNotIn('role_id', $excludedRoles)->get();
        
        //         foreach ($users as $user) {
        //             $userDivisionIds = json_decode($user->division_id, true);
        
        //             if (is_array($userDivisionIds)) {
        //                 $commonDivisions = array_intersect($indicatorDivisionIds, $userDivisionIds);
        
        //                 if (!empty($commonDivisions)) {
        //                     $matchingUserIds[$user->id] = $user->id; 
        //                 }
        //             }
        //         }
        //     }
        // }
        
        // //Insert into entries table
        // foreach ($matchingUserIds as $userId) {
        //     foreach ($successIndicatorIds as $indicatorId) {
        //         Entries::create([
        //             'indicator_id' => $indicatorId,
        //             'user_id' => $userId,
        //             'created_by' => Auth::user()->user_name,
        //         ]);
        //     }
        // }
        
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

        $ifExist = Entries::whereNull('deleted_at')->where('indicator_id', Crypt::decrypt($request->id))->exists();

        if($ifExist){
            
            return response()->json(['success' => false, 'errors' => 'The Indicator is being used on Entries table']);
        }

        $role = SuccessIndicator::findOrFail(Crypt::decrypt($request->id));
        $role->updated_by = Auth::user()->user_name;
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
            'division_id' => 'required|array',
            'division_id.*' => 'exists:divisions,id',
            'status' => 'nullable|string|in:Active,Inactive',
        ]);
    
        // Find the success indicator by ID
        $indicator = SuccessIndicator::findOrFail($request->id);

        $currentMonth = Carbon::now()->month;
       
    
        // Update the record with the new data
        $indicator->org_id = $request->input('org_id');
        $indicator->target = $request->input('target') ?? 'Actual';
        $indicator->Albay_target = str_replace(['[', ']', '"'], '', json_encode($request->input('Albay_target') ?? 0));
        $indicator->Camarines_Sur_target = str_replace(['[', ']', '"'], '', json_encode($request->input('Camarines_Sur_target') ?? 0));
        $indicator->Camarines_Norte_target = str_replace(['[', ']', '"'], '', json_encode($request->input('Camarines_Norte_target') ?? 0));
        $indicator->Catanduanes_target = str_replace(['[', ']', '"'], '', json_encode($request->input('Catanduanes_target') ?? 0));
        $indicator->Masbate_target = str_replace(['[', ']', '"'], '', json_encode($request->input('Masbate_target') ?? 0));
        $indicator->Sorsogon_target = str_replace(['[', ']', '"'], '', json_encode($request->input('Sorsogon_target') ?? 0));
        $indicator->Albay_budget = str_replace(['[', ']', '"'], '', json_encode($request->input('Albay_budget') ?? 0));
        $indicator->Camarines_Sur_budget = str_replace(['[', ']', '"'], '', json_encode($request->input('Camarines_Sur_budget') ?? 0));
        $indicator->Camarines_Norte_budget = str_replace(['[', ']', '"'], '', json_encode($request->input('Camarines_Norte_budget') ?? 0));
        $indicator->Catanduanes_budget = str_replace(['[', ']', '"'], '', json_encode($request->input('Catanduanes_budget') ?? 0));
        $indicator->Masbate_budget = str_replace(['[', ']', '"'], '', json_encode($request->input('Masbate_budget') ?? 0));
        $indicator->Sorsogon_budget = str_replace(['[', ']', '"'], '', json_encode($request->input('Sorsogon_budget') ?? 0));
        $indicator->measures = $request->input('measures');
        $indicator->alloted_budget = $request->input('alloted_budget');
        $indicator->division_id = json_encode($request->input('division_id')); // Save as JSON
        $indicator->status = $request->input('status', 'Active'); // Default to 'Active' if not provided
        $indicator->updated_by = Auth::user()->user_name; // Assuming you store the username of the creator
        $indicator->updated_at =now(); // Assuming you store the username of the creator
    
        // Save the updated indicator
        $indicator->save();

        $history = new History();
        $history->indicator_id = $indicator->id;
        $history->user_id = Auth::id();
        $history->save();

        return response()->json([
            'success' => true,
            'message' => 'Indicator have been successfully updated'
        ]);

    }

    public function update_nonSuperAdmin(Request $request)
    {

        $request->validate([
            'measures' => 'required',
            'alloted_budget' => 'required|numeric',
        ]);
    
        // Find the success indicator by ID
        $indicator = SuccessIndicator::findOrFail($request->id);

        // $po_budget = $indicator->Albay_budget + $indicator->Camarines_Sur_budget + $indicator->Camarines_Norte_budget +  $indicator->Catanduanes_budget + $indicator->Masbate_budget + $indicator->Sorsogon_budget;

        // dd($indicator->alloted_budget -  $request->input('alloted_budget'));
        
       
        // Update the record with the new data
        $indicator->Albay_budget = str_replace(['[', ']', '"'], '', json_encode($request->input('Albay_budget') ?? $indicator->Albay_budget));
        
        $indicator->Camarines_Sur_budget = str_replace(['[', ']', '"'], '', json_encode($request->input('Camarines_Sur_budget') ?? $indicator->Camarines_Sur_budget));
        

        $indicator->Camarines_Norte_budget = str_replace(['[', ']', '"'], '', json_encode($request->input('Camarines_Norte_budget') ?? $indicator->Camarines_Norte_budget));
        
      
        $indicator->Catanduanes_budget = str_replace(['[', ']', '"'], '', json_encode($request->input('Catanduanes_budget') ?? $indicator->Catanduanes_budget));
        
        
        $indicator->Masbate_budget = str_replace(['[', ']', '"'], '', json_encode($request->input('Masbate_budget') ?? $indicator->Masbate_budget));
        
       
        $indicator->Sorsogon_budget = str_replace(['[', ']', '"'], '', json_encode($request->input('Sorsogon_budget') ?? $indicator->Sorsogon_budget ));
        
        $indicator->alloted_budget = $request->input('alloted_budget');
        $indicator->updated_by = Auth::user()->user_name; // Assuming you store the username of the creator
        $indicator->updated_at =now(); // Assuming you store the username of the creator
    
        // Save the updated indicator
        $indicator->save();

        $history = new History();
        $history->indicator_id = $indicator->id;
        $history->user_id = Auth::id();
        $history->save();

        return response()->json([
            'success' => true,
            'message' => 'Indicator have been successfully updated'
        ]);

    }
    public function update_nonSuperAdminV2(Request $request)
    {

        $request->validate([
            // 'measures' => 'required',
            'alloted_budget' => 'required|numeric',
        ]);
    
        // Find the success indicator by ID
        $indicator = SuccessIndicator::findOrFail($request->id);

        // $po_budget = $indicator->Albay_budget + $indicator->Camarines_Sur_budget + $indicator->Camarines_Norte_budget +  $indicator->Catanduanes_budget + $indicator->Masbate_budget + $indicator->Sorsogon_budget;

        // dd($indicator->alloted_budget -  $request->input('alloted_budget'));
        
       
        // Update the record with the new data
        $indicator->Albay_budget = str_replace(['[', ']', '"'], '', json_encode($request->input('Albay_budget') ?? $indicator->Albay_budget));
        
        $indicator->Camarines_Sur_budget = str_replace(['[', ']', '"'], '', json_encode($request->input('Camarines_Sur_budget') ?? $indicator->Camarines_Sur_budget));
        

        $indicator->Camarines_Norte_budget = str_replace(['[', ']', '"'], '', json_encode($request->input('Camarines_Norte_budget') ?? $indicator->Camarines_Norte_budget));
        
      
        $indicator->Catanduanes_budget = str_replace(['[', ']', '"'], '', json_encode($request->input('Catanduanes_budget') ?? $indicator->Catanduanes_budget));
        
        
        $indicator->Masbate_budget = str_replace(['[', ']', '"'], '', json_encode($request->input('Masbate_budget') ?? $indicator->Masbate_budget));
        
       
        $indicator->Sorsogon_budget = str_replace(['[', ']', '"'], '', json_encode($request->input('Sorsogon_budget') ?? $indicator->Sorsogon_budget ));
        
        $indicator->alloted_budget = $request->input('alloted_budget');
        $indicator->updated_by = Auth::user()->user_name; // Assuming you store the username of the creator
        $indicator->updated_at =now(); // Assuming you store the username of the creator
    
        // Save the updated indicator
        $indicator->save();

        $history = new History();
        $history->indicator_id = $indicator->id;
        $history->user_id = Auth::id();
        $history->save();

        return response()->json([
            'success' => true,
            'message' => 'Indicator have been successfully updated'
        ]);

    }

    public function getIndicator(Request $request)
    {
        $searchTerm = $request->input('q');
    
        // Get the current user's division IDs
        $userDivisionIds = User::where('id', Auth::user()->id)
            ->pluck('division_id')
            ->first();
        $userDivisionIds = json_decode($userDivisionIds, true);
        $userDivisionIds = array_map('intval', $userDivisionIds);
    
        // Fetch success indicators where the user's division_id exists in the success indicator's division_id field
        $data = SuccessIndicator::where('status', 'Active')
            ->whereNull('deleted_at')
            ->where('measures', 'like', "%{$searchTerm}%")
            ->get(['id', 'measures', 'division_id'])
            ->filter(function($indicator) use ($userDivisionIds) {
                $indicatorDivisionIds = json_decode($indicator->division_id, true);
                $indicatorDivisionIds = array_map('intval', $indicatorDivisionIds);
                return !empty(array_intersect($userDivisionIds, $indicatorDivisionIds));
            })
            ->values(); // Re-index the array
    
        return response()->json($data);
    }
       
}

    
