<?php
use Giraffe\Common\Controller;

class PasswordController extends Controller
{
    public function forgot()
    {
        $email = Input::get('email');
        $this->log->notice("Password reset requested for $email.");
    }

    public function reset()
    {

    }
} 