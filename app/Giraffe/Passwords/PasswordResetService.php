<?php  namespace Giraffe\Passwords;

use Giraffe\Common\NotFoundModelException;
use Giraffe\Mailer\Mailer;
use Giraffe\Users\UserRepository;
use Hash;
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

    protected $enableMinuteThrottle = true;

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

        $this->resetTokenRepository->purgeExpired();
        if ($this->enableMinuteThrottle && $this->resetTokenRepository->countIssuedInLastMinuteFor($user) > 0) {
            return;
        }
        if ($this->resetTokenRepository->countIssuedFor($user) >= 5) {
            return;
        }

        $token = ResetTokenModel::issue($user);
        $this->resetTokenRepository->save($token);
        $email = PasswordResetEmail::makeFor($user, $token);
        $this->mailer->send($email);
    }

    public function disarmMinuteThrottle()
    {
        $this->enableMinuteThrottle = false;
    }

    public function enableMinuteThrottle()
    {
        $this->enableMinuteThrottle = true;
    }

    public function attemptReset($token, $newPassword)
    {
        try {
            /** @var ResetTokenModel $resetToken */
            $resetToken = $this->resetTokenRepository->getByToken($token);
        } catch (NotFoundModelException $e) {
            return;
        }
        $user = $this->userRepository->getById($resetToken->user_id);
        $user->password = Hash::make($newPassword);
        $this->userRepository->save($user);
        $this->resetTokenRepository->delete($resetToken);
    }

} 