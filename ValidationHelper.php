<?php

namespace App\Helpers;

class ValidationHelper
{
    public static function isPinValid(string $pin): bool
    {
        return strlen($pin) === 6 && is_numeric($pin);
    }
}