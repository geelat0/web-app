<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{

    public function roles()
    {
        $user=Auth::user();
        return view('role_page.roles');
    }

    public function getRole(Request $request){

        $roles = Role::where('status', 'Active')->whereNull('deleted_at')->get(['id', 'name']);
        return response()->json($roles);
        

    }

    public function list(Request $request)
    {
        $query = Role::whereNull('deleted_at');

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
    
        $roles = $query->get();
       
        return DataTables::of($roles)
            ->addColumn('id', function($role) {
                return Crypt::encrypt($role->id);
                
            })
            ->editColumn('created_at', function($role) {
                return $role->created_at->format('m/d/Y');
            })
            
            ->make(true);
    }


    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:role',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false,'errors' => $validator->errors()], 200);
        }

        $user = new Role();
        $user->name = ucfirst($request->name);
        $user->status = ucfirst($request->status);
        $user->created_by = Auth::user()->user_name;
        $user->save();

        return response()->json(['success' => true, 'message' => 'Role created successfully'], 200);
    }


    public function update(Request $request)
    {
        $id = Crypt::decrypt($request->id);
        // dd($id);
        $validator = Validator::make($request->all(), [
            'name' => [
            'required',
            'string',
            'max:255',
            Rule::unique('role')->ignore($id), // Ensures unique name except for the current role
        ],
        ]);
    
        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 200);
        }
    
        $user = Role::findOrFail($id);
    
        $user->name = ucfirst($request->name);
        $user->status = ucfirst($request->status);
        $user->created_by = Auth::user()->user_name;
        $user->save();
    
        return response()->json(['success' => true, 'message' => 'Role updated successfully']);
    }

    

    public function destroy(Request $request)
    {
        $validator = Validator::make($request->all(), [
            // 'id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 200);
        }

        $role = Role::findOrFail(Crypt::decrypt($request->id));
        $role->delete();

        return response()->json(['success' => true, 'message' => 'Role deleted successfully']);
    }
}
