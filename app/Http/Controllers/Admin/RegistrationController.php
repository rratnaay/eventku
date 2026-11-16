<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Registration;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RegistrationController extends Controller
{
    public function index(Request $request)
    {
        $registrations = Registration::with('user', 'event')
            ->when($request->query('event'), fn ($q, $e) => $q->where('event_id', $e))
            ->when($request->query('status'), fn ($q, $s) => $q->where('status', $s))
            ->when($request->query('q'), fn ($q, $t) => $q->where(fn ($w) => $w
                ->where('code', 'like', "%{$t}%")
                ->orWhere('participant_name', 'like', "%{$t}%")
                ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$t}%")->orWhere('email', 'like', "%{$t}%"))))
            ->latest('registered_at')->paginate(15)->withQueryString();

        return view('admin.registrations.index', [
            'registrations' => $registrations,
            'events' => Event::orderBy('title')->get(['id', 'title']),
        ]);
    }

    public function update(Request $request, Registration $registration)
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(array_keys(Registration::STATUSES))],
        ]);

        // Mengaktifkan kembali pendaftaran yang dibatalkan harus tetap menghormati kuota.
        if ($registration->isCancelled() && $data['status'] !== Registration::STATUS_CANCELLED) {
            $event = $registration->event()->withSeatCount()->first();
            if ($event->is_full) {
                return back()->with('error', 'Kuota event penuh, pendaftaran tidak dapat diaktifkan kembali.');
            }
        }

        if (in_array($data['status'], [Registration::STATUS_CONFIRMED, Registration::STATUS_ATTENDED], true)) {
            $data['verified_at'] = $registration->verified_at ?? now();
            if ($registration->payment_amount > 0 || ! $registration->event?->is_free) {
                $data['payment_status'] = Registration::PAYMENT_PAID;
            }
        } elseif ($data['status'] === Registration::STATUS_PENDING) {
            $data['verified_at'] = null;
            if ($registration->payment_amount > 0) {
                $data['payment_status'] = $registration->payment_proof
                    ? Registration::PAYMENT_WAITING_VERIFICATION
                    : Registration::PAYMENT_UNPAID;
            }
        }

        $registration->update($data);

        return back()->with('success', 'Status pendaftaran diperbarui.');
    }
}
