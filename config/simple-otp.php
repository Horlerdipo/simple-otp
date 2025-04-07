<?php

// config for Horlerdipo/SimpleOtp

use Horlerdipo\SimpleOtp\Enums\ChannelType;

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
    ]
    // TODO: implement config for otps that can only be tried a number of times
];
