<?php

namespace App\Services;

use App\Models\OtpCode;
use Illuminate\Support\Facades\Log;

class OtpService
{
    /**
     * ارسال کد OTP به شماره موبایل
     *
     * @param string $mobile
     * @return string کد OTP تولید شده
     */
    public function sendOtp(string $mobile): string
    {
        // حذف کدهای قبلی این شماره موبایل
        OtpCode::where('mobile', $mobile)->delete();

        // تولید کد تصادفی 5 رقمی
        $code = rand(10000, 99999);

        // ذخیره کد در دیتابیس
        OtpCode::create([
            'mobile' => $mobile,
            'code' => $code,
            'expires_at' => now()->addMinutes(2), // منقضی شدن بعد از 2 دقیقه
        ]);

        // ارسال پیامک - در اینجا فقط لاگ می‌کنیم
        try {
            // در اینجا کد ارسال پیامک را قرار دهید
            // مثال: $this->sendSms($mobile, "کد تأیید شما: $code");

            // فعلاً فقط در لاگ ذخیره می‌کنیم
            Log::info("OTP code for $mobile: $code");
        } catch (\Exception $e) {
            Log::error("Failed to send OTP to $mobile: " . $e->getMessage());
            throw $e;
        }

        return $code;
    }

    /**
     * بررسی صحت کد OTP
     *
     * @param string $mobile
     * @param string $code
     * @return bool
     */
    public function verifyOtp(string $mobile, string $code): bool
    {
        $otpCode = OtpCode::where('mobile', $mobile)
            ->where('code', $code)
            ->where('expires_at', '>', now())
            ->first();

        return $otpCode !== null;
    }
}
