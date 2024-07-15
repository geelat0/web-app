<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function getRole(Request $request){

        $roles = Role::where('status', 'Active')->whereNull('deleted_at')->get(['id', 'name']);
        return response()->json($roles);
        

    }
}
