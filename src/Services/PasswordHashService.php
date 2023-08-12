<?php
declare(strict_types=1);

namespace LoginControl\Services;

use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;

class PasswordHashService implements PasswordEncoderInterface
{
    /**
     * @var string
     */
    private $cryptKeyPath;
    /**
     * @var false|string
     */
    private $key;

    public function __construct()
    {
        $this->cryptKeyPath = getenv("CRYPT_FILE");
    }

    protected function getKey(): string
    {
        if ($this->key === null) {
            $this->key = file_get_contents($this->cryptKeyPath);
        }
        return $this->key;
    }

    /**
     * note: Uses crypt_key as salt and newer encryption protocols
     * Password_Argon2I which is native to php encryption.
     *
     */
    public function encodePassword($raw, $salt): string
    {
        $key = $this->getKey();
        $options = [
            'salt' => $key,
        ];
        return password_hash($raw, PASSWORD_ARGON2I, $options);
    }

    public function isPasswordValid($encoded, $raw, $salt): bool
    {
        $encPassword = $this->encodePassword($raw, 0);

        return ($encoded === $encPassword);
    }
}
