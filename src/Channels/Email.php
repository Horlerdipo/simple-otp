<?php

namespace Horlerdipo\SimpleOtp\Channels;

use Horlerdipo\SimpleOtp\Concerns\GeneratesOtp;
use Horlerdipo\SimpleOtp\Concerns\StoresOtp;
use Horlerdipo\SimpleOtp\Concerns\VerifiesOtp;
use Horlerdipo\SimpleOtp\Contracts\OtpContract;
use Horlerdipo\SimpleOtp\Enums\ChannelType;
use Horlerdipo\SimpleOtp\Exceptions\InvalidOtpLengthException;
use Horlerdipo\SimpleOtp\Mail\OtpMail;
use Illuminate\Queue\QueueManager;
use Illuminate\Support\Facades\Mail;

class Email implements OtpContract
{
    use GeneratesOtp, StoresOtp, VerifiesOtp;

    public function __construct(
        protected int $length,
        protected int $expiresIn,
        protected bool $hashToken,
        protected string $template,
        protected bool $numbersOnly,
    ) {}

    /**
     * @param  array<string, string>  $templateData
     *
     * @throws InvalidOtpLengthException
     */
    public function send(string $destination, string $purpose, array $templateData = [], string $queue = 'default'): void
    {

        $token = $this->generateOtp($this->length, $this->numbersOnly);
        $this->storeOtp(
            destination: $destination, token: $token, purpose: $purpose,
            expiration: $this->expiresIn, hashToken: $this->hashToken
        );

        (app(QueueManager::class)->push(function () use ($templateData, $purpose, $token, $destination) {
            Mail::to($destination)
                ->send(new OtpMail($token, $this->template, ['purpose' => $purpose, ...$templateData]));
        }, queue: $queue));
    }

    public function channel(): string
    {
        return ChannelType::EMAIL->value;
    }

    /**
     * @param  array<string, string>  $options
     * @return array<string, string>
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

    public function setTemplate(string $template): self
    {
        $this->template = $template;

        return $this;
    }
}
