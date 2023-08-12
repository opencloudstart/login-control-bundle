<?php

namespace OCS\LoginControlBundle\Controller;

use App\Entity\User;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use LoginControl\src\Entity\UserSecurity;
use LoginControl\src\Services\LoginControlService;
use LoginControl\src\Services\Signup2FAService;
use PragmaRX\Google2FA\Exceptions\IncompatibleWithGoogleAuthenticatorException;
use PragmaRX\Google2FA\Exceptions\InvalidCharactersException;
use PragmaRX\Google2FA\Exceptions\SecretKeyTooShortException;
use PragmaRX\Google2FA\Google2FA;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class LoginControlController extends AbstractController
{
    private const TIME_CHG_PASSWD_DAYS = 90;
    private EntityManagerInterface $em;
    private Signup2FAService $signup2FA;
    private LoggerInterface $logger;
    private LoginControlService $loginControl;
    private UserPasswordHasherInterface $userPasswordHasher;

    public function __construct(
        EntityManagerInterface $em,
        LoggerInterface $logger,
        LoginControlService $loginControl,
        Signup2FAService $signup2FA,
        UserPasswordHasherInterface $userPasswordHasher
    ) {
        $this->em = $em;
        $this->signup2FA = $signup2FA;
        $this->logger = $logger;
        $this->loginControl = $loginControl;
        $this->userPasswordHasher = $userPasswordHasher;
    }


    /**
     * NOTE: OCS loginControl assumes you have already created simply username/password
     * login form with and have successfully authenticated those 2 pairs of information.
     * Username and Password
     *
     * You are ready to start two-factor authentication. Variable needed to this endpoint
     * is userId from the users table.
     *
     * @Route("/loginControl", name="ocs_login_control")
     */
    public function ocsLoginControl(Request $request): Response
    {
       if (is_object($this->getUser())) {
           $user = $this->getUser();
           $userId = $user->getId();

           //check if passwd has expired
           $security = $this->em->getRepository(UserSecurity::class)
               ->findOneBy(['userId' => $userId, 'active' => 1]);

           if ($security && is_null($security->getPasswdExpire())) {
               return $this->redirectToRoute('ocs_passwd_verify_2fas');
           }

           $passwdExpireDate = $security->getPasswdExpire();
           if ($passwdExpireDate <= new DateTime()) {
               //time to change password
               //verify code using totp
               return $this->redirectToRoute('ocs_passwd_verify_2fas');
           }

           //check if 2fas config is available
           $this->em->getRepository(UserSecurity::class)->createIfNoneExists();
           $totpConfig = $this->em->getRepository(UserSecurity::class)->count(['userId' => $userId]);

           if ($totpConfig > 0 ) {
               //check if cookie is available prevents 2fas auth every login
               if (isset($_COOKIE['ocs_login_control'])) {
                   return $this->redirectToRoute('app_main');
               }
                return $this->redirectToRoute('ocs_login_verify_2fas');
           }

           try {
               /** @var User $user */
               return new Response($this->signup2FA->new2fasUser($user));
           } catch (IncompatibleWithGoogleAuthenticatorException |
                    InvalidCharactersException |
                    SecretKeyTooShortException $e) {
               $this->logger->alert('new user 2fas', ['message' => $e->getMessage()]);
           }
       }
       return new Response('Error: User Object Not Found');
    }


    /**
     * @Route("/loginControl/verify2fas", name="ocs_login_verify_2fas")
     */
    public function oscLoginVerify(): Response
    {
        //new verify form
        return $this->render('@loginControl/login_totp_form.twig');
    }

    /**
     * @Route("/loginControl/verifyPassewd2fas", name="ocs_passwd_verify_2fas")
     */
    public function oscPasswdVerify(): Response
    {
        $this->addFlash('danger', 'Your Password has Expired!');
        //new verify form
        return $this->render('@loginControl/passwd_totp_form.twig');
    }

    /**
     * @Route("/loginControl/verifyPost", name="ocs_login_verify_post", methods={"POST"})
     */
    public function ocsLoginVerify(Request $request): Response
    {
        $userId = $this->getUser()->getId();

        $userSecurity = $this->em->getRepository(UserSecurity::class)->findOneBy(['userId' => $userId, 'active'=> 1]);
        $totpSecret = $userSecurity->getTotpKey();

        $_g2fa = new Google2FA();
        $current_otp = $_g2fa->getCurrentOtp($totpSecret);

        if ($request->get('code') === $current_otp) {
            $cookieValue = $_g2fa->generateBase32RandomKey(32);
            //set cookie so verify only needs to be done every 24 hours
            setcookie('ocs_login_control', $cookieValue, time() + 86400, '/');
            return $this->redirectToRoute('app_main');
        }
        $this->addFlash('danger', 'Your Key was not Correct.');
        return $this->redirectToRoute('ocs_login_verify_2fas');
    }

    /**
     * @Route("/loginControl/verifyPwdPost", name="ocs_passwd_verify_post", methods={"POST"})
     */
    public function ocsPasswdVerify(Request $request): Response
    {
        $userId = $this->getUser()->getId();

        $userSecurity = $this->em->getRepository(UserSecurity::class)->findOneBy(['userId' => $userId, 'active'=> 1]);
        $totpSecret = $userSecurity->getTotpKey();

        $_g2fa = new Google2FA();
        $current_otp = $_g2fa->getCurrentOtp($totpSecret);

        if ($request->get('code') === $current_otp) {
            //change password form
            return $this->redirectToRoute('ocs_change_passwd');
        }
        $this->addFlash('danger', 'Your Key was not Correct.');
        return $this->redirectToRoute('ocs_passwd_verify_2fas');
    }

    /**
     * @Route("/loginControl/changePwd", name="ocs_change_passwd")
     */
    public function ocsChangePasswd(): Response
    {
        return $this->render(
            '@loginControl/change_pass_form.twig'
        );
    }

    /**
     * @Route("/loginControl/changePwd/post", name="ocs_change_passwd_post", methods={"POST"})
     * @throws Exception
     */
    public function ocsChangePasswdPost(Request $request): Response
    {
        //check password validation
        if($request->get('password1') !== $request->get('password2')) {
            $this->addFlash('danger', 'Passwords do not Match');
            return $this->redirectToRoute('ocs_change_passwd');
        }

        $plainPassword = $request->get('password1');
        $testPasswd = $this->loginControl->resetPasswdCheck($plainPassword);
        $strengthTest = $this->loginControl->checkPasswordStrength($plainPassword);
        $checkHackerPass = $this->loginControl->checkHackerList($plainPassword);

        if ($testPasswd !== 'ok') {
            $this->addFlash('warning', 'Password does not include Upper/Lower Letters, number, special characters.');
            return $this->redirectToRoute('ocs_change_passwd');
        }

        if ($strengthTest !== 'strong') {
            $this->addFlash('warning', 'Password is not strong enough: Strength is '.ucfirst($strengthTest));
            return $this->redirectToRoute('ocs_change_passwd');
        }

        //Distance from Bad Password higher the number the better the password
        if ($checkHackerPass['DistanceToBadPassword'] <= 2) {
            $this->addFlash('warning', 'Password appears to have dictionary words in it. Try Again!');
            return $this->redirectToRoute('ocs_change_passwd');
        }

        /** @var User $user */
        $user = $this->getUser();

        //update password Expiration Date
        $chgString = '+' . self::TIME_CHG_PASSWD_DAYS .'days';
        $newExpireDate = new DateTime($chgString);
        $userSecurity = $this->em->getRepository(UserSecurity::class)->findOneBy(['userId' => $user->getId()]);
        $userSecurity->setPasswdExpire($newExpireDate);

        //strong password
        $passwordHash = $this->userPasswordHasher->hashPassword(
            $user,
            $plainPassword
        );

        //Update Password in User Table
        $user->setPassword($passwordHash);
        $this->em->persist($user);
        $this->em->flush();

        $this->addFlash('success', 'Your Password has been updated!');
        return $this->redirectToRoute('app_main');
    }

}
