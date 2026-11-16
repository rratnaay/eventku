<?php

namespace App\Http\Controllers;

use App\Models\Registration;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function edit(Request $request)
    {
        $user = $request->user();

        $stats = [
            'total' => $user->registrations()->count(),
            'confirmed' => $user->registrations()->whereIn('status', [Registration::STATUS_CONFIRMED, Registration::STATUS_ATTENDED])->count(),
            'pending' => $user->registrations()->where('status', Registration::STATUS_PENDING)->count(),
            'cancelled' => $user->registrations()->where('status', Registration::STATUS_CANCELLED)->count(),
        ];

        return view('profile.edit', compact('user', 'stats'));
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:30', 'regex:/^[0-9+\-\s]{8,30}$/'],
            'institution' => ['nullable', 'string', 'max:120'],
            'city' => ['nullable', 'string', 'max:80'],
        ], [], [
            'name' => 'nama lengkap',
            'email' => 'email',
            'phone' => 'nomor WhatsApp',
            'institution' => 'instansi / komunitas',
            'city' => 'kota / domisili',
        ]);

        $user->update($data);

        return redirect()->route('profile.edit')->with('success', 'Profil berhasil diperbarui.');
    }
}
