<?php

namespace App\Policies;

use App\Models\Registration;
use App\Models\User;

class RegistrationPolicy
{
    /** Hanya pemilik pendaftaran (atau admin) yang boleh melihat. */
    public function view(User $user, Registration $registration): bool
    {
        return $user->id === $registration->user_id || $user->isAdmin();
    }

    /** Hanya pemilik yang boleh membatalkan pendaftarannya sendiri. */
    public function cancel(User $user, Registration $registration): bool
    {
        return $user->id === $registration->user_id;
    }
}
