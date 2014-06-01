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

    /**
     * @test
     */
    public function it_can_insert_a_new_user() {
        $response = $this->call("POST", "api/users/", [
            "email"     => "hello@something.com",
            "password"  => "anewpassword",
            "firstname" => "Sethen",
            "lastname"  => "Maleno",
            "gender"    => "M"
        ]);
        $this->assertResponseStatus(200);
        return json_decode($response->getContent())->user->id;
    }

    /**
     * @depends it_can_insert_a_new_user
     */
    public function it_can_find_a_user($id) {
        $response = $this->call("GET", "api/users/" . $id);
        $this->assertResponseStatus(200);
        echo $response;
    }
}