<?php

// config for Horlerdipo/SimpleOtp
return [
    'length' => env('OTP_LENGTH', 6),

    'default_channel' => ChannelType::EMAIL->value,

    'expires_in' => env('OTP_EXPIRATION_TIME', 10),

    'hash' => false,

    'email_template_location' => 'mails.otp',

    'numbers_only' => true,

    //TODO: implement config for otps that can only be tried a number of times
];
