<?php

namespace Horlerdipo\SimpleOtp;

use Exception;
use Horlerdipo\SimpleOtp\Channels\Email;
use Horlerdipo\SimpleOtp\Contracts\OtpContract;
use Horlerdipo\SimpleOtp\Enums\ChannelType;
use Horlerdipo\SimpleOtp\Exceptions\OtpException;
use Illuminate\Support\Manager;

class SimpleOtpManager extends Manager implements OtpContract
{
    public string $otpTemplate = '';

    /**
     * @param  array<string, mixed>  $templateData
     *
     * @throws OtpException
     */
    public function send(string $destination, string $purpose, array $templateData): void
    {

        try {
            $this->driver()->send(destination: $destination, purpose: $purpose, templateData: $templateData);
        } catch (Exception $e) {
            throw new OtpException('Failed to send OTP: '.$e->getMessage());
        }
    }

    /**
     * @param  array{use?: bool}  $options
     * @return array{status: bool, message: string}
     *
     * @throws OtpException
     */
    public function verify(string $destination, string $purpose, string $token, array $options = []): array
    {

        try {
            return $this->driver()->verify(destination: $destination, token: $token, purpose: $purpose, options: $options);
        } catch (Exception $e) {
            throw new OtpException('Failed to send OTP: '.$e->getMessage());
        }
    }

    /**
     * @return $this
     */
    public function setTemplate(string $template): self
    {
        $this->otpTemplate = $template;

        return $this;
    }

    /**
     * @return mixed|string
     */
    public function getDefaultDriver(): mixed
    {
        return $this->config->get('otp.default_channel', ChannelType::EMAIL->value);
    }

    public function createEmailDriver(): Email
    {
        return new Email(
            length: $this->config->get('simple-otp.length'),
            expiresIn: $this->config->get('simple-otp.expires_in'),
            hashToken: $this->config->get('simple-otp.hash'),
            template: ! empty($this->otpTemplate) ? $this->otpTemplate : $this->config->get('simple-otp.email_template_location'),
            numbersOnly: $this->config->get('simple-otp.numbers_only')
        );
    }

    public function channel(): string
    {
        return $this->driver()->channel();
    }
}
