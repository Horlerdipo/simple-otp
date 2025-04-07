<?php

namespace Horlerdipo\SimpleOtp\Enums;

enum ChannelType: string
{
    case EMAIL = 'email';
    case BLACKHOLE = 'blackhole';
    //    case SMS = 'sms';
}
