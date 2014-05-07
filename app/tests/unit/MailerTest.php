<?php 

class MailerTest extends TestCase
{
    public function it_can_send_a_notification_email()
    {
        $mailer = App::make('Giraffe\Helpers\Mailer\Mailer');
    }
} 