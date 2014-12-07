<?php

use Carbon\Carbon;
use Giraffe\Mailer\Mailer;
use Giraffe\Passwords\PasswordResetEmail;
use Giraffe\Passwords\PasswordResetService;
use Giraffe\Users\UserRepository;

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
    }

    /**
     * @test
     */
    public function reset_tokens_are_only_valid_for_24_hours()
    {
        $mario = $this->registerMario();
        $this->callJson('POST', '/password/forgot', ['email' => $this->mario['email']]);
        $this->assertResponseOk();

        /** @var Mailer $mailer */
        $mailer = App::make(Giraffe\Mailer\Mailer::class);
        $latest = $mailer->getLastSentMail();
        /** @var PasswordResetEmail $latest */
        $this->assertEquals(Carbon::now()->addHours(24), $latest->getToken()->expires_at);
    }

    /**
     * @test
     */
    public function users_can_only_receive_one_reset_per_minute()
    {
        $mario = $this->registerMario();
        $this->callJson('POST', '/password/forgot', ['email' => $this->mario['email']]);
        $this->assertResponseOk();

        /** @var Mailer $mailer */
        $mailer = App::make(Giraffe\Mailer\Mailer::class);
        $latest = $mailer->getLastSentMail();
        /** @var PasswordResetEmail $latest */
        $token = $latest->getToken()->token;

        $this->callJson('POST', '/password/forgot', ['email' => $this->mario['email']]);
        $this->assertResponseOk();

        // assert it's the same token
        $latest = $mailer->getLastSentMail();
        /** @var PasswordResetEmail $latest */
        $this->assertEquals($token, $latest->getToken()->token);
    }

    /**
     * @test
     */
    public function users_can_only_have_five_tokens_at_a_time()
    {
        /** @var PasswordResetService $service */
        $service = App::make(PasswordResetService::class);
        $service->disarmMinuteThrottle();
        /** @var Mailer $mailer */
        $mailer = App::make(Giraffe\Mailer\Mailer::class);

        $tokens = [];

        $mario = $this->registerMario();
        for ($i = 0; $i < 9; $i++) {
            $this->callJson('POST', '/password/forgot', ['email' => $this->mario['email']]);
            $latest = $mailer->getLastSentMail();
            /** @var PasswordResetEmail $latest */
            $tokens[] = $latest->getToken()->token;
        }

        $this->assertEquals(5, count(array_unique($tokens)));
    }

    /**
     * @test
     */
    public function users_can_change_their_password_with_a_reset_token()
    {
        /** @var Mailer $mailer */
        $mailer = App::make(Giraffe\Mailer\Mailer::class);

        $mario = $this->registerMario();
        $this->callJson('POST', '/password/forgot', ['email' => $this->mario['email']]);
        $this->assertResponseOk();

        $userRepository = App::make(UserRepository::class);
        /** @var UserRepository $userRepository */
        $user = $userRepository->getByEmail($this->mario['email']);

        $this->assertTrue(Hash::check('password', $user->getAuthPassword()));

        $latest = $mailer->getLastSentMail();
        /** @var PasswordResetEmail $latest */
        $token = $latest->getToken()->token;

        $this->callJson('POST', '/password/reset', ['password' => 'newpassword', 'token' => $token]);
        $this->assertResponseOk();

        $userRepository = App::make(UserRepository::class);
        /** @var UserRepository $userRepository */
        $user = $userRepository->getByEmail($this->mario['email']);

        $this->assertTrue(Hash::check('newpassword', $user->getAuthPassword()));

        // now verify that this token only works once

        $this->callJson('POST', '/password/reset', ['password' => 'newer-password', 'token' => $token]);
        $this->assertResponseOk();

        $userRepository = App::make(UserRepository::class);
        /** @var UserRepository $userRepository */
        $user = $userRepository->getByEmail($this->mario['email']);

        $this->assertTrue(Hash::check('newpassword', $user->getAuthPassword()));
    }

    /**
     * @test
     */
    public function invalid_tokens_fail_silently()
    {
        $this->callJson('POST', '/password/reset', ['password' => 'newpassword', 'token' => '1234']);
        $this->assertResponseOk();
    }
} 