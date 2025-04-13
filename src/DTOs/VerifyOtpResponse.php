<?php

namespace Horlerdipo\SimpleOtp\DTOs;

class VerifyOtpResponse
{
    public function __construct(public readonly bool $status, public readonly string $message) {}

    public function status(): bool
    {
        return $this->status;
    }

    public function message(): string
    {
        return $this->message;
    }
}
