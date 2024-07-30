<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class EntriesController extends Controller
{
    public function index(){
        $user=Auth::user();
        return view('entries.index');
    }
}
