<?php

namespace App\Http\Controllers;

use App\Models\Entries;
use App\Models\SuccessIndicator;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class EntriesController extends Controller
{
    public function index(){

        $user=Auth::user();
        return view('entries.index', compact('user'));
    }

    public function  create(){

        $user=Auth::user();
        return view('entries.create', compact('user'));
    }
    public function edit(Request $request){

        $id = $request->query('id');

        $entries = Entries::find(Crypt::decrypt($id));

        if ($entries && $entries->file) {
            // Decode the Base64 file to get the original contents
            $fileContents = base64_decode($entries->file);
            // Save the file temporarily or just pass the necessary data to the view
            $fileName = 'entry_' . $entries->id . '.pdf'; // Example file name
            Storage::put('public/entries/' . $fileName, $fileContents);
            $fileUrl = Storage::url('public/entries/' . $fileName);
        } else {
            $fileUrl = null;
        }

        $user=Auth::user();
        return view('entries.edit', compact('user', 'entries', 'fileUrl'));
    }
    public function view(Request $request){

        $id = $request->query('id');

        $entries = Entries::find(Crypt::decrypt($id));

        if ($entries && $entries->file) {
            // Decode the Base64 file to get the original contents
            $fileContents = base64_decode($entries->file);
            // Save the file temporarily or just pass the necessary data to the view
            $fileName = 'entry_' . $entries->id . '.pdf'; // Example file name
            Storage::put('public/entries/' . $fileName, $fileContents);
            $fileUrl = Storage::url('public/entries/' . $fileName);
        } else {
            $fileUrl = null;
        }

        $user=Auth::user();
        return view('entries.view', compact('user', 'entries', 'fileUrl'));
    }
    public function getIndicator(Request $request){

        if(in_array(Auth::user()->role->name, ['IT', 'SAP'])){
            $searchTerm = $request->input('q'); // Capture search term
            $data = SuccessIndicator::where('status', 'Active')
                                  ->whereNull('deleted_at')
                                  ->where('target', 'like', "%{$searchTerm}%")
                                  ->orWhere('measures', 'like', "%{$searchTerm}%")
                                  ->get(['id', 'target', 'measures']);
            return response()->json($data);

        }else{
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
                ->get(['id', 'measures', 'division_id', 'target'])
                ->filter(function($indicator) use ($userDivisionIds) {
                    $indicatorDivisionIds = json_decode($indicator->division_id, true);
                    $indicatorDivisionIds = array_map('intval', $indicatorDivisionIds);
                    return !empty(array_intersect($userDivisionIds, $indicatorDivisionIds));
                })
                ->values(); // Re-index the array
        
            return response()->json($data);
        }
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'indicator_id' => 'required|exists:success_indc,id',
            'file' => 'required|file|mimes:pdf|max:2048',
        ]);
    
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
    
        // Handle the file
        $file = $request->file('file');
        $fileContents = file_get_contents($file->getRealPath()); // Get the file contents
        $base64File = base64_encode($fileContents); // Convert to Base64

        // Validate the Base64 data (Check for PDF magic number)
        if (substr($fileContents, 0, 4) !== '%PDF') {
            return response()->json(['errors' => ['file' => 'Invalid PDF file']], 422);
        }
    
        $entry = Entries::create([
            'indicator_id' => $request->input('indicator_id'),
            'file' => $base64File, // Store the Base64 string directly
            'months' => Carbon::now()->month,
            'status' => 'Active',
            'created_by' => Auth::user()->user_name,
        ]);
    
        return response()->json([
            'success' => true,
            'message' => 'Entry created successfully!',
            'entry' => $entry
        ]);
    }
    
    public function list(Request $request){
        $query = Entries::whereNull('deleted_at')->with('indicator');

        if ($request->has('date_range') && !empty($request->date_range)) {
            [$startDate, $endDate] = explode(' to ', $request->date_range);
            $startDate = Carbon::createFromFormat('m/d/Y', $startDate)->startOfDay();
            $endDate = Carbon::createFromFormat('m/d/Y', $endDate)->endOfDay();

            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = $request->search;

            $query->whereHas('indicator',function($subQuery) use ($searchTerm) {
                $subQuery->where('target', 'like', "%{$searchTerm}%")
                         ->orWhere('measures', 'like', "%{$searchTerm}%")
                         ->orWhere('status', 'like', "%{$searchTerm}%");

            });
        }

        $indicator = $query->get();

        return DataTables::of($indicator)
            ->addColumn('id', function($data) {
                return Crypt::encrypt($data->id);
            })
            ->editColumn('indicator_id', function($data) {
                return '(' .$data->indicator->target .')' . '  '. $data->indicator->measures;
            })
            ->editColumn('created_at', function($data) {
                return $data->created_at->format('m/d/Y');
            })
            ->editColumn('months', function($data) {
                return date('F', mktime(0, 0, 0, $data->month, 10));
            })
           
            ->make(true);
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'indicator_id' => 'required|exists:success_indc,id',
            'file' => 'nullable|file|mimes:pdf|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $entry = Entries::find($request->input('id'));

        if ($request->hasFile('file')) {
            // Handle the file
            $file = $request->file('file');
            $fileContents = file_get_contents($file->getRealPath()); // Get the file contents
            $base64File = base64_encode($fileContents); // Convert to Base64

            // Validate the Base64 data (Check for PDF magic number)
            if (substr($fileContents, 0, 4) !== '%PDF') {
                return response()->json(['errors' => ['file' => 'Invalid PDF file']], 422);
            }
           
        }
        $entry->indicator_id = $request->input('indicator_id');
        $entry->months = Carbon::now()->month;
        $entry->file = $base64File;
        $entry->status = 'Active';
        $entry->created_by = Auth::user()->user_name;
        $entry->save();

        return response()->json([
            'success' => true,
            'message' => 'Entry updated successfully!',
            'entry' => $entry
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

        $role = Entries::findOrFail(Crypt::decrypt($request->id));
        $role->delete();

        return response()->json(['success' => true, 'message' => 'Entry deleted successfully']);
    }
}
