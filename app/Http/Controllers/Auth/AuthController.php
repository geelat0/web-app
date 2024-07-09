<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\LoginModel;
use Carbon\Carbon;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class AuthController extends Controller
{

    protected $redirectTo = '/dashboard';

    public function index()
    {

        return view('auth.login');
    }

    public  function  login(Request $request){

        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $credentials = $request->only('email', 'password');

        if (auth()->attempt($credentials)) {

            if (auth()->user()->status == 'active') {

                // Get the current day of the week (1 to 7, where 1 represents Monday)
                $currentDayOfWeek = Carbon::now()->dayOfWeekIso;

                    $loginS = new LoginModel();
                    $loginS->date_time_in = now();
                    $loginS->status = 'Logged In';
                    // Associate the post with the authenticated user
                    $loginS->user_id =  Auth::id();
                    $loginS->save();
                    return response()->json(['success' => true]);
            }

            // Check if the user status is 'Active'
            if (auth()->user()->status == 'active') {
                $loginS = new LoginModel();
                $loginS->date_time_in = now();
                $loginS->status = 'Logged In';
                // Associate the post with the authenticated user
                $loginS->user_id =  Auth::id();
                $loginS->save();
                return response()->json(['success' => true]);
            } else {
                // User status is not 'Active', logout and show an error message
                auth()->logout();
                return response()->json(['success' => false, 'message' => 'Your account is not active , Please contact the Admin.'], 401);
            }

        }

        return response()->json(['success' => false, 'message' => 'Invalid credentials'], 401);

    }

    public function showLinkRequestForm()
    {
        return view('auth.email-forgot-password');
    }

    public function sendResetLinkEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
                    ? back()->with(['status' => __($status)])
                    : back()->withErrors(['email' => __($status)]);
    }

    public function showResetForm($token)
    {
        return view('auth.forget-pass', ['token' => $token]);
    }

    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        return $status === Password::PASSWORD_RESET
                    ? redirect()->route('/')->with('status', __($status))
                    : back()->withErrors(['email' => [__($status)]]);
    }
}
