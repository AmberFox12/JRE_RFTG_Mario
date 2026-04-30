<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\ToadStaffService;
use App\Auth\ToadUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */
    private ToadStaffService $staffService;

    public function __construct(ToadStaffService $staffService){
         $this->staffService = $staffService;
        $this->middleware('guest');
    }

    public function register(Request $request)
    {
        $this -> validate($request, [
            'firstName' => 'required|string|max:255',
            'lastName' => 'required|string|max:255',
            'username' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:8|confirmed'
        ]);

        $data = [
            'firstName' => $request->input('firstName'),
            'lastName' => $request->input('lastName'),
            'username' => $request->input('username'),
            'email' => $request->input('email'),
            'password' => $request->input('password'),
            'lastUpdate' => now()->toIso8601String(),
        ];
        $staff = $this->staffService->createStaff($data);
        $message = 'Erreur dans l\'enregistrement de votre compte';
        if($staff === null){
            return back()->withInput()->with('error', $message);
        }
        $userData = [
            'id'        => $staff['staffId'] ?? $staff['id'] ?? $staff['email'],
            'firstName'  => $staff['firstName'] ?? null,
            'lastName'  => $staff['lastName'] ?? null,
            'username'  => $staff['username'] ?? null,
            'email'     => $staff['email'] ?? null,
            'token'     => $staff['token'] ?? $staff['access_token'] ?? null, // token JWT Toad si renvoyé
            'staff'     => $staff, // on garde toutes les infos utiles
        ];
    
        $user = new ToadUser($userData);
        $request->session()->put('toad_user', $userData);
        Auth::login($user, false);
        return redirect($this->redirectTo);
    }
    
    protected function showRegistrationForm()
    {
        return view('auth.register');
    }

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */ 
}
