<?php 

class MailerTest extends TestCase
{
    /**
     * @test
     */
    public function it_can_send_a_notification_email()
    {
        $mailer = App::make('Giraffe\Mailer\Mailer');
    }
}