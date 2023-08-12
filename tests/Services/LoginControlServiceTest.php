<?php
declare(strict_types=1);

namespace Services;

use Exception;
use LoginControl\Services\LoginControlService;
use LoginControl\Services\PasswordChecker;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class LoginControlServiceTest extends TestCase
{
    public function __construct($name = null, array $data = [], $dataName = '')
    {
        $name = 'LoginControl Services Password Strength Testers';
        parent::__construct($name, $data, $dataName);
    }

    public function SetUp(): void
    {
        parent::setUp();
        $this->validatorInterface = $this->createMock(ValidatorInterface::class);
        $this->passwordChecker = $this->createMock( PasswordChecker::class);
        $this->controlService = new LoginControlService(
            $this->validatorInterface,
            $this->passwordChecker
        );
    }


    public function testCheckPasswordStrength(): void
    {
            $passwordsToTest = $this->passwordDataProvider();
            $count = 0;
            foreach ($passwordsToTest as $passwd) {
                $passStrength = $this->controlService->checkPasswordStrength($passwd);
                switch($passStrength){
                    case 'weak':
                        self::assertEquals('weak', $passStrength);
                        $count++;
                        break;
                    case 'medium-weak':
                        self::assertEquals('medium-weak', $passStrength);
                        $count++;
                        break;
                    case 'medium-strong':
                        self::assertEquals('medium-strong', $passStrength);
                        $count++;
                        break;
                    case 'strong':
                        self::assertEquals('strong', $passStrength);
                        $count++;
                        break;
                    default:
                        self::assertEquals('fail', $passStrength);
                        $count++;
                        break;
                }
            }
            self::assertEquals(5, $count);
    }

    public function testResetPasswdCheck(): void
    {
        $passwordsTest = $this->passwordDataProvider();
        foreach ($passwordsTest as $password) {
            $valid = $this->controlService->resetPasswdCheck($password);
            if ($valid) {
                self::assertEquals('ok', $valid);
            }
        }
    }

    /**
     * @throws Exception
     */
    public function testCheckHackerList(): void
    {
        $passwordsTest = $this->passwordDataProvider();
        foreach ($passwordsTest as $password) {
            $results = $this->controlService->checkHackerList($password);
            self::assertIsArray($results);
        }
    }

    public function passwordDataProvider(): array
    {
        return [
            "starwars",
            "arf12",
            "LKA#@%.54",
            "Mz8eXrM3Dfasd8G.",
            "PnzcjqxjiEs",
        ];
    }
}
