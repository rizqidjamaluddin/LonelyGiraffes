<?php

use Giraffe\Users\UserService;

class UserAccountTest extends TestCase
{

    public static function setUpBeforeClass()
    {
    }

    public function setUp()
    {
        parent::setUp();
        Artisan::call('migrate');
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
        $id = json_decode($response->getContent())->user->id;
        return $id;
    }

    /**
     * @test
     */
    public function it_can_find_a_user() {

        /** @var Giraffe\Users\UserService $serv */
        $serv = App::make('Giraffe\Users\UserService');
        $model = $serv->createUser([
              "email"     => "hello@something.com",
              "password"  => "anewpassword",
              "firstname" => "Sethen",
              "lastname"  => "Maleno",
              "gender"    => "M"
          ]);

        $response = $this->call("GET", "api/users/" . $model->id);
        $this->assertResponseStatus(200);
        $json = json_decode($response->getContent());
        $this->assertEquals($json->user->email, "hello@something.com");
    }
}