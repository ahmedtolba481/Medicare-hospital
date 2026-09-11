<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/* Handles patient registration for the shared authentication foundation. */

class RegisterController extends Controller
{
    public function create(): mixed
    {
        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:1000'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'password' => ['required', 'confirmed', 'string', 'min:8'],
        ]);

        $user = User::create([...$validated, 'role' => 'patient']);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended(route($user->dashboardRoute()))->with('status', 'Registration successful.');
    }
}
