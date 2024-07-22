<?php

namespace App\Http\Controllers;

use App\Models\LoginModel;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Yajra\DataTables\Facades\DataTables;

class LogController extends Controller
{
    public function index()
    {
        $logPath = storage_path('logs');
        $files = File::files($logPath);

        $latestFile = collect($files)->sortByDesc(function ($file) {
            return $file->getCTime();
        })->first();

        $logContent = $latestFile ? File::get($latestFile) : '';

        return view('logs.index', [
            'logContent' => $logContent,
            'fileName' => $latestFile ? $latestFile->getFilename() : 'No log file found'
        ]);
    }

    public function login_in()
    {
        if(Auth::check()){

            $user=Auth::user();
             return view('logs.login_in');
    
        }else{
            return redirect('/');
        }
       
    }


    public function clear()
    {
        $logPath = storage_path('logs');
        $files = File::files($logPath);

        foreach ($files as $file) {
            File::delete($file);
        }

        return redirect()->route('logs.index')->with('success', 'Logs cleared successfully.');
    }

    public function list()
    {
        $login_in = LoginModel::with('user')->get();

        // dd($login_in);
        //dd($users->password);// Eager load the role relationship
        return DataTables::of($login_in)
            ->addColumn('id', function($data) {
                return Crypt::encrypt($data->id);
                
            })
            ->addColumn('user', function($data) {
                return ucfirst($data->user->first_name) . ' ' . ucfirst($data->user->last_name);
            })
            // ->editColumn('date_time_in', function($data) {
            //     // return $data->date_time_in->format('m/d/Y');
            // })
            
           
            ->make(true);
    }
}
