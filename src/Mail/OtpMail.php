<?php

namespace Horlerdipo\SimpleOtp\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OtpMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  array<string, mixed>  $templateData
     */
    public function __construct(protected string $token, protected string $template, protected array $templateData = []) {}

    public function build(): OtpMail
    {
        return $this->view($this->template)
            ->subject($this->templateData['subject'] ?? 'OTP Mail')
            ->with([
                'token' => $this->token,
                ...$this->templateData,
            ]);
    }
}
