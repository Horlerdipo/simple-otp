<?php

namespace Horlerdipo\SimpleOtp\Contracts;

interface OtpContract
{
    public function send(string $destination, string $purpose, array $templateData): void;

    public function verify(string $destination, string $purpose, string $token, array $options = []): array;

    public function channel(): string;

    public function setTemplate(string $template): self;
}
