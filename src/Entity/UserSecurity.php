<?php

namespace LoginControl\src\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use LoginControl\src\Services\UserSecurityRepository;

/**
 * @ORM\Entity(repositoryClass=UserSecurityRepository::class)
 * @ORM\Table(name="ocs_user_security")
 */
class UserSecurity
{
    /**
     * @var integer
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @var integer
     * @ORM\Column(type="integer", name="user_id", nullable=false)
     */
    private int $userId;

    /**
     * @var string|null
     * @ORM\Column(type="string", name="totp_key", nullable=true)
     */
    private ?string $totpKey;

    /**
     * @var DateTime|null
     * @ORM\Column(type="datetime", name="create_date", nullable=true)
     */
    private ?DateTime $createDate;

    /**
     * @var DateTime|null
     * @ORM\Column(type="datetime", name="passwd_expire", nullable=true)
     */
    private ?DateTime $passwdExpire;

    /**
     * @var int
     * @ORM\Column(type="integer", name="active", nullable=false, options={"default": 1})
     */
    private int $active;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getUserId(): int
    {
        return $this->userId;
    }

    /**
     * @param int $userId
     * @return UserSecurity
     */
    public function setUserId(int $userId): UserSecurity
    {
        $this->userId = $userId;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getTotpKey(): ?string
    {
        return $this->totpKey;
    }

    /**
     * @param string|null $totpKey
     * @return UserSecurity
     */
    public function setTotpKey(?string $totpKey): UserSecurity
    {
        $this->totpKey = $totpKey;
        return $this;
    }

    /**
     * @return DateTime|null
     */
    public function getCreateDate(): ?DateTime
    {
        return $this->createDate;
    }

    /**
     * @param DateTime|null $createDate
     * @return UserSecurity
     */
    public function setCreateDate(?DateTime $createDate): UserSecurity
    {
        $this->createDate = $createDate;
        return $this;
    }

    /**
     * @return DateTime|null
     */
    public function getPasswdExpire(): ?DateTime
    {
        return $this->passwdExpire;
    }

    /**
     * @param DateTime $passwdExpire
     * @return UserSecurity
     */
    public function setPasswdExpire(DateTime $passwdExpire): UserSecurity
    {
        $this->passwdExpire = $passwdExpire;
        return $this;
    }

    /**
     * @return int
     */
    public function getActive(): int
    {
        return $this->active;
    }

    /**
     * @param int $active
     * @return UserSecurity
     */
    public function setActive(int $active): UserSecurity
    {
        $this->active = $active;
        return $this;
    }
}
