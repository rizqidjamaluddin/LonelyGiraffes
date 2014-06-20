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
        $response = $this->call('GET', '/api/users/404');
        $this->assertResponseStatus(404);
    }

    /**
     * @test
     */
    public function it_can_create_a_new_user()
    {
        $response = $this->call("POST", "/api/users/", $this->genericUser);
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
        $response = $this->call("POST", "/api/users/", [
                'email'     => '@lonelygiraffes.x',
                'password'  => 'password',
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
        $model = $this->call("POST", "/api/users/", $this->genericUser);
        $modelResponse = json_decode($model->getContent());

        $response = $this->call("GET", "/api/users/" . $modelResponse->user->hash);
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
        $model = $this->call("POST", "/api/users/", $this->genericUser);
        $modelResponse = json_decode($model->getContent());
        $this->asUser($modelResponse->user->hash);

        $response = $this->call("PUT", "/api/users/" . $modelResponse->user->hash,
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
        $model = $this->createGenericUser();
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
     */
    public function a_user_updating_information_must_conform_to_validation()
    {
        $model = $this->call("POST", "/api/users/", $this->genericUser);
        $modelResponse = json_decode($model->getContent());
        $this->asUser($modelResponse->user->hash);

        $response = $this->call(
            "PUT",
            "/api/users/" . $modelResponse->user->hash,
            [
                'email' => 'lonelygiraffes.com'
            ]
        );

        $this->assertResponseStatus(422);

        $check = $this->repository->get($modelResponse->user->hash);
        $this->assertEquals($check->email, 'hello@lonelygiraffes.com');
    }

    /**
     * @test
     */
    public function a_user_cannot_change_another_users_data()
    {
        $model = $this->createGenericUser();
        $otherUser = $this->service->createUser(
            [
                'email'     => 'other@lonelygiraffes.com',
                'password'  => 'password',
                'firstname' => 'Lonely',
                'lastname'  => 'Giraffe',
                'gender'    => 'M'
            ]
        );

        $response = $this->call(
            "PUT",
            "api/users/" . $otherUser->hash,
            [
                'email' => 'evil@example.com'
            ]
        );

        $this->assertResponseStatus(403);

        $check = $this->repository->get($otherUser->hash);
        $this->assertEquals($check->email, 'other@lonelygiraffes.com');
    }

    /**
     * @test
     */
    public function an_administrator_can_change_a_users_data()
    {
        $model = $this->call('POST', '/api/users/', $this->genericUser);
        $modelResponse = json_decode($model->getContent());
        $this->asUser($modelResponse->user->hash);

        $admin = $this->call('POST', '/api/users/', $this->administrator);
        $adminResponse = json_decode($model->getContent());
        $this->asUser($adminResponse->user->hash);

        $response = $this->call(
            'PUT',
            '/api/users/' . $modelResponse->user->hash,
            [
                'email' => 'new@lonelygiraffes.com'
            ]
        );

        $this->assertResponseStatus(200);
    }

    /**
     * @test
     */
    public function a_user_cannot_change_their_email_to_another_users_email()
    {
        $model = $this->call('POST', '/api/users/', $this->genericUser);
        $modelResponse = json_decode($model->getContent());
        $this->asUser($modelResponse->user->hash);

        $anotherModel = $this->call('POST', '/api/users/', $this->anotherGenericUser);
        $anotherModelResponse = json_decode($model->getContent());
        $this->asUser($anotherModelResponse->user->hash);        

        $response = $this->call(
            "PUT",
            "/api/users/" . $modelResponse->user->hash,
            [
                'email' => 'anotherHello@lonelygiraffes.com'
            ]
        );

        $this->assertResponseStatus(422);

        // make sure everything is intact
        $check = $this->repository->get($modelResponse->user->hash);
        $this->assertEquals($check->email, 'hello@lonelygiraffes.com');
        $otherCheck = $this->repository->get($anotherModelResponse->user->hash);
        $this->assertEquals($otherCheck->email, 'anotherHello@lonelygiraffes.com');
    }

    /**
     * @test
     * @depends it_can_create_a_new_user
     */
    public function an_administrator_account_can_delete_a_user()
    {
        $admin = $this->createAdministratorAccount();
        $this->gatekeeper->iAm($admin);
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

    /**
     * @return UserModel
     */
    protected function createGenericUser()
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
        $this->asUser($model);
        return $model;
    }
}