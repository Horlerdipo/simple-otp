<?php

namespace Horlerdipo\SimpleOtp\Channels;

use Horlerdipo\SimpleOtp\Contracts\ChannelContract;
use Horlerdipo\SimpleOtp\Contracts\OtpContract;
use Horlerdipo\SimpleOtp\DTOs\VerifyOtpResponse;
use Horlerdipo\SimpleOtp\Enums\ChannelType;
use Horlerdipo\SimpleOtp\Exceptions\InvalidOtpExpirationTimeException;
use Horlerdipo\SimpleOtp\Exceptions\InvalidOtpLengthException;
use Horlerdipo\SimpleOtp\Exceptions\OtpException;
use Horlerdipo\SimpleOtp\Mail\OtpMail;
use Illuminate\Queue\QueueManager;
use Illuminate\Support\Facades\Mail;

class Email extends BaseChannel implements ChannelContract, OtpContract
{
    //
    public function __construct(
        public int $length,
        public int $expiresIn,
        public bool $hashToken,
        public string $template,
        public bool $numbersOnly,
    ) {
        parent::__construct($this->length, $this->expiresIn, $this->hashToken, $this->template, $this->numbersOnly);
    }

    /**
     * @param  array<string, mixed>  $templateData
     *
     * @throws InvalidOtpExpirationTimeException
     * @throws InvalidOtpLengthException
     * @throws OtpException
     */
    public function send(string $destination, string $purpose, array $templateData = [], string $queue = 'default'): void
    {
        try {
            $token = $this->generateOtp($this->length, $this->numbersOnly);
            $this->storeOtp(
                destination: $destination, token: $token, purpose: $purpose,
                expiration: $this->expiresIn, hashToken: $this->hashToken
            );

            (app(QueueManager::class)->push(function () use ($templateData, $purpose, $token, $destination) {
                Mail::to($destination)
                    ->send(new OtpMail($token, $this->template, ['purpose' => $purpose, ...$templateData]));
            }, queue: $queue));
        } catch (InvalidOtpLengthException|InvalidOtpExpirationTimeException $exception) {
            throw $exception;
        } catch (\Exception $e) {
            throw new OtpException($e->getMessage());
        }

    }

    /**
     * @param  array{use?: bool}  $options
     */
    public function verify(string $destination, string $purpose, string $token, array $options = []): VerifyOtpResponse
    {
        return $this->verifyOtp(
            destination: $destination,
            token: $token,
            purpose: $purpose,
            use: $options['use'] ?? true
        );
    }

    public function channelName(): string
    {
        return ChannelType::EMAIL->value;
    }
}
