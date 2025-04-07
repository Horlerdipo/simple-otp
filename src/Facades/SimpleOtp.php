<?php

namespace Horlerdipo\SimpleOtp\Facades;

use Horlerdipo\SimpleOtp\Channels\Email;
use Horlerdipo\SimpleOtp\Contracts\OtpContract;
use Illuminate\Support\Facades\Facade;

/**
 * @see \Horlerdipo\SimpleOtp\SimpleOtpManager
 * @method static void send(string $destination, string $purpose, array $templateData = [])
 * @method static array{status: bool, message: string} verify(string $destination, string $purpose, string $token, array $options = [])
 * @method static self template(string $template)
 * @method static mixed|string getDefaultDriver()
 * @method static Email createEmailDriver()
 * @method static string channel()
 * @method static self hash(bool $hash = true)
 * @method static self length(int $length)
 * @method static self expiresIn(int $expiresIn)
 * @method static self numbersOnly(bool $numbersOnly = true)
 */
class SimpleOtp extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return OtpContract::class;
    }
}
