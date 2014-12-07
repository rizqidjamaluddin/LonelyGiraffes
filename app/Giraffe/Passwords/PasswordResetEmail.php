<?php  namespace Giraffe\Passwords; 
use Giraffe\Mailer\Email;
use Giraffe\Users\UserModel;

class PasswordResetEmail extends Email
{
    protected $subject = 'LonelyGiraffes Password Reset';
    protected $template = 'emails.blank';

    /**
     * @var ResetTokenModel
     */
    protected $token;

    public static function makeFor(UserModel $user, ResetTokenModel $token)
    {
        $i = new static;
        $i->to = $user->email;
        $i->token = $token;
        return $i;
    }

    public function getBindings()
    {
        return ['msg' => 'http://lonelygiraffes.com/reset/' . $this->token];
    }


} 