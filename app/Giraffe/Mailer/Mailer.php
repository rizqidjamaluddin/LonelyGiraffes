<?php  namespace Giraffe\Mailer;

use Giraffe\Users\UserModel;
use Illuminate\Mail\Mailer as IlluminateMailer;

class Mailer
{
    /**
     * @var null|Email
     */
    protected $lastSentEmail = null;

    /**
     * @var \Illuminate\Mail\Mailer
     */
    private $mailer;

    public function __construct(IlluminateMailer $illuminateMailer)
    {
        $this->mailer = $illuminateMailer;
    }

    /**
     * @return Email|null
     */
    public function getLastSentMail()
    {
        return $this->lastSentEmail;
    }

    public function send(Email $email)
    {
        $this->mailer->send($email->getTemplate(), $email->getBindings(), function ($mail) use ($email) {
            $mail->to($email->getDestination())->from("support@lonelygiraffes.com", "LonelyGiraffes Support");
        });
        $this->lastSentEmail = $email;
    }
} 