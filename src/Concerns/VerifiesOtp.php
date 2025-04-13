<?php

namespace Horlerdipo\SimpleOtp\Concerns;

use Horlerdipo\SimpleOtp\DTOs\VerifyOtpResponse;
use Horlerdipo\SimpleOtp\Models\Otp as OtpModel;
use Illuminate\Support\Facades\Hash;

trait VerifiesOtp
{
    /**
     * @param string $destination
     * @param string $token
     * @param string $purpose
     * @param bool $use
     * @return VerifyOtpResponse
     */
    public function verifyOtp(string $destination, string $token, string $purpose, bool $use = true): VerifyOtpResponse
    {

        $otpRecord = OtpModel::query()
            ->where('destination', $destination)
            ->where('purpose', $purpose)
            ->orderBy('created_at', 'desc')
            ->first();

        if (! $otpRecord) {
            return new VerifyOtpResponse(
                false,
                config('simple-otp.messages.incorrect_otp')
            );
        }

        if ($otpRecord->is_hashed) {
            $isTokenCorrect = Hash::check($token, $otpRecord->token);
            if (! $isTokenCorrect) {
                return new VerifyOtpResponse(
                    false,
                    config('simple-otp.messages.incorrect_otp')
                );
            }
        } else {
            if ($otpRecord->token !== $token) {
                return new VerifyOtpResponse(
                    false,
                    config('simple-otp.messages.incorrect_otp')
                );
            }
        }

        if ($otpRecord->is_used) {
            return new VerifyOtpResponse(
                false,
                config('simple-otp.messages.used_otp')
            );
        }

        if ($otpRecord->expires_at <= now()) {
            return new VerifyOtpResponse(
                false,
                config('simple-otp.messages.expired_otp')
            );
        }

        if ($use) {
            $otpRecord->update(['is_used' => true]);
        }

        return new VerifyOtpResponse(
            true,
            config('simple-otp.messages.valid_otp')
        );
    }
}
