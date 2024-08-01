<?php

namespace App\Http\Controllers;

use App\Models\Entries;
use App\Models\SuccessIndicator;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
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
    public function getIndicator(Request $request){
        $searchTerm = $request->input('q'); // Capture search term
        $data = SuccessIndicator::where('status', 'Active')
                              ->whereNull('deleted_at')
                              ->where('target', 'like', "%{$searchTerm}%")
                              ->orWhere('measures', 'like', "%{$searchTerm}%")
                              ->get(['id', 'target', 'measures']);
        return response()->json($data);

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

            $query->where(function($subQuery) use ($searchTerm) {
                $subQuery->where('name', 'like', "%{$searchTerm}%")
                        //  ->orWhere('created_by', 'like', "%{$searchTerm}%")
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
}
