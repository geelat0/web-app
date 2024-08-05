<?php

namespace App\Http\Controllers;

use App\Models\Organizational;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class OutcomeController extends Controller
{
    public function index(){

        $user=Auth::user();
        return view('outcome.index', compact('user'));
    }

    public function list(Request $request)
    {
        $query = Organizational::whereNull('deleted_at');

        if ($request->has('date_range') && !empty($request->date_range)) {
            [$startDate, $endDate] = explode(' to ', $request->date_range);
            $startDate = Carbon::createFromFormat('m/d/Y', $startDate)->startOfDay();
            $endDate = Carbon::createFromFormat('m/d/Y', $endDate)->endOfDay();

            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = $request->search;

            $query->where(function($subQuery) use ($searchTerm) {
                $subQuery->where('organizational_outcome', 'like', "%{$searchTerm}%")
                         ->orWhere('created_by', 'like', "%{$searchTerm}%")
                         ->orWhere('status', 'like', "%{$searchTerm}%");

            });
        }

        $orgs = $query->get();

        return DataTables::of($orgs)
            ->addColumn('id', function($org) {
                return Crypt::encrypt($org->id);

            })
            ->editColumn('created_at', function($org) {
                return $org->created_at->format('m/d/Y');
            })

            ->make(true);
    }

    public function store(Request $request)
    {
        // Custom validation rule to check unique organizational outcomes where deleted_at is null
        Validator::extend('unique_with_soft_delete', function ($attribute, $value, $parameters, $validator) {
            $count = DB::table($parameters[0])
                ->where($parameters[1], $value)
                ->whereNull('deleted_at')
                ->count();
            return $count === 0;
        }, 'The :input has already been taken.');

        $validator = Validator::make($request->all(), [
            'organizational_outcome.*' => 'required|string|max:255|unique_with_soft_delete:org_otc,organizational_outcome',
            'status' => 'required|string',
            'organizational_outcome_order' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 200);
        }

        foreach ($request->organizational_outcome as $index => $outcome) {
            $organizational = new Organizational();
            $organizational->organizational_outcome = ucfirst($outcome);
            $organizational->organizational_outcome_order = $request->organizational_outcome_order[$index];
            $organizational->status = ucfirst($request->status);
            $organizational->created_by = Auth::user()->user_name;
            $organizational->save();
        }

        return response()->json(['success' => true, 'message' => 'Organization Outcome(s) created successfully'], 200);
    }

    public function update(Request $request)
    {
        $id = Crypt::decrypt($request->id);
        // dd($id);
        $validator = Validator::make($request->all(), [
            'organizational_outcome' => [
            'required',
            'string',
            'max:255',
            Rule::unique('org_otc')->whereNull('deleted_at')->ignore($id), // Ensures unique name except for the current role
        ],
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 200);
        }

        $org = Organizational::findOrFail($id);

        $org->organizational_outcome = ucfirst($request->organizational_outcome);
        $org->organizational_outcome_order = ucfirst($request->organizational_outcome_order);
        $org->status = ucfirst($request->status);
        $org->created_by = Auth::user()->user_name;
        $org->save();

        return response()->json(['success' => true, 'message' => 'Organization Outcome updated successfully']);
    }

    public function destroy(Request $request)
    {
        $validator = Validator::make($request->all(), [
            // 'id' => 'required|exists:org_otc,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 200);
        }

        $role = Organizational::findOrFail(Crypt::decrypt($request->id));
        $role->delete();

        return response()->json(['success' => true, 'message' => 'Organization Outcome deleted successfully']);
    }

    public function getOrg(Request $request){
        $searchTerm = $request->input('q'); // Capture search term
        $data = Organizational::where('status', 'Active')
                              ->whereNull('deleted_at')
                              ->where('organizational_outcome', 'like', "%{$searchTerm}%")
                              ->get(['id', 'organizational_outcome']);
        return response()->json($data);

    }
}
