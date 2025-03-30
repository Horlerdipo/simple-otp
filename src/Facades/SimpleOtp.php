<?php

namespace Horlerdipo\SimpleOtp\Facades;

use Horlerdipo\SimpleOtp\Contracts\OtpContract;
use Illuminate\Support\Facades\Facade;

/**
 * @see \Horlerdipo\SimpleOtp\SimpleOtp
 */
class SimpleOtp extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return OtpContract::class;
    }
}
