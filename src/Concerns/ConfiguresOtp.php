<?php

namespace Horlerdipo\SimpleOtp\Concerns;

use Horlerdipo\SimpleOtp\Exceptions\InvalidOtpExpirationTimeException;
use Horlerdipo\SimpleOtp\Exceptions\InvalidOtpLengthException;

trait ConfiguresOtp
{
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
}
