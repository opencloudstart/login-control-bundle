<?php

namespace LoginControl\src\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class UserPasswordDTO
{
    /**
     * @var string
     * @Assert\NotCompromisedPassword()
     * @Assert\NotBlank(message="Password must meet minimum standards")
     */
    protected string $rawPassword;

    /**
     * @return string
     */
    public function getRawPassword(): string
    {
        return $this->rawPassword;
    }

    /**
     * @param string $rawPassword
     * @return UserPasswordDTO
     */
    public function setRawPassword(string $rawPassword): UserPasswordDTO
    {
        $this->rawPassword = $rawPassword;
        return $this;
    }

}
