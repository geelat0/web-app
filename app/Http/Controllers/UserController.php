<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

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
            'role' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'status' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false,'errors' => $validator->errors()], 200);
        }

        $user = new User();
        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;
        $user->middle_name = $request->middle_name;
        $user->user_name = ucfirst(strtolower(substr($request->first_name, 0, 1) . '.' . substr($request->last_name, 0, 4)));
        $user->province = $request->province;
        $user->position = $request->position;
        $user->mobile_number = $request->mobile_number;
        $user->role = $request->role;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->status = $request->status;
        $user->save();

        return response()->json(['success' => true, 'message' => 'User registered successfully'], 200);
    }

    public function user_create()
    {
        return view('user_page.user');
    }

    public function getData()
    {
        $users = User::select(['id', 'user_name', 'email', 'created_at']);
        return DataTables::of($users)
            
            ->make(true);
    }


    
}
