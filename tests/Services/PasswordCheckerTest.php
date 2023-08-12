<?php

namespace Services;

use Exception;
use LoginControl\Services\PasswordChecker;
use PHPUnit\Framework\TestCase;

class PasswordCheckerTest extends TestCase
{

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        $name = 'Password Hacker Check Test';
        parent::__construct($name, $data, $dataName);
    }

    public function SetUp(): void
    {
        parent::setUp();
        $this->hacker = new PasswordChecker();
    }

    /**
     * Notes: Tests clear text password is found on hackers list of passwords.
     * This list is generated on wiki page 10,000 top commonly used passwords
     * that hackers like to use robots to gain access to systems.
     *
     *  distance = 0 hacker password in test password
     *  distance > 0 further away from hackable password
     *
     * @throws Exception
     */
    public function testHackerCheckPassword(): void
    {
        $testPasswords = $this->passwordDataProvider();
        $countDistance = 0;
        $countPhrase = 0;
        foreach ($testPasswords as $passwordTest) {
            $result = $this->hacker->hackerCheckPassword($passwordTest);

            $distance = $result['DistanceToBadPassword'];
            if ($distance > 5) {
                $countDistance++;
                self::assertGreaterThan(5, $distance);
            }
            if ($distance === 5) {
                $countDistance++;
                self::assertEquals(5, $distance);
            }
            if ($distance < 5) {
                $countDistance++;
                self::assertLessThan(5, $distance);
            }
            //hackerPhrase
            $phraseBool = $result['HackerPhase'];
            if ($phraseBool) {
               $countPhrase++;
               self::assertTrue($phraseBool);
            } else {
                $countPhrase++;
                self::assertFalse($phraseBool);
            }
        }

        self::assertEquals(4, $countPhrase);
        self::assertEquals(4, $countDistance);
    }

     public function passwordDataProvider(): array
     {
         return [
             "starwars",
             "arf12",
             "LKA#@%.54",
             "Mz8eXrM3Dfasd8G.",
         ];
     }
}
