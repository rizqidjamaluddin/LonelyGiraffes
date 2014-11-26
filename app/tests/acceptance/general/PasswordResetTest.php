<?php 

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
        $this->markTestIncomplete();
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

        $this->callJson('POST', '/password/reset', ['password' => 'newpassword']);
        $this->assertResponseOk();

        $this->markTestIncomplete();
    }
} 