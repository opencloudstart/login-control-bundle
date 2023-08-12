<?php
declare(strict_types=1);

namespace LoginControl\src\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Id;
use LoginControl\Services\GetIPServices;

/**
 * @ORM\Entity(repositoryClass="LoginControl\Repository\LoginAttemptRepository")
 * @ORM\Table(name="cobra.login_attempts")
 */
class LoginAttemptEntity
{
    /**
     * @var int
     * @Id
     * @ORM\Column(type="integer", name="id", nullable=false)
     * @ORM\GeneratedValue()
     */
    private $id;

    /**
     * @var string|null
     * @ORM\Column(type="string", name="ip_address", nullable=true)
     */
    private $ipAddress;

    /**
     * @var DateTimeImmutable
     * @ORM\Column(type="datetime_immutable", name="attempt_date", nullable=false)
     */
    private $attemptDate;

    /**
     * @var string|null
     * @ORM\Column(type="string", name="user_name", nullable=true)
     */
    private $userName;

    public function __construct(?string $userName)
    {
        $this->ipAddress = GetIPServices::getIP();
        $this->userName = $userName;
        $this->attemptDate = new DateTimeImmutable('now');
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string|null
     */
    public function getIpAddress(): ?string
    {
        return $this->ipAddress;
    }

    /**
     * @return DateTimeImmutable
     */
    public function getAttemptDate(): DateTimeImmutable
    {
        return $this->attemptDate;
    }

    /**
     * @return string|null
     */
    public function getUserName(): ?string
    {
        return $this->userName;
    }
}

