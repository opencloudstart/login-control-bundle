<?php
declare(strict_types=1);

namespace LoginControl\Services;

class LoginKeyHashService
{
    public static function loginCreateHash(int $userId): string
    {
        $keyFile = self::getCryptKeyContent();
        return crypt((string)$userId, $keyFile);
    }

    public static function loginCheckHash(string $userId, string $loginKey): bool
    {
        $key = self::getCryptKeyContent();
        $expected = crypt($userId, $key);
        return hash_equals($expected, $loginKey);
    }

    private static function getCryptKeyContent(): string
    {
       return '$2a$07$wiGq2maozpBkI8dLA3xUW1qdM$';
    }
}
