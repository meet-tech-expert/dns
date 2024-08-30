<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class RegisterController extends Controller
{
    public function create()
    {
        return view('session.register');
    }

    public function store()
    {
        $attributes = request()->validate([
            'name' => ['required', 'max:50'],
            'email' => ['required', 'email', 'max:50', Rule::unique('users', 'email')],
            'password' => ['required', 'min:5', 'max:20'],
            'agreement' => ['accepted']
        ]);
        $attributes['password'] = bcrypt($attributes['password']);
        session()->flash('success', 'Your account has been created.');
        $user = User::create($attributes);
        Auth::login($user);
        return redirect('/dashboard');
    }
    /**
     * Handle Social login request
     *
     * @return response
     */
    public function socialLogin($social)
    {
        return Socialite::driver($social)->redirect();
    }
    /**
     * Obtain the user information from Social Logged in.
     * @param $social
     * @return Response
     */
    public function handleProviderCallback($social)
    {
        $userSocial = Socialite::driver($social)->user();
        // var_dump($social);
        // dd($userSocial);

        $existingUser = User::where(['email' => $userSocial->email])->first();
        if ($existingUser) {
            $existingUser->update([
                'provider' => ($social == 'twitter') ? 'x' : $social,
                'provider_id' => $userSocial->id,
            ]);
            Auth::login($existingUser);
        } else {
            // Create a new user account
            $newUser = User::create([
                'name'  => $userSocial->name,
                'email' => $userSocial->email,
                'provider' => ($social == 'twitter') ? 'x' : $social,
                'provider_id' => $userSocial->id,
            ]);
            Auth::login($newUser);
        }
        return redirect('/dashboard');
    }
}
