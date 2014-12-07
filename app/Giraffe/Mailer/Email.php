<?php  namespace Giraffe\Mailer;

use Giraffe\Users\UserModel;

class Email
{

    protected $to = null;
    protected $subject = 'LonelyGiraffes Support';
    protected $template = 'emails.blank';

    /**
     * @return string
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * @return array
     */
    public function getBindings()
    {
        return [];
    }

    public static function sendTo(UserModel $user)
    {
        $i = new static;
        $i->to = $user->email;
        return $i;
    }

    public function getDestination()
    {
        return $this->to;
    }

    public function getSubject()
    {
        return $this->subject;
    }

    protected function isReadyToSend()
    {
        if (!$this->to) return false;
        return true;
    }
}