<?php

namespace Horlerdipo\SimpleOtp\Concerns;

use Exception;
use Horlerdipo\SimpleOtp\Exceptions\InvalidOtpLengthException;
use Illuminate\Support\Str;
use Random\RandomException;

trait GeneratesOtp
{
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
     * @throws RandomException
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
}
