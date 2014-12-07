<?php
use Giraffe\Common\Controller;
use Giraffe\Passwords\PasswordResetService;

class PasswordController extends Controller
{

    public function forgot()
    {
        $email = Input::get('email');

        /** @var PasswordResetService $resetService */
        $resetService = \App::make(PasswordResetService::class);
        $resetService->requestReset($email);
        return ['message' => 'OK'];

    }

    public function reset()
    {
        $token = Input::get('token');
        $password = Input::get('password');
        /** @var PasswordResetService $resetService */
        $resetService = \App::make(PasswordResetService::class);
        $resetService->attemptReset($token, $password);
        return ['message' => 'OK'];
    }
} 