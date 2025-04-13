<?php

use Horlerdipo\SimpleOtp\Channels\Email;
use Horlerdipo\SimpleOtp\DTOs\VerifyOtpResponse;
use Horlerdipo\SimpleOtp\Enums\ChannelType;
use Horlerdipo\SimpleOtp\Mail\OtpMail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

use function Pest\Laravel\assertDatabaseHas;

beforeEach(function () {
    Mail::fake();
    config()->set('simple-otp.email_template_location', 'mails.otp');
    $this->otpDestination = 'email-avenue';
    $this->otpPurpose = 'email-activation';
    $this->emailChannel = new Email(
        length: config('simple-otp.length'),
        expiresIn: config('simple-otp.expires_in'),
        hashToken: config('simple-otp.hash'),
        template: config('simple-otp.email_template_location'),
        numbersOnly: config('simple-otp.numbers_only')
    );
});

it('can successfully configure otp configurations on emails', function () {
    expect($this->emailChannel)
        ->toBeInstanceOf(Email::class)
        ->and($this->emailChannel->channelName())->toBe(ChannelType::EMAIL->value)
        ->and($this->emailChannel->length)->toBe(config('simple-otp.length'))
        ->and($this->emailChannel->expiresIn)->toBe(config('simple-otp.expires_in'))
        ->and($this->emailChannel->hashToken)->toBe(config('simple-otp.hash'))
        ->and($this->emailChannel->template)->toBe(config('simple-otp.email_template_location'))
        ->and($this->emailChannel->numbersOnly)->toBe(config('simple-otp.numbers_only'));
});

it('can successfully send unhashed otp over email', function () {
    // ARRANGE
    $this->emailChannel->hashToken = false;

    // ACT
    $this->emailChannel->send($this->otpDestination, $this->otpPurpose);

    // ASSERT
    assertDatabaseHas(config('simple-otp.table_name'), [
        'destination' => $this->otpDestination,
        'purpose' => $this->otpPurpose,
        'destination_type' => $this->emailChannel->channelName(),
        'is_used' => false,
        'is_hashed' => false,
    ]);

    $token = DB::table(config('simple-otp.table_name'))
        ->where('destination', $this->otpDestination)
        ->where('purpose', $this->otpPurpose)
        ->where('destination_type', $this->emailChannel->channelName())
        ->where('is_used', false)
        ->where('is_hashed', false)
        ->value('token');

    Mail::assertSent(OtpMail::class, function (OtpMail $mail) use ($token) {
        $html = $mail->render();

        preg_match('/\b\d{6}\b/', $html, $matches);
        $extractedOtp = $matches[0] ?? null;

        $this->assertEquals($token, $extractedOtp);

        return $mail->hasTo($this->otpDestination) && $mail->assertSeeInHtml($token);
    });
});

it('can successfully send hashed otp over email', function () {
    // ARRANGE
    $this->emailChannel->hashToken = true;

    // ACT
    $this->emailChannel->send($this->otpDestination, $this->otpPurpose);

    // ASSERT
    assertDatabaseHas(config('simple-otp.table_name'), [
        'destination' => $this->otpDestination,
        'purpose' => $this->otpPurpose,
        'destination_type' => $this->emailChannel->channelName(),
        'is_used' => false,
        'is_hashed' => true,
    ]);

    Mail::assertSent(OtpMail::class, function (OtpMail $mail) {
        $html = $mail->render();

        preg_match('/\b\d{6}\b/', $html, $matches);
        $extractedOtp = $matches[0] ?? null;

        $token = DB::table(config('simple-otp.table_name'))
            ->where('destination', $this->otpDestination)
            ->where('purpose', $this->otpPurpose)
            ->where('destination_type', $this->emailChannel->channelName())
            ->where('is_used', false)
            ->where('is_hashed', true)
            ->value('token');

        expect(Hash::check($extractedOtp, $token))->toBeTrue();

        return $mail->hasTo($this->otpDestination) && $mail->assertSeeInHtml($extractedOtp);
    });
});

it('successfully returns error on wrong otp', function () {
    // ARRANGE
    $this->emailChannel->send($this->otpDestination, $this->otpPurpose);

    // ACT
    $response = $this->emailChannel->verify($this->otpDestination, $this->otpPurpose, 'wrong-otp');

    // ASSERT
    expect($response)->toBeInstanceOf(VerifyOtpResponse::class)
        ->and($response->status)->toBeFalse()
        ->and($response->message)->toBe(config('simple-otp.messages.incorrect_otp'));
});

