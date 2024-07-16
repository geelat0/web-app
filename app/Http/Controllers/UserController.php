<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function create()
    {
        return view('auth.register');
    }

    public function user_create()
    {
        if(Auth::check()){

            $user=Auth::user();
             return view('user_page.user');
    
        }else{
            return redirect('/');
        }
       
    }

    public function getData()
    {
        $users = User::with('role')->whereNull('deleted_at')->get();
        //dd($users->password);// Eager load the role relationship
        return DataTables::of($users)
            ->addColumn('name', function($user) {
                return ucfirst($user->first_name) . ' ' . ucfirst($user->last_name);
            })
            ->editColumn('created_at', function($user) {
                return $user->created_at->format('m/d/Y');
            })
            ->addColumn('role', function($user) {
                return $user->role ? $user->role->name : 'N/A'; // Fetch the role name
            })
            // ->addColumn('password', function($user) {
            //     return Hasher::decode() ; // Fetch the role name
            // })
           
            ->make(true);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'middle_name' => 'required|string|max:255',
            'province' => 'required|string|max:255',
            'position' => 'required|string|max:255',
            'mobile_number' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'status' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false,'errors' => $validator->errors()], 200);
        }

        $user = new User();
        $user->first_name = ucfirst($request->first_name);
        $user->last_name = ucfirst($request->last_name);
        $user->middle_name = ucfirst($request->middle_name);
        $user->user_name = ucfirst(strtolower(substr($request->first_name, 0, 1) . '.' . substr($request->last_name, 0, 4)));
        $user->province = ucfirst($request->province);
        $user->position = ucfirst($request->position);
        $user->mobile_number = $request->mobile_number;
        $user->role_id = $request->role_id;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->status = ucfirst($request->status);
        $user->created_by = ucfirst(strtolower(substr($request->first_name, 0, 1) . '.' . substr($request->last_name, 0, 4)));
        $user->save();

        return response()->json(['success' => true, 'message' => 'User registered successfully'], 200);
    }

    public function UserStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'middle_name' => 'required|string|max:255',
            'province' => 'required|string|max:255',
            'position' => 'required|string|max:255',
            'mobile_number' => [
                            'required',
                            'string',
                            
                            'max:255',
                            Rule::unique('users')->whereNull('deleted_at')->ignore($request->id),
                        ],
            'email' => [
                            'required',
                            'string',
                            'email',
                            'max:255',
                            Rule::unique('users')->whereNull('deleted_at')->ignore($request->id),
                        ],
            'role_id' => 'required',
        ],[
            'role_id' => 'The role field is required'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false,'errors' => $validator->errors()], 200);
        }

       $randomString = Str::random(10);

        $user = new User();
        $user->first_name = ucfirst($request->first_name);
        $user->last_name = ucfirst($request->last_name);
        $user->middle_name = ucfirst($request->middle_name);
        $user->user_name = ucfirst(strtolower(substr($request->first_name, 0, 1) . '.' . substr($request->last_name, 0, 4)));
        $user->province = ucfirst($request->province);
        $user->position = ucfirst($request->position);
        $user->mobile_number = $request->mobile_number;
        $user->role_id = $request->role_id;
        $user->email = $request->email;
        $user->password = Hash::make($randomString);
        $user->status = 'Active';
        $user->created_by = Auth::user()->user_name;
        $user->save();

        return response()->json(['success' => true, 'message' => 'User added successfully'], 200);
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:users,id',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'middle_name' => 'required|string|max:255',
            'province' => 'required|string|max:255',
            'position' => 'required|string|max:255',
            'mobile_number' => [
                            'required',
                            'string',
                            
                            'max:255',
                            Rule::unique('users')->whereNull('deleted_at')->ignore($request->id),
                        ],
            'email' => [
                            'required',
                            'string',
                            'email',
                            'max:255',
                            Rule::unique('users')->whereNull('deleted_at')->ignore($request->id),
                        ],
            // 'password' => 'required|string|min:8',
            'role_id' => 'required|exists:role,id',
        ],[
            'role_id.required' => 'The role field is required'
        ]);
    
        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 200);
        }
    
        $user = User::findOrFail($request->id);
    
        $user->first_name = ucfirst($request->first_name);
        $user->last_name = ucfirst($request->last_name);
        $user->middle_name = ucfirst($request->middle_name);
        $user->user_name = ucfirst(strtolower(substr($request->first_name, 0, 1) . '.' . substr($request->last_name, 0, 4)));
        $user->province = ucfirst($request->province);
        $user->position = ucfirst($request->position);
        $user->mobile_number = $request->mobile_number;
        $user->role_id = $request->role_id;
        $user->email = $request->email;
        // $user->password = Hash::make($randomString); // Uncomment if you need to update the password
        $user->status = 'Active';
        $user->created_by = Auth::user()->user_name;
        $user->save();
    
        return response()->json(['success' => true, 'message' => 'User updated successfully']);
    }
    
    public function destroy(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 200);
        }

        $user = User::findOrFail($request->id);
        $user->delete();

        return response()->json(['success' => true, 'message' => 'User deleted successfully']);
    }


    
}
