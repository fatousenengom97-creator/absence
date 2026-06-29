<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function showDebug()
    {
        return view('auth.login_debug');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user();

            \Log::info('Login OK', ['email' => $user->email, 'role' => $user->role]);

            return match ($user->role) {
                'administrateur' => redirect()->route('admin.dashboard'),
                'etudiant'       => redirect()->route('etudiant.dashboard'),
                'professeur'     => redirect()->route('professeur.dashboard'),
                'chef_service'   => redirect()->route('chef.dashboard'),
                default          => redirect('/'),
            };
        }

        \Log::warning('Login ECHEC', ['email' => $request->email]);

        return back()->withErrors([
            'email' => 'Email ou mot de passe incorrect.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}