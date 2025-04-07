<?php

namespace Horlerdipo\SimpleOtp\Contracts;

interface OtpContract
{
    public function send(string $destination, string $purpose, array $templateData): void;

    public function verify(string $destination, string $purpose, string $token, array $options = []): array;

    public function channel(): string;

    //    public function template(string $template): self;
    //
    //    public function hash(bool $hash): self;
    //
    //    public function length(int $length): self;
    //
    //    public function expiresIn(int $expiresIn): self;
    //
    //    public function numbersOnly(bool $numbersOnly): self;

}
