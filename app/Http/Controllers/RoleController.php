<?php

namespace App\Http\Controllers;

use App\Models\Role;
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

    public function list()
    {
        $roles = Role::whereNull('deleted_at')->get();
       
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
