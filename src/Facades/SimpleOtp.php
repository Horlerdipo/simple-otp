<?php

namespace Horlerdipo\SimpleOtp\Facades;

use Horlerdipo\SimpleOtp\Channels\Email;
use Horlerdipo\SimpleOtp\Contracts\OtpContract;
use Horlerdipo\SimpleOtp\DTOs\VerifyOtpResponse;
use Illuminate\Support\Facades\Facade;

/**
 * @see \Horlerdipo\SimpleOtp\SimpleOtpManager
 *
 * @method static void send(string $destination, string $purpose, array $templateData = [])
 * @method static VerifyOtpResponse verify(string $destination, string $purpose, string $token, array $options = [])
 * @method static self template(string $template)
 * @method static mixed|string getDefaultDriver()
 * @method static Email createEmailDriver()
 * @method static mixed channel(string|null $channel)
 * @method static string channelName()
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
