<?php

use Giraffe\Authorization\Gatekeeper;
use Giraffe\Users\UserModel;
use Giraffe\Users\UserRepository;
use Giraffe\Users\UserService;
use Json\Validator as JsonValidator;

class UserAccountCase extends AcceptanceCase
{
    /**
     * @var UserService
     */
    protected $service;

    /**
     * @var UserRepository
     */
    protected $repository;

    public function setUp()
    {
        parent::setUp();
        $this->service = App::make('Giraffe\Users\UserService');
        $this->repository = App::make('Giraffe\Users\UserRepository');
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
    public function it_can_create_a_new_user()
    {
        $response = $this->call(
            "POST",
            "api/users/",
            [
                "email"     => 'hello@lonelygiraffes.com',
                "password"  => 'password',
                'firstname' => 'Lonely',
                'lastname'  => 'Giraffe',
                'gender'    => 'M'
            ]
        );
        $responseContent = json_decode($response->getContent());
        $this->assertResponseStatus(200);

        $validator = new JsonValidator(app_path() . '/schemas/UserSchema.json');
        $validator->validate($responseContent);

        $this->assertEquals('hello@lonelygiraffes.com', $responseContent->user->email);
        $this->assertEquals('Lonely', $responseContent->user->firstname);
        $this->assertEquals('Giraffe', $responseContent->user->lastname);
        $this->assertEquals('M', $responseContent->user->gender);
    }

    /**
     * @test
     */
    public function it_fails_to_create_a_user_with_a_bad_email()
    {
        $response = $this->call(
            "POST",
            "api/users/",
            [
                "email"     => 'lonelygiraffes.com',
                "password"  => 'password',
                'firstname' => 'Lonely',
                'lastname'  => 'Giraffe',
                'gender'    => 'M'
            ]
        );
        $this->assertResponseStatus(422);
    }

    /**
     * @test
     * @depends it_can_create_a_new_user
     */
    public function it_can_find_a_user()
    {
        $service = App::make('Giraffe\Users\UserService');
        $model = $service->createUser(
            [
                "email"     => 'hello@lonelygiraffes.com',
                "password"  => 'password',
                'firstname' => 'Lonely',
                'lastname'  => 'Giraffe',
                'gender'    => 'M'
            ]
        );
        $response = $this->call("GET", "api/users/" . $model->id);
        $responseContent = json_decode($response->getContent());
        $validator = new JsonValidator(app_path() . '/schemas/UserSchema.json');
        $validator->validate($responseContent);

        $this->assertResponseStatus(200);
        $this->assertEquals('hello@lonelygiraffes.com', $responseContent->user->email);
        $this->assertEquals('Lonely', $responseContent->user->firstname);
        $this->assertEquals('Giraffe', $responseContent->user->lastname);
        $this->assertEquals('M', $responseContent->user->gender);
    }

    /**
     * @test
     * @depends it_can_create_a_new_user
     */
    public function a_user_can_update_information()
    {
        $model = $this->service->createUser(
            [
                'email'     => 'hello@lonelygiraffes.com',
                'password'  => 'password',
                'firstname' => 'Lonely',
                'lastname'  => 'Giraffe',
                'gender'    => 'M'
            ]
        );
        $response = $this->call(
            "PUT",
            "api/users/1",
            [
                'email'     => 'hello@notlonelygiraffes.com',
                'password'  => 'anotherpassword',
                'firstname' => 'Lonesome',
                'lastname'  => 'Penguin',
                'gender'    => 'F'
            ]
        );
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStatus(200);
        $this->assertEquals('hello@notlonelygiraffes.com', $responseContent->user->email);
        $this->assertEquals('Lonesome', $responseContent->user->firstname);
        $this->assertEquals('Penguin', $responseContent->user->lastname);
        $this->assertEquals('F', $responseContent->user->gender);
    }

    /**
     * @test
     */
    public function a_user_cannot_change_their_user_hash_or_id()
    {
        $model = $this->service->createUser(
            [
                'email'     => 'hello@lonelygiraffes.com',
                'password'  => 'password',
                'firstname' => 'Lonely',
                'lastname'  => 'Giraffe',
                'gender'    => 'M'
            ]
        );
        $originalHash = $model->hash;
        $originalId = $model->id;
        $response = $this->call(
            "PUT",
            "api/users/" . $model->hash,
            [
                'id'     => 1000,
                'hash'   => Str::random(32),
                'gender' => 'F'
            ]
        );

        // the system should simply ignore the new data, but not fail
        $this->assertResponseStatus(200);

        $check = $this->repository->get($model->id);
        $this->assertEquals($originalHash, $check->hash);
        $this->assertEquals($originalId, $check->id);
        $this->assertEquals('F', $check->gender);
    }

    /**
     * @test
     * @depends it_can_create_a_new_user
     */
    public function an_administrator_account_can_delete_a_user()
    {
        $admin = $this->createAdministratorAccount();
        $this->gatekeeper->iAm($admin);;
        $model = $this->service->createUser(
            [
                "email"     => 'hello@lonelygiraffes.com',
                "password"  => 'password',
                'firstname' => 'Lonely',
                'lastname'  => 'Giraffe',
                'gender'    => 'M'
            ]
        );

        $response = $this->call("DELETE", "api/users/" . $model->id);
        $this->assertResponseStatus(200);

        $this->setExpectedException('Giraffe\Common\NotFoundModelException');
        $this->repository->get($model->hash);
    }

    /**
     * @test
     * @depends it_can_create_a_new_user
     */
    public function a_user_cannot_delete_their_own_account()
    {
        $model = $this->service->createUser(
            [
                "email"     => 'hello@lonelygiraffes.com',
                "password"  => 'password',
                'firstname' => 'Lonely',
                'lastname'  => 'Giraffe',
                'gender'    => 'M'
            ]
        );
        $test = $this->repository->get($model->hash);
        $this->gatekeeper->iAm($model);

        $response = $this->call("DELETE", "api/users/" . $model->hash);
        $this->assertResponseStatus(403);

        $fetch = $this->repository->get($model->hash);
        $this->assertEquals($fetch->hash, $model->hash);
    }
}