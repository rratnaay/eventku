<?php

namespace App\Http\Controllers;

use App\Exceptions\RegistrationException;
use App\Models\Event;
use App\Models\Registration;
use App\Services\RegistrationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class RegistrationController extends Controller
{
    public function __construct(private RegistrationService $service) {}

    public function index(Request $request)
    {
        $filters = $request->validate([
            'status' => ['nullable', 'in:confirmed,pending,cancelled'],
        ]);

        $user = $request->user();
        $activeFilter = $filters['status'] ?? null;

        $counts = [
            'all' => $user->registrations()->count(),
            'confirmed' => $user->registrations()->whereIn('status', [Registration::STATUS_CONFIRMED, Registration::STATUS_ATTENDED])->count(),
            'pending' => $user->registrations()->where('status', Registration::STATUS_PENDING)->count(),
            'cancelled' => $user->registrations()->where('status', Registration::STATUS_CANCELLED)->count(),
        ];

        $query = $user->registrations()
            ->with('event.category')
            ->latest('registered_at');

        match ($activeFilter) {
            'confirmed' => $query->whereIn('status', [Registration::STATUS_CONFIRMED, Registration::STATUS_ATTENDED]),
            'pending' => $query->where('status', Registration::STATUS_PENDING),
            'cancelled' => $query->where('status', Registration::STATUS_CANCELLED),
            default => null,
        };

        $registrations = $query->paginate(8)->withQueryString();

        return view('registrations.index', compact('registrations', 'counts', 'activeFilter'));
    }

    public function create(Request $request, Event $event)
    {
        abort_unless(
            in_array($event->status, [Event::STATUS_PUBLISHED, Event::STATUS_CLOSED]) || $request->user()?->isAdmin(),
            404
        );

        $event->loadCount([
            'registrations as active_registrations_count' => fn ($q) => $q->where('status', '!=', Registration::STATUS_CANCELLED),
        ])->load('category');

        $existing = $event->registrations()
            ->where('user_id', $request->user()->id)
            ->first();

        if ($existing && ! $existing->isCancelled()) {
            return redirect()->route('registrations.show', $existing)
                ->with('error', 'Anda sudah terdaftar pada event ini.');
        }

        if ($reason = $event->closedReason()) {
            return redirect()->route('events.show', $event)->with('error', $reason);
        }

        return view('registrations.create', [
            'event' => $event,
            'user' => $request->user(),
        ]);
    }

    public function store(Request $request, Event $event)
    {
        $user = $request->user();
        $hasPhoneInput = $request->has('phone');

        $request->merge([
            'name' => $request->has('name') ? $request->input('name') : $user->name,
            'email' => $request->has('email') ? $request->input('email') : $user->email,
            'phone' => $hasPhoneInput ? $request->input('phone') : $user->phone,
            'institution' => $request->has('institution') ? $request->input('institution') : $user->institution,
            'city' => $request->has('city') ? $request->input('city') : $user->city,
        ]);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150'],
            'phone' => [$hasPhoneInput ? 'required' : 'nullable', 'string', 'max:30', 'regex:/^[0-9+\-\s]{8,30}$/'],
            'institution' => ['nullable', 'string', 'max:120'],
            'city' => ['nullable', 'string', 'max:80'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'payment_method' => ['nullable', Rule::in(array_keys(Registration::PAYMENT_METHODS))],
            'payment_proof' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [], [
            'name' => 'nama lengkap',
            'email' => 'email',
            'phone' => 'nomor WhatsApp',
            'institution' => 'instansi / komunitas',
            'city' => 'kota / domisili',
            'notes' => 'catatan',
            'payment_method' => 'metode pembayaran',
            'payment_proof' => 'bukti pembayaran',
        ]);

        $proofPath = null;
        if ($request->hasFile('payment_proof')) {
            $proofPath = $request->file('payment_proof')->store(
                'payment-proofs',
                config('filesystems.poster_disk')
            );
            $data['payment_proof_path'] = $proofPath;
        }

        try {
            $registration = $this->service->register($user, $event, $data);
        } catch (RegistrationException $e) {
            if ($proofPath) {
                Storage::disk(config('filesystems.poster_disk'))->delete($proofPath);
            }

            return back()->withInput()->with('error', $e->getMessage());
        }

        $message = match (true) {
            $event->is_free => "Pendaftaran berhasil dikirim! Kode registrasi Anda: {$registration->code} (menunggu verifikasi).",
            (bool) $registration->payment_proof => "Pendaftaran dan bukti pembayaran berhasil dikirim! Kode: {$registration->code} (menunggu verifikasi admin).",
            default => "Pendaftaran berhasil dibuat! Kode: {$registration->code}. Silakan unggah bukti pembayaran pada halaman detail.",
        };

        return redirect()->route('registrations.index')
            ->with('success', $message);
    }

    public function show(Registration $registration)
    {
        Gate::authorize('view', $registration);

        $registration->load('event.category', 'user');

        return view('registrations.show', compact('registration'));
    }

    public function uploadPayment(Request $request, Registration $registration)
    {
        Gate::authorize('cancel', $registration);

        $data = $request->validate([
            'payment_method' => ['nullable', Rule::in(array_keys(Registration::PAYMENT_METHODS))],
            'payment_proof' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [], [
            'payment_method' => 'metode pembayaran',
            'payment_proof' => 'bukti pembayaran',
        ]);

        $proofPath = $request->file('payment_proof')->store(
            'payment-proofs',
            config('filesystems.poster_disk')
        );

        try {
            $this->service->uploadPaymentProof($registration, $proofPath, $data['payment_method'] ?? null);
        } catch (RegistrationException $e) {
            Storage::disk(config('filesystems.poster_disk'))->delete($proofPath);

            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('registrations.show', $registration)
            ->with('success', 'Bukti pembayaran berhasil diunggah dan sedang menunggu verifikasi admin.');
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
