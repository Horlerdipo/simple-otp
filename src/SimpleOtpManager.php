<?php

namespace Horlerdipo\SimpleOtp;

use Exception;
use Horlerdipo\SimpleOtp\Channels\BlackHole;
use Horlerdipo\SimpleOtp\Channels\Email;
use Horlerdipo\SimpleOtp\Concerns\ConfiguresOtp;
use Horlerdipo\SimpleOtp\Contracts\OtpContract;
use Horlerdipo\SimpleOtp\DTOs\VerifyOtpResponse;
use Horlerdipo\SimpleOtp\Enums\ChannelType;
use Horlerdipo\SimpleOtp\Exceptions\OtpException;
use Illuminate\Support\Manager;

class SimpleOtpManager extends Manager implements OtpContract
{
    use ConfiguresOtp;

    public string $template = '';

    public ?bool $hashToken = null;

    public ?int $length = null;

    public ?int $expiresIn = null;

    public ?bool $numbersOnly = null;

    /**
     * @param  array<string, mixed>  $templateData
     *
     * @throws OtpException
     */
    public function send(string $destination, string $purpose, array $templateData = []): void
    {
        try {
            $this->driver()->send(destination: $destination, purpose: $purpose, templateData: $templateData);
        } catch (Exception $e) {
            throw new OtpException('Failed to send OTP: '.$e->getMessage());
        }
    }

    /**
     * @param  array{use?: bool}  $options
     *
     * @throws OtpException
     */
    public function verify(string $destination, string $purpose, string $token, array $options = []): VerifyOtpResponse
    {

        try {
            return $this->driver()->verify(destination: $destination, token: $token, purpose: $purpose, options: $options);
        } catch (Exception $e) {
            throw new OtpException('Failed to send OTP: '.$e->getMessage());
        }
    }

    /**
     * @return mixed|string
     */
    public function getDefaultDriver(): mixed
    {
        return $this->config->get('simple-otp.default_channel', ChannelType::EMAIL->value);
    }

    public function createEmailDriver(): Email
    {
        return new Email(
            length: is_null($this->length) ? $this->config->get('simple-otp.length') : $this->length,
            expiresIn: is_null($this->expiresIn) ? $this->config->get('simple-otp.expires_in') : $this->expiresIn,
            hashToken: is_null($this->hashToken) ? $this->config->get('simple-otp.hash') : $this->hashToken,
            template: ! empty($this->otpTemplate) ? $this->otpTemplate : $this->config->get('simple-otp.email_template_location'),
            numbersOnly: is_null($this->numbersOnly) ? $this->config->get('simple-otp.numbers_only') : $this->numbersOnly,
        );
    }

    public function createBlackHoleDriver(): BlackHole
    {
        return new BlackHole(
            length: is_null($this->length) ? $this->config->get('simple-otp.length') : $this->length,
            expiresIn: is_null($this->expiresIn) ? $this->config->get('simple-otp.expires_in') : $this->expiresIn,
            hashToken: is_null($this->hashToken) ? $this->config->get('simple-otp.hash') : $this->hashToken,
            template: ! empty($this->template) ? $this->template : $this->config->get('simple-otp.email_template_location'),
            numbersOnly: is_null($this->numbersOnly) ? $this->config->get('simple-otp.numbers_only') : $this->numbersOnly,
        );
    }

    public function channelName(): string
    {
        return $this->driver()->channelName();
    }

    public function channel(?string $channel = null): mixed
    {
        return $this->driver($channel);
    }
}