it('successfully returns error on expired otp', function () {
    // ARRANGE
    $this->emailChannel->hashToken = false;
    $this->emailChannel->send($this->otpDestination, $this->otpPurpose);
    Mail::assertSent(OtpMail::class, function (OtpMail $mail) {
        $html = $mail->render();

        preg_match('/\b\d{6}\b/', $html, $matches);
        $this->token = $matches[0] ?? null;

        return $mail->hasTo($this->otpDestination) && $mail->assertSeeInHtml($this->token);
    });

    // ACT
    DB::table(config('simple-otp.table_name'))
        ->where('destination', $this->otpDestination)
        ->where('purpose', $this->otpPurpose)
        ->where('destination_type', $this->emailChannel->channelName())
        ->where('is_used', false)
        ->where('is_hashed', false)
        ->where('token', $this->token)
        ->update([
            'expires_at' => now()->subDay(),
        ]);

    $response = $this->emailChannel->verify($this->otpDestination, $this->otpPurpose, $this->token);

    // ASSERT
    expect($response)->toBeInstanceOf(VerifyOtpResponse::class)
        ->and($response->status)->toBeFalse()
        ->and($response->message)->toBe(config('simple-otp.messages.expired_otp'));
});

it('successfully returns error on used otp', function () {
    // ARRANGE
    $this->emailChannel->hashToken = false;
    $this->emailChannel->send($this->otpDestination, $this->otpPurpose);
    Mail::assertSent(OtpMail::class, function (OtpMail $mail) {
        $html = $mail->render();

        preg_match('/\b\d{6}\b/', $html, $matches);
        $this->token = $matches[0] ?? null;

        return $mail->hasTo($this->otpDestination) && $mail->assertSeeInHtml($this->token);
    });

    // ACT
    DB::table(config('simple-otp.table_name'))
        ->where('destination', $this->otpDestination)
        ->where('purpose', $this->otpPurpose)
        ->where('destination_type', $this->emailChannel->channelName())
        ->where('is_used', false)
        ->where('is_hashed', false)
        ->where('token', $this->token)
        ->update([
            'is_used' => true,
        ]);

    $response = $this->emailChannel->verify($this->otpDestination, $this->otpPurpose, $this->token);

    // ASSERT
    expect($response)->toBeInstanceOf(VerifyOtpResponse::class)
        ->and($response->status)->toBeFalse()
        ->and($response->message)->toBe(config('simple-otp.messages.used_otp'));
});

it('can successfully verify unhashed otp', function () {
    // ARRANGE
    $this->emailChannel->hashToken = false;
    $this->emailChannel->send($this->otpDestination, $this->otpPurpose);
    Mail::assertSent(OtpMail::class, function (OtpMail $mail) {
        $html = $mail->render();

        preg_match('/\b\d{6}\b/', $html, $matches);
        $this->token = $matches[0] ?? null;

        return $mail->hasTo($this->otpDestination) && $mail->assertSeeInHtml($this->token);
    });

    // ACT
    $response = $this->emailChannel->verify($this->otpDestination, $this->otpPurpose, $this->token);

    // ASSERT
    expect($response)->toBeInstanceOf(VerifyOtpResponse::class)
        ->and($response->status)->toBeTrue()
        ->and($response->message)->toBe(config('simple-otp.messages.valid_otp'));
    assertDatabaseHas(config('simple-otp.table_name'), [
        'destination' => $this->otpDestination,
        'purpose' => $this->otpPurpose,
        'destination_type' => $this->emailChannel->channelName(),
        'is_used' => true,
        'is_hashed' => false,
    ]);
});

it('can successfully verify hashed otp', function () {
    // ARRANGE
    $this->emailChannel->hashToken = true;
    $this->emailChannel->send($this->otpDestination, $this->otpPurpose);
    Mail::assertSent(OtpMail::class, function (OtpMail $mail) {
        $html = $mail->render();

        preg_match('/\b\d{6}\b/', $html, $matches);
        $this->token = $matches[0] ?? null;

        return $mail->hasTo($this->otpDestination) && $mail->assertSeeInHtml($this->token);
    });

    // ACT
    $response = $this->emailChannel->verify($this->otpDestination, $this->otpPurpose, $this->token);

    // ASSERT
    expect($response)->toBeInstanceOf(VerifyOtpResponse::class)
        ->and($response->status)->toBeTrue()
        ->and($response->message)->toBe(config('simple-otp.messages.valid_otp'));
    assertDatabaseHas(config('simple-otp.table_name'), [
        'destination' => $this->otpDestination,
        'purpose' => $this->otpPurpose,
        'destination_type' => $this->emailChannel->channelName(),
        'is_used' => true,
        'is_hashed' => true,
    ]);
});
