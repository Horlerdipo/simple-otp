<?php

namespace Horlerdipo\SimpleOtp\Contracts;

use Horlerdipo\SimpleOtp\DTOs\VerifyOtpResponse;

interface OtpContract
{
    public function send(string $destination, string $purpose, array $templateData): void;

    public function verify(string $destination, string $purpose, string $token, array $options = []): VerifyOtpResponse;

    public function channelName(): string;

}
