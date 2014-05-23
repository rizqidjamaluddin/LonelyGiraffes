<?php  namespace Giraffe\Mailer;

use Illuminate\Mail\Mailer as IlluminateMailer;

class Mailer
{
    /**
     * @var \Illuminate\Mail\Mailer
     */
    private $mailer;

    public function __construct(IlluminateMailer $illuminateMailer)
    {
        $this->mailer = $illuminateMailer;
    }
} 