<?php

namespace Horlerdipo\SimpleOtp\Channels;

use Exception;
use Horlerdipo\SimpleOtp\Contracts\ChannelContract;
use Horlerdipo\SimpleOtp\DTOs\VerifyOtpResponse;
use Horlerdipo\SimpleOtp\Exceptions\InvalidOtpExpirationTimeException;
use Horlerdipo\SimpleOtp\Exceptions\InvalidOtpLengthException;
use Horlerdipo\SimpleOtp\Models\Otp as OtpModel;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

abstract class BaseChannel implements ChannelContract
{
    public function __construct(
        public int $length,
        public int $expiresIn,
        public bool $hashToken,
        public string $template,
        public bool $numbersOnly,
    ) {}

    public function hash(bool $hash = true): self
    {
        $this->hashToken = $hash;

        return $this;
    }

    /**
     * @throws InvalidOtpLengthException
     */
    public function length(int $length): self
    {
        if ($length < 1) {
            throw new InvalidOtpLengthException('OTP length must be greater than 0');
        }
        $this->length = $length;

        return $this;
    }

    /**
     * @throws InvalidOtpExpirationTimeException
     */
    public function expiresIn(int $expiresIn): self
    {
        if ($expiresIn < 1) {
            throw new InvalidOtpExpirationTimeException('OTP expiration time must be greater than 0');
        }
        $this->expiresIn = $expiresIn;

        return $this;
    }

    public function numbersOnly(bool $numbersOnly = true): self
    {
        $this->numbersOnly = $numbersOnly;

        return $this;
    }

    public function template(string $template): self
    {
        $this->template = $template;

        return $this;
    }

    /**
     * @throws InvalidOtpLengthException
     * @throws Exception
     */
    public function generateOtp(int $length = 6, bool $onlyNumbers = true): string
    {
        if ($length < 1) {
            throw new InvalidOtpLengthException('OTP length must be at least 1');
        }

        if ($onlyNumbers) {
            return $this->generateNumericOtp($length);
        } else {
            return $this->generateAlphanumericOtp($length);
        }
    }

    /**
     * @throws Exception
     */
    private function generateNumericOtp(int $length): string
    {
        $min = pow(10, $length - 1);
        $max = pow(10, $length) - 1;

        return strval(random_int($min, $max));
    }

    private function generateAlphanumericOtp(int $length): string
    {
        return Str::upper(Str::random($length));
    }

    protected function storeOtp(string $destination, string $token, string $purpose, int $expiration, bool $hashToken): void
    {
        OtpModel::query()->updateOrCreate([
            'destination' => $destination,
            'destination_type' => $this->channelName(),
            'purpose' => $purpose,
        ], [
            'expires_at' => now()->addMinutes($expiration),
            'is_used' => false,
            'is_hashed' => $hashToken,
            'token' => $hashToken ? Hash::make($token) : $token,
        ]);
    }

    public function verifyOtp(string $destination, string $token, string $purpose, bool $use = true): VerifyOtpResponse
    {

        $otpRecord = OtpModel::query()
            ->where('destination_type', $this->channelName())
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

    abstract public function channelName(): string;
}
