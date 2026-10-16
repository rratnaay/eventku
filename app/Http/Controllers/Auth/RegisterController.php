<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;

class RegisterController extends Controller
{
    public function create()
    {
        return view('auth.register');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:30', 'regex:/^[0-9+\-\s]{8,30}$/'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ], [], ['name' => 'nama', 'phone' => 'nomor telepon', 'password' => 'kata sandi']);

        // Role tidak berasal dari input; pengguna baru selalu 'user'.
        $user = User::create($data);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('home')->with('success', "Selamat datang di EventKu, {$user->name}!");
    }
}
