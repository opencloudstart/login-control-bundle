<?php

namespace LoginControl\src\Services;

use Exception;
use LoginControl\src\DTO\UserPasswordDTO;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class LoginControlService
{
    private ValidatorInterface $validator;
    private PasswordChecker $passwordChecker;

    public function __construct(
        ValidatorInterface $validator,
        PasswordChecker $passwordChecker
    ) {
        $this->validator = $validator;
        $this->passwordChecker = $passwordChecker;
    }

    /**
     * Note: Check characters min 8 and alphanumeric and special chars
     * @param string $passwd
     * @return string|null
     */
    public function resetPasswdCheck(string $passwd): ?string
    {
        $rawPasswdCheck = new UserPasswordDTO();
        $rawPasswdCheck->setRawPassword($passwd);
        try {
            $errors = $this->validator->validate($rawPasswdCheck);
            if (count($errors) > 0 ) {
                $finalMessage = '';
                foreach ($errors as $error) {
                    $finalMessage .= $error->getMessage();
                }
                return $finalMessage;
            }

            return 'ok';

        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    public function checkPasswordStrength(string $passwd): string
    {
        $indicator = '';
        //indicates it is at least 8 chars long
        if (preg_match('/^.{8,}$/', $passwd)) {
            $indicator = 'weak';
        }
        //indicates it is at least has 1 upper character
        if (preg_match('/^(?=.*[A-Z]).{8,}$/', $passwd)) {
            $indicator = 'medium-weak';
        }
        //indicates it is at least has 1 digital
        if (preg_match('/^(?=.*\d)(?=.*[A-Z]).{8,}$/', $passwd)) {
            $indicator = 'medium-strong';
        }
        //indicates strong passwd which is in compliance for SOC2/HIPAA
        if (preg_match('/^(?=.*[!@#$%^&*-])(?=.*\d)(?=.*[A-Z]).{8,}$/', $passwd)) {
            $indicator = 'strong';
        }

        return $indicator;
    }

    /**
     * @throws Exception
     */
    public function checkHackerList(string $passwd): array
    {
        return $this->passwordChecker->hackerCheckPassword($passwd);
    }

}
