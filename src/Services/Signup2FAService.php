<?php

namespace LoginControl\src\Services;

use App\Entity\User;
use BaconQrCode\Renderer\Image\Png;
use BaconQrCode\Writer;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use LoginControl\src\Entity\UserSecurity;
use PragmaRX\Google2FA\Exceptions\IncompatibleWithGoogleAuthenticatorException;
use PragmaRX\Google2FA\Exceptions\InvalidCharactersException;
use PragmaRX\Google2FA\Exceptions\SecretKeyTooShortException;
use PragmaRX\Google2FA\Google2FA;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;


class Signup2FAService
{
    private Environment $twig;
    private EntityManagerInterface $em;

    public function __construct(
        Environment            $twig,
        EntityManagerInterface $em
    ) {
        $this->twig = $twig;
        $this->em = $em;
    }

    /**
     * @param User $user
     * @return string|null
     * @throws IncompatibleWithGoogleAuthenticatorException
     * @throws InvalidCharactersException
     * @throws SecretKeyTooShortException
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function new2fasUser(User $user): ?string
    {
        $userId = $user->getId();
        $userEmail = $user->getEmail();

        $_g2fa = new Google2FA();
        $newPassword = $_g2fa->generateSecretKey();

        $app_name = 'RTS';

        $securityUser = new UserSecurity();
        $securityUser->setUserId($userId)
            ->setActive(1)
            ->setCreateDate(new DateTime())
            ->setPasswdExpire(new DateTime())
            ->setTotpKey($newPassword);
        $this->em->getRepository(UserSecurity::class)->createIfNoneExists();
        $this->em->persist($securityUser);
        $this->em->flush();

        $qrCodeUrl = $_g2fa->getQRCodeUrl(
            $app_name,
            $userEmail,
            $securityUser->getTotpKey()
        );

        $renderer = new Png();
        $renderer->setHeight(256);
        $renderer->setWidth(256);
        $writer = new Writer($renderer);
        $encoded_qr_data = base64_encode($writer->writeString($qrCodeUrl));
        $current_otp = $_g2fa->getCurrentOtp($securityUser->getTotpKey());

        return $this->twig->render(
            '@loginControl/signup_form.twig',
            [
                'encoded_qr_data' => $encoded_qr_data,
                'current_otp' => $current_otp,
                'signupCode' => $securityUser->getTotpKey(),
                'appName' => $app_name,
                'formAction' => '/loginControl/verify2fas',
            ]
        );
    }

}
