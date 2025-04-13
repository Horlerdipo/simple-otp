<?php

use Horlerdipo\SimpleOtp\Channels\BlackHole;
use Horlerdipo\SimpleOtp\DTOs\VerifyOtpResponse;
use Horlerdipo\SimpleOtp\Enums\ChannelType;
use Horlerdipo\SimpleOtp\Exceptions\InvalidOtpExpirationTimeException;
use Horlerdipo\SimpleOtp\Exceptions\InvalidOtpLengthException;
use Horlerdipo\SimpleOtp\Facades\SimpleOtp;
use Horlerdipo\SimpleOtp\SimpleOtpManager;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

use function Pest\Laravel\assertDatabaseHas;

beforeEach(function () {
    $this->otpDestination = 'blackhole-avenue';
    $this->otpPurpose = 'blackhole-activation';
    config(['simple-otp.default_channel' => ChannelType::BLACKHOLE->value]);
});

it('can successfully set otp configurations', function () {
    // ARRANGE
    config()->set('simple-otp.email_template_location', 'not-mails-test');
    config()->set('simple-otp.hash', false);
    config()->set('simple-otp.expires_in', 1);
    config()->set('simple-otp.length', 1);
    config()->set('simple-otp.numbers_only', true);

    // ACT
    /** @var SimpleOtpManager $simpleOtp */
    $simpleOtp = SimpleOtp::template('mails-test')
        ->hash()
        ->length(10)
        ->expiresIn(10)
        ->numbersOnly(false);

    // ASSERT

    // testing template()
    expect($simpleOtp->template)->toBe('mails-test')
        ->and($simpleOtp->template)->not->toBe(config('simple-otp.email_template_location'))
        ->and($simpleOtp->hashToken)->toBeTrue()
        ->and($simpleOtp->template)->not->toBe(config('simple-otp.hash'))
        ->and($simpleOtp->length)->toBe(10)
        ->and($simpleOtp->length)->not->toBe(config('simple-otp.length'));

    // testing hash()

    // testing length()
    try {
        $simpleOtp->length(-1);
    } catch (\Exception $exception) {
        expect($exception)->toBeInstanceOf(InvalidOtpLengthException::class);
    }

    // testing expiresIn()
    expect($simpleOtp->expiresIn)->toBe(10)
        ->and($simpleOtp->expiresIn)->not->toBe(config('simple-otp.expires_in'));
    try {
        $simpleOtp->expiresIn(-1);
    } catch (\Exception $exception) {
        expect($exception)->toBeInstanceOf(InvalidOtpExpirationTimeException::class);
    }

    // testing numbersOnly()
    expect($simpleOtp->numbersOnly)->toBeFalse()
        ->and($simpleOtp->numbersOnly)->not->toBe(config('simple-otp.numbers_only'))

        // testing $this->channelName()
        ->and($simpleOtp->channelName())->toBe(ChannelType::BLACKHOLE->value)
        ->and($simpleOtp->channelName())->not->toBe(ChannelType::EMAIL->value);

});

it('can successfully generate unhashed otp', function () {
    // ARRANGE
    /** @var SimpleOtpManager|BlackHole $simpleOtp */
    $simpleOtp = SimpleOtp::template('mails-test')
        ->hash(false)
        ->length(6)
        ->expiresIn(10)
        ->numbersOnly();

    // ACT
    $simpleOtp->send($this->otpDestination, $this->otpPurpose);

    // ASSERT
    expect($simpleOtp->getToken())->toBeNumeric();
    assertDatabaseHas(config('simple-otp.table_name'), [
        'destination' => $this->otpDestination,
        'purpose' => $this->otpPurpose,
        'destination_type' => $simpleOtp->channelName(),
        'token' => $simpleOtp->getToken(),
        'is_used' => false,
        'is_hashed' => false,
    ]);
});

it('can successfully send hashed otp', function () {
    /** @var SimpleOtpManager|BlackHole $simpleOtp */
    $simpleOtp = SimpleOtp::template('mails-test')
        ->hash()
        ->length(6)
        ->expiresIn(10)
        ->numbersOnly();

    // ACT
    $simpleOtp->send($this->otpDestination, $this->otpPurpose);

    // ASSERT
    expect($simpleOtp->getToken())->toBeNumeric();
    assertDatabaseHas(config('simple-otp.table_name'), [
        'destination' => $this->otpDestination,
        'purpose' => $this->otpPurpose,
        'destination_type' => $simpleOtp->channelName(),
        'is_used' => false,
        'is_hashed' => true,
    ]);
    $token = DB::table(config('simple-otp.table_name'))
        ->where('destination', $this->otpDestination)
        ->where('purpose', $this->otpPurpose)
        ->where('destination_type', $simpleOtp->channelName())
        ->where('is_used', false)
        ->where('is_hashed', true)
        ->value('token');

    expect(Hash::check($simpleOtp->getToken(), $token))->toBeTrue();
});

it('successfully returns error on wrong otp', function () {
    // ARRANGE
    /** @var SimpleOtpManager|BlackHole $simpleOtp */
    $simpleOtp = SimpleOtp::template('mails-test')
        ->hash()
        ->length(6)
        ->expiresIn(10)
        ->numbersOnly();

    $simpleOtp->send($this->otpDestination, $this->otpPurpose);

    // ACT
    $response = $simpleOtp->verify($this->otpDestination, $this->otpPurpose, 'wrong-otp');

    // ASSERT
    expect($response)->toBeInstanceOf(VerifyOtpResponse::class);
    expect($response->status)->toBe(false);
    expect($response->message)->toBe(config('simple-otp.messages.incorrect_otp'));
});

