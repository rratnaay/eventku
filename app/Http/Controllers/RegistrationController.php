<?php

namespace App\Http\Controllers;

use App\Exceptions\RegistrationException;
use App\Models\Event;
use App\Models\Registration;
use App\Services\RegistrationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class RegistrationController extends Controller
{
    public function __construct(private RegistrationService $service)
    {
    }

    public function index(Request $request)
    {
        $registrations = $request->user()->registrations()
            ->with('event.category')
            ->latest('registered_at')
            ->paginate(8);

        return view('registrations.index', compact('registrations'));
    }

    public function store(Request $request, Event $event)
    {
        try {
            $registration = $this->service->register($request->user(), $event);
        } catch (RegistrationException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('registrations.show', $registration)
            ->with('success', 'Pendaftaran berhasil! Simpan kode registrasi Anda.');
    }

    public function show(Registration $registration)
    {
        Gate::authorize('view', $registration);

        $registration->load('event.category', 'user');

        return view('registrations.show', compact('registration'));
    }

    public function cancel(Registration $registration)
    {
        Gate::authorize('cancel', $registration);

        try {
            $this->service->cancel($registration);
        } catch (RegistrationException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('registrations.index')->with('success', 'Pendaftaran berhasil dibatalkan.');
    }
}
