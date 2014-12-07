<?php
use Giraffe\Common\Controller;
use Giraffe\Passwords\PasswordResetService;

class PasswordController extends Controller
{

    public function forgot()
    {
        $email = Input::get('email');
        $this->log->notice("Password reset requested for $email.");

        /** @var PasswordResetService $resetService */
        $resetService = \App::make(PasswordResetService::class);

        $resetService->requestReset($email);

    }

    public function reset()
    {

    }
} 