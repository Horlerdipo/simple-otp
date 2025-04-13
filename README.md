# A OTP implementation for Laravel 

[![Latest Version on Packagist](https://img.shields.io/packagist/v/horlerdipo/simple-otp.svg?style=flat-square)](https://packagist.org/packages/horlerdipo/simple-otp)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/horlerdipo/simple-otp/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/horlerdipo/simple-otp/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/horlerdipo/simple-otp/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/horlerdipo/simple-otp/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/horlerdipo/simple-otp.svg?style=flat-square)](https://packagist.org/packages/horlerdipo/simple-otp)

This Laravel package provides a flexible and pluggable One-Time Password (OTP) system, supporting multiple delivery channels like Email and custom drivers. 

[//]: # (## Support us)

[//]: # ()
[//]: # ([<img src="https://github-ads.s3.eu-central-1.amazonaws.com/simple-otp.jpg?t=1" width="419px" />]&#40;https://spatie.be/github-ad-click/simple-otp&#41;)

[//]: # ()
[//]: # (We invest a lot of resources into creating [best in class open source packages]&#40;https://spatie.be/open-source&#41;. You can support us by [buying one of our paid products]&#40;https://spatie.be/open-source/support-us&#41;.)

[//]: # ()
[//]: # (We highly appreciate you sending us a postcard from your hometown, mentioning which of our package&#40;s&#41; you are using. You'll find our address on [our contact page]&#40;https://spatie.be/about-us&#41;. We publish all received postcards on [our virtual postcard wall]&#40;https://spatie.be/open-source/postcards&#41;.)

## Introduction
- **Overview**
- **Features**
- **Requirements**
- **Installation**

## Quick Start
- **Basic Setup**
- **Sending an OTP**
- **Verifying an OTP**
- **Configuration Overview**

## Channel Guide

### Email Channel
- **Overview**
- **Example Usage**

### BlackHole Channel
- **Overview**
- **Example Usage**

## Advanced Usage

### Adding Custom Channels
- **Creating a custom channel class**
- **Required methods**
- **Registering the channel**
- **Usage**
- **Example Channel Implementation** 

### Using the Manager Class directly

## Troubleshooting & FAQ
- **Common Issues**
- **Debugging Tips**

## Testing

## Changelog
## Contributing
## Security Vulnerabilities
## Credits
## License


## Introduction

### Overview
This Laravel package provides a flexible and pluggable One-Time Password (OTP) system, supporting multiple delivery channels like Email and custom drivers. You can get up and running with a full OTP system with just a couple of lines

### Features
- OTP generation and validation
- Facade support for simple usage
- Built-in Email and Null channels
- Easy integration of custom delivery channels
- Coming soon: Twilio, Termii SMS channels, Redis Storage for OTPs
- Coming soon: TOTP (Time-based One-Time Password) 

### Requirements 
- PHP 8.1+
- Laravel 9.x or higher

### Installation
You can install the package via composer:


```bash

composer require horlerdipo/simple-otp

```


You can publish and run the migrations with:


```bash

php artisan vendor:publish --tag="simple-otp-migrations"

php artisan migrate

```


You can publish the config file with:


```bash

php artisan vendor:publish --tag="simple-otp-config"

```
This is the contents of the published config file:

```php

return [
    'length' => env('OTP_LENGTH', 6),

    'default_channel' => ChannelType::EMAIL->value,

    'expires_in' => env('OTP_EXPIRATION_TIME', 10),

    'hash' => false,

    'email_template_location' => 'vendor.simple-otp.mails.otp',

    'numbers_only' => true,

    'table_name' => 'otps',

    'messages' => [
        'incorrect_otp' => 'This OTP is incorrect',
        'used_otp' => 'This OTP has already been used',
        'expired_otp' => 'This OTP has expired',
        'valid_otp' => 'This OTP is correct',
    ],
];

```

If you would like to use the package email template(you shouldn't 😂), you can publish the views using

```bash

php artisan vendor:publish --tag="simple-otp-views"

```

## Quickstart

### Basic Setup
After installation, make sure your .env and config/otp.php are configured correctly, the default channel is Email so OTPs will be sent to emails.
You can change the default channel on the config file at runtime(add link to the channel) as well.

### Sending an OTP
```php
use Horlerdipo\SimpleOtp\Facades\SimpleOtp;
SimpleOtp::send(destination: "test@laravel.com", purpose: "login");
```
### Verifying an OTP
```php
use Horlerdipo\SimpleOtp\Facades\SimpleOtp;
$response = SimpleOtp::verify(destination: "test@laravel.com", purpose: "login", token: "267799");
```
The ```verify()``` method returns a ```VerifyOtpResponse``` object that has a ```status``` which is a boolean that is true if the OTP is 
correct and false if it is not, the object also has ```message``` property that contains the reason why the OTP is not correct.

### Configuration Overview
You can configure OTP generation using method chaining before calling ```send()``` method
- ```length(int $length)``` : This is to set the OTP length, default is 6
- ```expiresIn(int $minutes)``` : This is to set how long the OTP will last, default is 10 minutes
- ```numbersOnly(bool $bool)``` : This is to set if the generated OTP should contain letters or not, default is false
- ```template(string $template)``` : This is to set the template that will be used to send the OTP, default is vendor.simple-otp.mails.otp
- ```hash(bool $bool)``` : This is to set if the OTP should be hashed before it is saved into the database or not, default is false
- ```channel(string $channel)``` : This is to set the channel that will be used to send the OTP, if ```null``` is set, default is email
- ```channelName()``` : This returns the name of the channel in use, this method cannot be chained like the ones above

Example
```php
use Horlerdipo\SimpleOtp\Facades\SimpleOtp;

SimpleOtp::channel(\Horlerdipo\SimpleOtp\Enums\ChannelType::EMAIL->value)
    ->template('vendor.simple-otp.mails.otp')
    ->length(6)
    ->expiresIn(1)
    ->numbersOnly()
    ->hash(false)
    ->send("test@laravel.com", "testing");
```
## Channel Guide 

### Email Channel
#### Overview
Ensure your mail configuration is properly set in the .env. The email template is defined in config/simple-otp.php.

#### Example Usage
```php
use Horlerdipo\SimpleOtp\Facades\SimpleOtp;
SimpleOtp::channel('email')
    ->send('test@laravel.com', 'password_reset');
```
### BlackHole Channel
#### Overview
This channel was created primarily for testing or development. It simulates OTP sending without actually delivering the OTP.
It comes with a ```getToken()``` method that returns the token that was generated, this is useful in a scenario where you would like
to send the OTP in some other way the package is not shipped with, and you do not want to create a custom channel for it.

#### Example Usage
```php
use Horlerdipo\SimpleOtp\Facades\SimpleOtp;
SimpleOtp::channel('blackhole')
    ->send('test@laravel.com', '2fa');
```

## Advanced Usage
### Adding Custom Channels
#### Creating a custom channel class
 The custom channel class must implement the ```Horlerdipo\SimpleOtp\Contracts\OtpContract``` and the ```Horlerdipo\SimpleOtp\Contracts\ChannelContract```.
You can simply extend the ```Horlerdipo\SimpleOtp\Channels\BaseChannel``` abstract class to get predefined methods to speed up your custom channel development

#### Registering the channel
The custom service is registered by calling the ```Horlerdipo\SimpleOtp\Facades\SimpleOtp::extend()``` method in the ```register```
method of the service provider
```php
class AppServiceProvider extends ServiceProvider
{

    public function register(): void
    {
        $this->app->booting(function () {
            SimpleOtpManager::extend('sms', function () {
                return new SmsChannel(
                    length: config()->get('simple-otp.length'),
                    expiresIn: config()->get('simple-otp.expires_in'),
                    hashToken: config()->get('simple-otp.hash'),
                    template: config()->get('simple-otp.email_template_location'),
                    numbersOnly: config()->get('simple-otp.numbers_only'),
                );
            });
        });
    }
}
```
#### Custom Channel Usage
The newly registered channel can now be used by either changing the ```default_channel``` to ```sms``` or the name added while registering or
using it in the channel 
```php
use Horlerdipo\SimpleOtp\Facades\SimpleOtp;
SimpleOtp::channel('sms')
    ->send('+23470345480896', '2fa');
```
#### Example Channel Implementation 
```php
namespace App\Channels;

use Horlerdipo\SimpleOtp\Channels\BaseChannel;
use Horlerdipo\SimpleOtp\Contracts\ChannelContract;
use Horlerdipo\SimpleOtp\Contracts\OtpContract;
use Horlerdipo\SimpleOtp\DTOs\VerifyOtpResponse;
use Horlerdipo\SimpleOtp\Exceptions\InvalidOtpLengthException;

class SmsChannel extends BaseChannel implements OtpContract, ChannelContract
{
    public function channelName(): string
    {
        return 'sms';
    }

    /**
     * @throws InvalidOtpLengthException
     */
    public function send(string $destination, string $purpose, array $templateData): void
    {
        $token = $this->generateOtp($this->length, $this->numbersOnly);
        $this->storeOtp(
            destination: $destination, token: $token, purpose: $purpose,
            expiration: $this->expiresIn, hashToken: $this->hashToken
        );

        $this->sendOtpToSms($token);
    }

    public function verify(string $destination, string $purpose, string $token, array $options = []): VerifyOtpResponse
    {
        return $this->verifyOtp(
            destination: $destination,
            token: $token,
            purpose: $purpose,
            use: $options['use'] ?? true
        );
    }

    protected function sendOtpToSms(string $token) {
        dd($token);
    }
}
```
The ```verifyOtp()``` , ```generateOtp()``` and ```storeOtp()``` are already implemented in the abstract class, all you need to 
be concerned about it the ```sendOtpToSms()``` method which defines how the OTP will be sent to the user.

### Using the Manager Class directly
If you are not a fan of Facades, you can also simply call the underlying ```Horlerdipo\SimpleOtp\SSimpleOtpManager``` class directly like below
```php
Route::get('/generate-otp', function (\Illuminate\Http\Request $request, \Horlerdipo\SimpleOtp\SimpleOtpManager $otpManager) {
    $otpManager->channel('email')
        ->template('vendor.simple-otp.mails.otp')
        ->hash(false)
        ->numbersOnly()
        ->length(6)
        ->expiresIn(1)
        ->send("test@laravel.com", "login");
});

Route::get('/verify-otp', function (\Illuminate\Http\Request $request, \Horlerdipo\SimpleOtp\SimpleOtpManager $otpManager) {
    return dd($otpManager->verify("test@laravel.com", "login", $request->otp));
});
```

You can as well call the Channel classes directly if you even want to go even lower, we currently have the following channels
```\Horlerdipo\SimpleOtp\Channels\Email``` and the ```\Horlerdipo\SimpleOtp\Channels\BlackHole``` classes

## Troubleshooting & FAQ
- OTP not being delivered
Check mail config or your custom channel integration

Verify your template paths

- OTP always fails validation
Check token expiration and matching

Ensure hashing is consistent between send and verify

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Umar Oladipo](https://github.com/Horlerdipo)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