it('successfully returns error on expired otp', function () {
    // ARRANGE
    /** @var SimpleOtpManager|BlackHole $simpleOtp */
    $simpleOtp = SimpleOtp::hash(false)
        ->length(6)
        ->expiresIn(10)
        ->numbersOnly();

    $simpleOtp->send($this->otpDestination, $this->otpPurpose);

    DB::table(config('simple-otp.table_name'))
        ->where('destination', $this->otpDestination)
        ->where('purpose', $this->otpPurpose)
        ->where('destination_type', $simpleOtp->channelName())
        ->where('is_used', false)
        ->where('is_hashed', false)
        ->update([
            'expires_at' => now()->subDay(),
        ]);

    // ACT
    $response = $simpleOtp->verify($this->otpDestination, $this->otpPurpose, $simpleOtp->getToken());

    // ASSERT
    expect($response)->toBeInstanceOf(VerifyOtpResponse::class)
        ->and($response->status)->toBeFalse()
        ->and($response->message)->toBe(config('simple-otp.messages.expired_otp'));
});

it('successfully returns error on used otp', function () {
    // ARRANGE
    /** @var SimpleOtpManager|BlackHole $simpleOtp */
    $simpleOtp = SimpleOtp::hash(false)
        ->length(6)
        ->expiresIn(10)
        ->numbersOnly();

    $simpleOtp->send($this->otpDestination, $this->otpPurpose);

    DB::table(config('simple-otp.table_name'))
        ->where('destination', $this->otpDestination)
        ->where('purpose', $this->otpPurpose)
        ->where('destination_type', $simpleOtp->channelName())
        ->where('is_used', false)
        ->where('is_hashed', false)
        ->update([
            'is_used' => true,
        ]);

    // ACT
    $response = $simpleOtp->verify($this->otpDestination, $this->otpPurpose, $simpleOtp->getToken());

    // ASSERT
    expect($response)->toBeInstanceOf(VerifyOtpResponse::class)
        ->and($response->status)->toBeFalse()
        ->and($response->message)->toBe(config('simple-otp.messages.used_otp'));
});

it('can successfully verify unhashed otp', function () {
    // ARRANGE
    /** @var SimpleOtpManager|BlackHole $simpleOtp */
    $simpleOtp = SimpleOtp::hash(false)
        ->length(6)
        ->expiresIn(10)
        ->numbersOnly();

    $simpleOtp->send($this->otpDestination, $this->otpPurpose);

    // ACT
    $response = $simpleOtp->verify($this->otpDestination, $this->otpPurpose, $simpleOtp->getToken());

    // ASSERT
    expect($response)->toBeInstanceOf(VerifyOtpResponse::class)
        ->and($response->status)->toBeTrue()
        ->and($response->message)->toBe(config('simple-otp.messages.valid_otp'));
    assertDatabaseHas(config('simple-otp.table_name'), [
        'destination' => $this->otpDestination,
        'purpose' => $this->otpPurpose,
        'destination_type' => $simpleOtp->channelName(),
        'is_used' => true,
        'is_hashed' => false,
    ]);
});

it('can successfully verify hashed otp', function () {
    // ARRANGE
    /** @var SimpleOtpManager|BlackHole $simpleOtp */
    $simpleOtp = SimpleOtp::hash()
        ->length(6)
        ->expiresIn(10)
        ->numbersOnly();

    $simpleOtp->send($this->otpDestination, $this->otpPurpose);

    // ACT
    $response = $simpleOtp->verify($this->otpDestination, $this->otpPurpose, $simpleOtp->getToken());

    // ASSERT
    expect($response)->toBeInstanceOf(VerifyOtpResponse::class)
        ->and($response->status)->toBeTrue()
        ->and($response->message)->toBe(config('simple-otp.messages.valid_otp'));
    assertDatabaseHas(config('simple-otp.table_name'), [
        'destination' => $this->otpDestination,
        'purpose' => $this->otpPurpose,
        'destination_type' => $simpleOtp->channelName(),
        'is_used' => true,
        'is_hashed' => true,
    ]);
});

it('does not mark otp as used if the option is passed', function () {
    // ARRANGE
    /** @var SimpleOtpManager|BlackHole $simpleOtp */
    $simpleOtp = SimpleOtp::hash()
        ->length(6)
        ->expiresIn(10)
        ->numbersOnly();

    $simpleOtp->send($this->otpDestination, $this->otpPurpose);

    // ACT
    $response = $simpleOtp->verify($this->otpDestination, $this->otpPurpose, $simpleOtp->getToken(), [
        'use' => false,
    ]);

    // ASSERT
    expect($response)->toBeInstanceOf(VerifyOtpResponse::class)
        ->and($response->status)->toBeTrue()
        ->and($response->message)->toBe(config('simple-otp.messages.valid_otp'));
    assertDatabaseHas(config('simple-otp.table_name'), [
        'destination' => $this->otpDestination,
        'purpose' => $this->otpPurpose,
        'destination_type' => $simpleOtp->channelName(),
        'is_used' => false,
        'is_hashed' => true,
    ]);
});
