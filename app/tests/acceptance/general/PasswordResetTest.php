<?php

use Giraffe\Mailer\Mailer;
use Giraffe\Passwords\PasswordResetEmail;
use Giraffe\Passwords\PasswordResetService;

class PasswordResetTest extends AcceptanceCase
{

    /**
     * @test
     */
    public function users_can_request_a_password_reset()
    {
        $mario = $this->registerMario();
        $this->callJson('POST', '/password/forgot', ['email' => $this->mario['email']]);
        $this->assertResponseOk();

        /** @var Mailer $mailer */
        $mailer = App::make(Giraffe\Mailer\Mailer::class);
        $latest = $mailer->getLastSentMail();
        $this->assertFalse($latest === null);
        /** Email $latest */
        $this->assertTrue($latest instanceof PasswordResetEmail);
        $this->assertEquals($this->mario['email'], $latest->getDestination());
        $this->assertEquals("LonelyGiraffes Password Reset", $latest->getSubject());
    }

    /**
     * @test
     */
    public function invalid_emails_for_password_resets_are_invisible_to_users()
    {
        $this->callJson('POST', '/password/forgot', ['email' => 'foo']);
        $this->assertResponseOk();
        $this->markTestIncomplete();
    }

    /**
     * @test
     */
    public function users_can_change_their_password_with_a_reset_token()
    {
        $mario = $this->registerMario();
        $this->callJson('POST', '/password/forgot', ['email' => $this->mario['email']]);
        $this->assertResponseOk();

        $this->callJson('POST', '/password/reset', ['password' => 'newpassword', 'token']);
        $this->assertResponseOk();

        $this->markTestIncomplete();
    }
} 