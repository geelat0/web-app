<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use App\Mail\TempPasswordMail;
use App\Models\LoginModel;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;

class UserController extends Controller
{

    protected $redirectTo = '/dash-home';
    
    public function create()
    {
        return view('auth.register');
    }

    public function user_create()
    {
        $user=Auth::user();
        return view('user_page.user');
    }

    public function list(Request $request)
    {
        $query = User::with('role')->whereNull('deleted_at');
    
        if ($request->has('date_range') && !empty($request->date_range)) {
            [$startDate, $endDate] = explode(' - ', $request->date_range);
            $startDate = Carbon::createFromFormat('m/d/Y', $startDate)->startOfDay();
            $endDate = Carbon::createFromFormat('m/d/Y', $endDate)->endOfDay();
    
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = $request->search;
    
            if (strpos($searchTerm, ' ') !== false) {
                [$firstName, $lastName] = explode(' ', $searchTerm, 2);
                $query->whereHas('role', function($subQuery) use ($firstName, $lastName) {
                    $subQuery->where('first_name', 'like', "%{$firstName}%")
                            ->where('last_name', 'like', "%{$lastName}%");
                });
            } else {
                $query->whereHas('role',function($subQuery) use ($searchTerm) {
                    $subQuery->where('first_name', 'like', "%{$searchTerm}%")
                            ->orWhere('last_name', 'like', "%{$searchTerm}%")
                            ->orWhere('user_name', 'like', "%{$searchTerm}%")
                            ->orWhere('email', 'like', "%{$searchTerm}%")
                            ->orWhere('position', 'like', "%{$searchTerm}%")
                            ->orWhere('name', 'like', "%{$searchTerm}%")
                            ->orWhere('province', 'like', "%{$searchTerm}%");
                });
            }
        }
    
        $users = $query->get();
    
        return DataTables::of($users)
            ->addColumn('id', function($user) {
                return Crypt::encrypt($user->id);
            })
            ->addColumn('name', function($user) {
                return ucfirst($user->first_name) . ' ' . ucfirst($user->last_name);
            })
            ->editColumn('created_at', function($user) {
                return $user->created_at->format('m/d/Y');
            })
            ->addColumn('role', function($user) {
                return $user->role ? $user->role->name : 'N/A';
            })
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
                            Rule::unique('users')->whereNull('deleted_at'),
                        ],
            'email' => [
                            'required',
                            'string',
                            'email',
                            'max:255',
                            Rule::unique('users')->whereNull('deleted_at'),
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
        $id = Crypt::decrypt($request->id);
        // dd($id);
        $validator = Validator::make($request->all(), [
            // 'id' => 'required|exists:users,id',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'middle_name' => 'required|string|max:255',
            'province' => 'required|string|max:255',
            'position' => 'required|string|max:255',
            'mobile_number' => [
                            'required',
                            'string',
                            
                            'max:255',
                            Rule::unique('users')->whereNull('deleted_at')->ignore($id),
                        ],
            'email' => [
                            'required',
                            'string',
                            'email',
                            'max:255',
                            Rule::unique('users')->whereNull('deleted_at')->ignore($id),
                        ],
            // 'password' => 'required|string|min:8',
            'role_id' => 'required|exists:role,id',
        ],[
            'role_id.required' => 'The role field is required'
        ]);
    
        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 200);
        }
    
        $user = User::findOrFail($id);
    
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
            // 'id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 200);
        }

        $user = User::findOrFail(Crypt::decrypt($request->id));
        $user->delete();

        return response()->json(['success' => true, 'message' => 'User deleted successfully']);
    }

    public function temp_password(Request $request){

        $user = User::findOrFail(Crypt::decrypt($request->id));

        $randomString = Str::random(10);

        $user->is_change_password = 1;
        $user->password = Hash::make($randomString);
        $user->save();

        // Send the temporary password to the user's email
        Mail::to($user->email)->send(new TempPasswordMail($randomString));

        return response()->json(['data' => $randomString, 'success' => true, 'message' => 'Successfully created a temporary password.']);
    }

    public function proxy(Request $request){

        $user = User::findOrFail(Crypt::decrypt($request->id));

        $randomString = Str::random(10);

        $user->proxy_password = Hash::make($randomString);
        
        $loginS = new LoginModel();
        $loginS->status = 'Proxy Logged In';
        $loginS->user_id =$user->id;
        $loginS->save();


        Auth::login($user);

        return response()->json(['success' => true, 'redirect' => $this->redirectTo]);

    }

    public function changeStatus(Request $request)
    {
        $user = User::findOrFail(Crypt::decrypt($request->id));
        if ($user) {
            $user->status = $request->status;
            $user->save();

            return response()->json(['success' => true, 'message' => 'User status updated successfully']);
        }

        return response()->json(['success' => false, 'message' => 'User not found']);
    }


    public function twoAuthDisabled(Request $request)
    {
            $user = User::findOrFail(Crypt::decrypt($request->id));
            $user->is_two_factor_enabled = 0;
            $user->is_two_factor_verified = 0;
            $user->twofa_secret = null;
            $user->save();

            return response()->json(['success' => true, 'message' => 'Two Factor Authentication Disabled Successfully.']);
       
    }


    
}
