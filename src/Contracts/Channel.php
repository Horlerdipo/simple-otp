<?php

namespace Horlerdipo\SimpleOtp\Contracts;

interface Channel
{
    public function sendOtp(string $destination, string $purpose, array $templateData): void;

    public function generateOtp(int $length, bool $onlyNumbers): string;

    public function verifyOtp(string $destination, string $token, string $purpose): array;
}
