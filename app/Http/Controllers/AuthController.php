<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('username', $credentials['username'])->first();

        if (! $user || ! Auth::validate(['username' => $credentials['username'], 'password' => $credentials['password']])) {
            return back()->withErrors(['username' => 'Invalid username or password.'])->withInput();
        }

        if (! $user->is_active) {
            return back()->withErrors(['username' => 'Account is disabled.']);
        }

        Auth::login($user, $request->boolean('remember'));

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'username'   => ['required', 'string', 'max:64', 'unique:users,username'],
            'email'      => ['required', 'email', 'max:120', 'unique:users,email'],
            'full_name'  => ['required', 'string', 'max:128'],
            'phone'      => ['nullable', 'string', 'max:20'],
            'password'   => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $user = User::create([
            'username'  => $data['username'],
            'email'     => $data['email'],
            'full_name' => $data['full_name'],
            'phone'     => $data['phone'] ?? null,
            'password'  => $data['password'],
            'role'      => 'staff',
            'is_active' => true,
        ]);

        Auth::login($user);

        return redirect()->route('dashboard');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}