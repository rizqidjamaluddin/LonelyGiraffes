<?php  namespace Giraffe\Passwords;

use Giraffe\Common\NotFoundModelException;
use Giraffe\Mailer\Mailer;
use Giraffe\Users\UserRepository;
use Str;

class PasswordResetService
{

    /**
     * @var UserRepository
     */
    private $userRepository;
    /**
     * @var ResetTokenRepository
     */
    private $resetTokenRepository;
    /**
     * @var Mailer
     */
    private $mailer;

    public function __construct(UserRepository $userRepository, ResetTokenRepository $resetTokenRepository, Mailer $mailer)
    {
        $this->userRepository = $userRepository;
        $this->resetTokenRepository = $resetTokenRepository;
        $this->mailer = $mailer;
    }

    public function requestReset($email)
    {
        try {
            $user = $this->userRepository->getByEmail($email);
        } catch (NotFoundModelException $e) {
            return;
        }
        $token = ResetTokenModel::issue($user);
        $this->resetTokenRepository->save($token);

        $email = PasswordResetEmail::makeFor($user, $token);
        $this->mailer->send($email);
    }

    public function attemptReset($token, $newPassword)
    {

    }

} 