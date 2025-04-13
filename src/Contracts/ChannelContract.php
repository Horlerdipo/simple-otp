<?php

namespace Horlerdipo\SimpleOtp\Contracts;

interface ChannelContract
{
    public function template(string $template): self;

    public function hash(bool $hash): self;

    public function length(int $length): self;

    public function expiresIn(int $expiresIn): self;

    public function numbersOnly(bool $numbersOnly): self;

    public function channelName(): string;
}
