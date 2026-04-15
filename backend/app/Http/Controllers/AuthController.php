<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLoginForm(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => 'Lauks "E-pasts" ir obligāts.',
            'email.email' => 'Ievadiet derīgu e-pasta adresi.',
            'password.required' => 'Lauks "Parole" ir obligāts.',
        ]);

        if (! Auth::attempt($credentials)) {
            return back()->withErrors([
                'email' => 'Nepareizs e-pasts vai parole.',
            ])->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->intended(route('sakums'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('pieslegties');
    }
}
