<?php

namespace App\Lib\Otp\Enums;

enum ChannelType: string
{
    case EMAIL = 'email';
    case SMS = 'sms';
}
