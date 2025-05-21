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
    public function updating(User $user): void
    {
        // بررسی تغییر شماره موبایل
        if ($user->isDirty('mobile') && $user->getOriginal('mobile') !== null) {
            $user->mobile_verified_at = null;
        }
    }
    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        //
    }

    /**
     * Handle the User "deleting" event.
     */
    public function deleting(User $user): void
    {
        // قبل از حذف کاربر، referrer_id کاربرانی که این کاربر را به عنوان معرف دارند را به NULL تغییر می‌دهیم
        User::where('referrer_id', $user->id)->update(['referrer_id' => null]);
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
