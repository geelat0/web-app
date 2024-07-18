<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{

    public function index(){

        if(Auth::check()){

            $userCount = User::count();
            $roleCount = Role::count();
            $user=Auth::user();
            return view('dashboard', compact('user', 'userCount', 'roleCount'));

        }else{
            return redirect('/');
        }
              
    }

}
