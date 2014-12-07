<?php  namespace Giraffe\Passwords;

use Giraffe\Common\NotFoundModelException;
use Giraffe\Common\Service;
use Giraffe\Mailer\Mailer;
use Giraffe\Users\UserModel;
use Giraffe\Users\UserRepository;
use Hash;
use Str;

class PasswordResetService extends Service
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

    public function __construct(
        UserRepository $userRepository,
        ResetTokenRepository $resetTokenRepository,
        Mailer $mailer
    ) {
        parent::__construct();
        $this->userRepository = $userRepository;
        $this->resetTokenRepository = $resetTokenRepository;
        $this->mailer = $mailer;
    }

    public function requestReset($email)
    {
        $this->log->notice("Password reset requested for $email.");

        try {
            $user = $this->userRepository->getByEmail($email);
        } catch (NotFoundModelException $e) {
            $this->log->info("Invalid email for password reset ({$email})");
            return;
        }

        $this->resetTokenRepository->purgeExpired();
        if ($this->enableMinuteThrottle && $this->resetTokenRepository->countIssuedInLastMinuteFor($user) > 0) {
            $this->log->notice("One-email-per-minute throttle hit for $email.");
            return;
        }
        if ($this->resetTokenRepository->countIssuedFor($user) >= 5) {
            $this->log->notice("Five-active-reset-tokens throttle hit for $email.");
            return;
        }

        $this->issueResetToken($user);
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
        $this->log->notice('Password reset attempt initiated.');
        try {
            /** @var ResetTokenModel $resetToken */
            $resetToken = $this->resetTokenRepository->getByToken($token);
        } catch (NotFoundModelException $e) {
            $this->log->info('Invalid password reset token given.');
            return;
        }
        $this->executePasswordReset($newPassword, $resetToken);
    }

    /**
     * @param $user
     */
    protected function issueResetToken(UserModel $user)
    {
        /** @var ResetTokenModel $token */
        $token = ResetTokenModel::issue($user);
        $this->resetTokenRepository->save($token);
        $email = PasswordResetEmail::makeFor($user, $token);
        $this->mailer->send($email);
        $this->log->info(
            "Password reset for {$user->email} generated and sent.",
            ['user-hash' => $user->hash, 'expires-at' => $token->expires_at]
        );
    }

    /**
     * @param                 $newPassword
     * @param ResetTokenModel $resetToken
     */
    protected function executePasswordReset($newPassword, $resetToken)
    {
        $user = $this->userRepository->getById($resetToken->user_id);
        $this->log->info("Password reset on behalf of {$user->email} accepted.");
        $user->password = Hash::make($newPassword);
        $this->userRepository->save($user);
        $this->log->info("Invalidating reset token {$resetToken->token}.");
        $this->resetTokenRepository->delete($resetToken);
    }

} 