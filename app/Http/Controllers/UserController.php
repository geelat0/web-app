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


class UserController extends Controller
{
    public function create()
    {
        return view('auth.register');
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
            'mobile_number' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            // 'status' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false,'errors' => $validator->errors()], 200);
        }

       // $randomString = Str::random(10);

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
        $user->status = 'Active';
        $user->created_by = Auth::user()->user_name;
        $user->save();

        return response()->json(['success' => true, 'message' => 'User added successfully'], 200);
    }

    public function update(Request $request)
    {
        $validatedData = $request->validate([
            'id' => 'required|exists:users,id',
            'first_name' => 'required|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'suffix' => 'nullable|string|max:50',
            'email' => 'required|email|max:255',
            'mobile_number' => 'nullable|string|max:20',
            'position' => 'nullable|string|max:255',
            'province' => 'nullable|string|max:255',
            'role' => 'nullable|string|max:255',
        ]);

        $user = User::findOrFail($validatedData['id']);
        $user->fill($validatedData);
        $user->save();

        return response()->json(['success' => true, 'message' => 'User updated successfully']);
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
        $users = User::with('role')->get(); 
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


    
}
