<?php
declare(strict_types=1);

namespace LoginControl\src\DTO;

class LoginControlTOTPTypes
{
    public const TOTP_KEY_TYPE_APP = 1;
    public const TOTP_KEY_TYPE_EMAIL = 2;
    public const TOTP_KEY_TYPE_SMS = 3;

    public const OTP_DURATION = 15;
}
