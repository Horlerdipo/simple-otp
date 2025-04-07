<?php

namespace Horlerdipo\SimpleOtp\Channels;

use Horlerdipo\SimpleOtp\Concerns\ConfiguresOtp;
use Horlerdipo\SimpleOtp\Concerns\GeneratesOtp;
use Horlerdipo\SimpleOtp\Concerns\StoresOtp;
use Horlerdipo\SimpleOtp\Concerns\VerifiesOtp;
use Horlerdipo\SimpleOtp\Contracts\OtpContract;
use Horlerdipo\SimpleOtp\Enums\ChannelType;
use Horlerdipo\SimpleOtp\Exceptions\InvalidOtpLengthException;
use Horlerdipo\SimpleOtp\Mail\OtpMail;
use Illuminate\Queue\QueueManager;
use Illuminate\Support\Facades\Mail;

class BlackHole implements OtpContract
{
    use GeneratesOtp, StoresOtp, VerifiesOtp;

    public string $token = '';

    public function __construct(
        public int $length,
        public int $expiresIn,
        public bool $hashToken,
        public string $template,
        public bool $numbersOnly,
    ) {}

    /**
     * @param  array<string, mixed>  $templateData
     *
     * @throws InvalidOtpLengthException
     */
    public function send(string $destination, string $purpose, array $templateData = [], string $queue = 'default'): void
    {

        $this->token = $this->generateOtp($this->length, $this->numbersOnly);
        $this->storeOtp(
            destination: $destination, token: $this->token, purpose: $purpose,
            expiration: $this->expiresIn, hashToken: $this->hashToken
        );
    }

    public function channel(): string
    {
        return ChannelType::BLACKHOLE->value;
    }

    /**
     * @param  array{use?: bool}  $options
     * @return array{status: bool, message: string}
     */
    public function verify(string $destination, string $purpose, string $token, array $options = []): array
    {
        return $this->verifyOtp(
            destination: $destination,
            token: $token,
            purpose: $purpose,
            use: $options['use'] ?? true
        );
    }

    public function getToken(): string {
        return $this->token;
    }
}
