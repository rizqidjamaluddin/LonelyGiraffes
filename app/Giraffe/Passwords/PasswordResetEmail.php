<?php  namespace Giraffe\Passwords; 
use Giraffe\Mailer\Email;
use Giraffe\Users\UserModel;

class PasswordResetEmail extends Email
{
    protected $subject = 'LonelyGiraffes Password Reset';
    protected $template = 'emails.reset';

    /**
     * @var ResetTokenModel
     */
    protected $token;

    /**
     * @return ResetTokenModel
     */
    public function getToken()
    {
        return $this->token;
    }

    public static function makeFor(UserModel $user, ResetTokenModel $token)
    {
        $i = new static;
        $i->to = $user->email;
        $i->token = $token;
        return $i;
    }

    public function getBindings()
    {
        return ['resetUrl' => 'http://v2.develop.lonelygiraffes.com/#/password/reset/'.$this->token->token];
    }


} 