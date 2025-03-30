<?php

namespace Horlerdipo\SimpleOtp\Contracts;

interface OtpConfig
{
    public function getLength(): int;

    public function getDefaultChannel(): string;

    public function getExpiresIn(): string;

    public function shouldHash(): string;

    public function getHandlers(): array;

    public function getEmailTemplate(): string;

    public function shouldOnlyContainNumbers(): bool;
}
