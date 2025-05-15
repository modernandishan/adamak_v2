<?php

namespace App\Services;

use App\Models\OtpCode;
use Illuminate\Support\Facades\Log;
use IPPanel\Client;
use Exception;

class OtpService
{
    protected $smsClient;
    public function __construct()
    {
        $this->smsClient = new Client(env('IPPANEL_API_KEY'));
    }
    public function sendOtp(string $mobile): string
    {
        OtpCode::where('mobile', $mobile)->delete();
        $digits = (int) env('IPPANEL_OTP_DIGITS', 4);
        $min = pow(10, $digits - 1);
        $max = pow(10, $digits) - 1;
        $code = (string) rand($min, $max);
        OtpCode::create([
            'mobile' => $mobile,
            'code' => $code,
            'expires_at' => now()->addMinutes(2),
        ]);

        try {
            $formattedMobile = $this->formatMobileNumber($mobile);
            $patternValues = [
                "code" => $code,
            ];
            $bulkId = $this->smsClient->sendPattern(
                env('IPPANEL_REST_PASSWORD_PATTERN'),
                env('IPPANEL_ORIGIN_NUMBER'),
                $formattedMobile,
                $patternValues
            );

            Log::info("SMS sent to $mobile with pattern. Bulk ID: $bulkId, Code: $code");

            if (env('APP_ENV') !== 'production') {
                Log::info("Development mode: OTP code for $mobile: $code");
            }
        } catch (Exception $e) {
            Log::error("Failed to send OTP to $mobile: " . $e->getMessage());

            if (env('APP_ENV') !== 'production') {
                Log::warning("Development mode: Ignoring SMS error and returning code anyway");
                return $code;
            }

            throw $e;
        }

        return $code;
    }

    public function verifyOtp(string $mobile, string $code): bool
    {
        $otpCode = OtpCode::where('mobile', $mobile)
            ->where('code', $code)
            ->where('expires_at', '>', now())
            ->first();

        return $otpCode !== null;
    }

    private function formatMobileNumber(string $mobile): string
    {
        if (substr($mobile, 0, 2) === '09') {
            return '98' . substr($mobile, 1);
        }

        return $mobile;
    }
}
