<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function show()
    {
        return view('contact');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150'],
            'subject' => ['required', 'string', 'max:150'],
            'message' => ['required', 'string', 'min:10', 'max:3000'],
        ], [], ['name' => 'nama', 'subject' => 'subjek', 'message' => 'pesan']);

        ContactMessage::create($data);

        return redirect()->route('contact')->with('success', 'Terima kasih! Pesan Anda sudah kami terima dan akan segera dibalas.');
    }
}
