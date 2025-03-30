<?php

namespace Horlerdipo\SimpleOtp\Enums;

enum ChannelType: string
{
    case EMAIL = 'email';
    case SMS = 'sms';
}
