<?php

namespace Services;

use LoginControl\Services\LoginKeyHashService;
use PHPUnit\Framework\TestCase;

class LoginKeyHashServiceTest extends TestCase
{
    private const USER_ID = 142;

    public function testLoginCreateHash(): void
    {
        $hasher = new LoginKeyHashService();
        $hashedUserId = $hasher::loginCreateHash(self::USER_ID);
        self::assertNotEmpty($hashedUserId);
    }

    public function testLoginCheckHash(): void
    {
        $hasher = new LoginKeyHashService();
        $hashedUserId = $hasher::loginCreateHash(self::USER_ID);
        $userId = (string)self::USER_ID;
        self::assertTrue($hasher::loginCheckHash($userId, $hashedUserId));
    }
}
