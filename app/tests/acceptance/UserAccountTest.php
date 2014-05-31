<?php

class UserAccountTest extends TestCase
{

    public function setUp()
    {
        parent::setUp();
        Artisan::call('migrate');
        Artisan::call('db:seed');
    }

    /**
     * @test
     */
    public function it_can_fail_to_find_a_user()
    {
        $response = $this->call('GET', 'api/users/404');
        $this->assertResponseStatus(404);
    }

}