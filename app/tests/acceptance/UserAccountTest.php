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
        $response = $this->toJson($this->call("POST", "/api/users/", $this->genericUser));
        $this->assertResponseStatus(200);

        $validator = new JsonValidator(app_path() . '/schemas/UserSchema.json');
        //$validator->validate($response->user);

        $this->assertEquals('hello@lonelygiraffes.com', $response->user->email);
        $this->assertEquals('Lonely', $response->user->firstname);
        $this->assertEquals('Giraffe', $response->user->lastname);
        $this->assertEquals('M', $response->user->gender);
    }

    /**
     * @test
     * @depends it_can_create_a_new_user
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
        $model = $this->toJson($this->call("POST", "/api/users/", $this->genericUser));
        $getModel = $this->toJson($this->call("GET", "/api/users/" . $model->user->hash));
        
        //$validator = new JsonValidator(app_path() . '/schemas/UserSchema.json');
        //$validator->validate($responseContent);

        $this->assertResponseStatus(200);
        $this->assertEquals('hello@lonelygiraffes.com', $getModel->user->email);
        $this->assertEquals('Lonely', $getModel->user->firstname);
        $this->assertEquals('Giraffe', $getModel->user->lastname);
        $this->assertEquals('M', $getModel->user->gender);
    }

    /**
     * @test
     * @depends it_can_create_a_new_user
     */
    public function a_user_can_update_information()
    {
        $model = $this->toJson($this->call("POST", "/api/users/", $this->genericUser));
        $this->asUser($model->user->hash);

        $response = $this->toJson($this->call("PUT", "/api/users/" . $model->user->hash,
            [
                'email'     => 'hello@notlonelygiraffes.com',
                'password'  => 'anotherpassword',
                'firstname' => 'Lonesome',
                'lastname'  => 'Penguin',
                'gender'    => 'F'
            ]
        ));

        $this->assertResponseStatus(200);
        $this->assertEquals('hello@notlonelygiraffes.com', $response->user->email);
        $this->assertEquals('Lonesome', $response->user->firstname);
        $this->assertEquals('Penguin', $response->user->lastname);
        $this->assertEquals('F', $response->user->gender);
    }

    /**
     * @test
     * @depends it_can_create_a_new_user
     */
    public function a_user_cannot_change_their_user_hash_or_id()
    {
        $model = $this->toJson($this->call('POST', '/api/users/', $this->genericUser));
        $modelHash = $model->user->hash;
        $this->asUser($modelHash);

        $response = $this->call(
            "PUT",
            "/api/users/" . $modelHash,
            [
                'id'     => 1000,
                'hash'   => Str::random(32),
                'gender' => 'F'
            ]
        );

        // the system should simply ignore the new data, but not fail
        $this->assertResponseStatus(200);

        $getModel = $this->toJson($this->call('GET', '/api/users/' . $model->user->hash));
        $this->assertEquals($modelHash, $getModel->user->hash);
        $this->assertEquals('F', $getModel->user->gender);
    }

    /**
     * @test
     * @depends it_can_create_a_new_user
     */
    public function a_user_updating_information_must_conform_to_validation()
    {
        $model = $this->toJson($this->call("POST", "/api/users/", $this->genericUser));
        $this->asUser($model->user->hash);

        $response = $this->call(
            "PUT",
            "/api/users/" . $model->user->hash,
            [
                'email' => 'lonelygiraffes.com'
            ]
        );

        $this->assertResponseStatus(422);

        $getModel = $this->repository->get($model->user->hash);
        $this->assertEquals($getModel->email, 'hello@lonelygiraffes.com');
    }

    /**
     * @test
     * @depends it_can_create_a_new_user
     */
    public function a_user_cannot_change_another_users_data()
    {
        $model = $this->toJson($this->call('POST', '/api/users', $this->genericUser));
        $this->asUser($model->user->hash);

        $anotherModel = $this->toJson($this->call('POST', '/api/users', $this->anotherGenericUser));

        $response = $this->call(
            "PUT",
            "/api/users/" . $anotherModel->user->hash,
            [
                'email' => 'evil@example.com'
            ]
        );

        $this->assertResponseStatus(403);

        $getModel = $this->toJson($this->call('GET', '/api/users/' . $anotherModel->user->hash));
        $this->assertEquals($getModel->user->email, 'anotherHello@lonelygiraffes.com');
    }

    /**
     * @test
     * @depends it_can_create_a_new_user
     */
    public function an_administrator_can_change_a_users_data()
    {
        $model = $this->toJson($this->call('POST', '/api/users/', $this->genericUser));
        $anotherModel = $this->toJson($this->call('POST', '/api/users/', $this->anotherGenericUser));
        $this->service->setUserRole($anotherModel->user->hash, 'admin');
        $this->asUser($anotherModel->user->hash);

        $response = $this->call(
            'PUT',
            '/api/users/' . $model->user->hash,
            [
                'email' => 'new@lonelygiraffes.com'
            ]
        );

        $this->assertResponseStatus(200);
        $getModel = $this->toJson($this->call('GET', '/api/users/' . $model->user->hash));
        $this->assertEquals($getModel->user->email, 'new@lonelygiraffes.com');
    }

    /**
     * @test
     * @depends it_can_create_a_new_user
     */
    public function a_user_cannot_change_their_email_to_another_users_email()
    {
        $model = $this->toJson($this->call('POST', '/api/users/', $this->genericUser));
        $this->asUser($model->user->hash);

        $anotherModel = $this->toJson($this->call('POST', '/api/users/', $this->anotherGenericUser));

        $response = $this->call(
            "PUT",
            "/api/users/" . $model->user->hash,
            [
                'email' => 'anotherHello@lonelygiraffes.com'
            ]
        );

        $this->assertResponseStatus(422);

        // make sure everything is intact
        $getModel = $this->toJson($this->call('GET', '/api/users/' . $model->user->hash));
        $this->assertEquals($getModel->user->email, 'hello@lonelygiraffes.com');
        $getAnotherModel = $this->toJson($this->call('GET', '/api/users/' . $anotherModel->user->hash));
        $this->assertEquals($getAnotherModel->user->email, 'anotherHello@lonelygiraffes.com');
    }

    /**
     * @test
     * @depends it_can_create_a_new_user
     */
    public function an_administrator_account_can_delete_a_user()
    {
        $model = $this->toJson($this->call('POST', '/api/users/', $this->genericUser));
        $anotherModel = $this->toJson($this->call('POST', '/api/users/', $this->anotherGenericUser));
        $this->service->setUserRole($anotherModel->user->hash, 'admin');
        $this->asUser($anotherModel->user->hash);

        $response = $this->call("DELETE", "/api/users/" . $model->user->hash);
        $this->assertResponseStatus(200);

        $this->setExpectedException('Giraffe\Common\NotFoundModelException');
        $this->repository->get($model->user->hash);
    }

    /**
     * @test
     * @depends it_can_create_a_new_user
     */
    public function a_user_cannot_delete_their_own_account()
    {
        $model = $this->toJson($this->call('POST', '/api/users/', $this->genericUser));
        $this->asUser($model->user->hash);

        $response = $this->call("DELETE", "/api/users/" . $model->user->hash);
        $this->assertResponseStatus(403);

        $getModel = $this->toJson($this->call('GET', '/api/users/' . $model->user->hash));
        $this->assertEquals($getModel->user->hash, $model->user->hash);
    }
}