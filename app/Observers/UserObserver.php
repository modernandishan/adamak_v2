<?php

namespace App\Observers;

use App\Models\User;
use Illuminate\Support\Str;

class UserObserver
{

    public function created(User $user): void
    {
        $user->profile()->create();
    }

    public function creating(User $user): void
    {
        if (empty($user->referral_code)) {
            $user->referral_code = $this->generateUniqueReferralCode();
        }
    }

    private function generateUniqueReferralCode(): string
    {
        do {
            $code = Str::random(8);
        } while (User::where('referral_code', $code)->exists());

        return $code;
    }

    public function updated(User $user): void
    {
        //
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        //
    }

    /**
     * Handle the User "restored" event.
     */
    public function restored(User $user): void
    {
        //
    }

    /**
     * Handle the User "force deleted" event.
     */
    public function forceDeleted(User $user): void
    {
        //
    }
}
