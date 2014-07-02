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

        $this->assertEquals('hello@lonelygiraffes.com', $response->users[0]->email);
        $this->assertEquals('Lonely Giraffe', $response->users[0]->name);
        $this->assertEquals('M', $response->users[0]->gender);
    }

    /**
     * @test
     * @depends it_can_create_a_new_user
     */
    public function it_fails_to_create_a_user_with_a_bad_email()
    {
        $response = $this->call(
            "POST",
            "/api/users/",
            [
                'email'    => '@lonelygiraffes.x',
                'password' => 'password',
                'name'     => 'Lonely',
                'gender'   => 'M'
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
        $getModel = $this->toJson($this->call("GET", "/api/users/" . $model->users[0]->hash));

        $this->assertResponseStatus(200);
        $this->assertEquals('hello@lonelygiraffes.com', $getModel->users[0]->email);
        $this->assertEquals('Lonely Giraffe', $getModel->users[0]->name);
        $this->assertEquals('M', $getModel->users[0]->gender);
    }

    /**
     * @test
     * @depends         it_can_find_a_user
     * @outputBuffering enabled
     */
    public function it_can_find_a_user_by_email()
    {
        $model = $this->toJson($this->call("POST", "/api/users/", $this->genericUser));
        $getModel = $this->toJson($this->call("GET", "/api/users", array('email' => $model->users[0]->email)));

        $this->assertResponseStatus(200);
        $this->assertEquals('hello@lonelygiraffes.com', $getModel->users[0]->email);
        $this->assertEquals('Lonely Giraffe', $getModel->users[0]->name);
        $this->assertEquals('M', $getModel->users[0]->gender);

        //It should fail when it needs to fail
        $this->call("GET", "/api/users", array('email' => '@lonelygiraffes.x'));
        $this->assertResponseStatus(404);
    }

    /**
     * @test
     * @depends it_can_find_a_user
     * @outputBuffering enabled
     */
    public function it_can_find_a_user_by_name()
    {
        $model1 = $this->toJson($this->call("POST", "/api/users/", $this->genericUser));
        $model2 = $this->toJson($this->call("POST", "/api/users/", $this->anotherGenericUser));
        $model3 = $this->toJson($this->call("POST", "/api/users/", $this->similarGenericUser));


        // Retrieve 1 user
        $getModels = $this->toJson($this->call("GET", "/api/users", array('name' => $model1->users[0]->name)));
        $this->assertResponseStatus(200);
        $this->assertEquals(1, count($getModels->users));
        $this->assertEquals('hello@lonelygiraffes.com', $getModels->users[0]->email);
        $this->assertEquals('Lonely Giraffe', $getModels->users[0]->name);
        $this->assertEquals('M', $getModels->users[0]->gender);


        // Retrieve n users
        $getModels = $this->toJson($this->call("GET", "/api/users", array('name' => $model2->users[0]->name)));
        $this->assertResponseStatus(200);
        $this->assertEquals(2, count($getModels->users));
        $this->assertEquals('anotherHello@lonelygiraffes.com', $getModels->users[0]->email);
        $this->assertEquals('Lonesome Penguin', $getModels->users[0]->name);
        $this->assertEquals('F', $getModels->users[0]->gender);
        $this->assertEquals('similarHello@lonelygiraffes.com', $getModels->users[1]->email);
        $this->assertEquals('Lonesome Penguin', $getModels->users[1]->name);
        $this->assertEquals('M', $getModels->users[1]->gender);


        //It should fail when it needs to fail
        $this->call("GET", "/api/users", array('name' => 'Benadryl Cabbagepatch'));
        $this->assertResponseStatus(404);
    }


    /**
     * @test
     * @depends it_can_create_a_new_user
     */
    public function a_user_can_update_information()
    {
        $model = $this->toJson($this->call("POST", "/api/users/", $this->genericUser));
        $this->asUser($model->users[0]->hash);

        $response = $this->toJson(
            $this->call(
                "PUT",
                "/api/users/" . $model->users[0]->hash,
                [
                    'email'    => 'hello@notlonelygiraffes.com',
                    'password' => 'anotherpassword',
                    'name'     => 'Lonesome Penguin',
                    'gender'   => 'F'
                ]
            )
        );

        $this->assertResponseStatus(200);
        $this->assertEquals('hello@notlonelygiraffes.com', $response->users[0]->email);
        $this->assertEquals('Lonesome Penguin', $response->users[0]->name);
        $this->assertEquals('F', $response->users[0]->gender);
    }

    /**
     * @test
     * @depends it_can_create_a_new_user
     */
    public function a_user_cannot_change_their_user_hash_or_id()
    {
        $model = $this->toJson($this->call('POST', '/api/users/', $this->genericUser));
        $modelHash = $model->users[0]->hash;
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

        $getModel = $this->toJson($this->call('GET', '/api/users/' . $model->users[0]->hash));
        $this->assertEquals($modelHash, $getModel->users[0]->hash);
        $this->assertEquals('F', $getModel->users[0]->gender);
    }

    /**
     * @test
     * @depends it_can_create_a_new_user
     */
    public function a_user_updating_information_must_conform_to_validation()
    {
        $model = $this->toJson($this->call("POST", "/api/users/", $this->genericUser));
        $this->asUser($model->users[0]->hash);

        $response = $this->call(
            "PUT",
            "/api/users/" . $model->users[0]->hash,
            [
                'email' => 'lonelygiraffes.com'
            ]
        );

        $this->assertResponseStatus(422);

        $getModel = $this->repository->get($model->users[0]->hash);
        $this->assertEquals($getModel->email, 'hello@lonelygiraffes.com');
    }

    /**
     * @test
     * @depends it_can_create_a_new_user
     */
    public function a_user_cannot_change_another_users_data()
    {
        $model = $this->toJson($this->call('POST', '/api/users', $this->genericUser));
        $this->asUser($model->users[0]->hash);

        $anotherModel = $this->toJson($this->call('POST', '/api/users', $this->anotherGenericUser));

        $response = $this->call(
            "PUT",
            "/api/users/" . $anotherModel->users[0]->hash,
            [
                'email' => 'evil@example.com'
            ]
        );

        $this->assertResponseStatus(403);

        $getModel = $this->toJson($this->call('GET', '/api/users/' . $anotherModel->users[0]->hash));
        $this->assertEquals($getModel->users[0]->email, 'anotherHello@lonelygiraffes.com');
    }

    /**
     * @test
     * @depends it_can_create_a_new_user
     */
    public function an_administrator_can_change_a_users_data()
    {
        $model = $this->toJson($this->call('POST', '/api/users/', $this->genericUser));
        $anotherModel = $this->toJson($this->call('POST', '/api/users/', $this->anotherGenericUser));
        Artisan::call('lgutil:promote', ['email' => $this->anotherGenericUser['email'], '--force' => true]);
        $this->asUser($anotherModel->users[0]->hash);

        $response = $this->call(
            'PUT',
            '/api/users/' . $model->users[0]->hash,
            [
                'email' => 'new@lonelygiraffes.com'
            ]
        );

        $this->assertResponseStatus(200);
        $getModel = $this->toJson($this->call('GET', '/api/users/' . $model->users[0]->hash));
        $this->assertEquals($getModel->users[0]->email, 'new@lonelygiraffes.com');
    }

    /**
     * @test
     * @depends it_can_create_a_new_user
     */
    public function a_user_cannot_change_their_email_to_another_users_email()
    {
        $model = $this->toJson($this->call('POST', '/api/users/', $this->genericUser));
        $this->asUser($model->users[0]->hash);

        $anotherModel = $this->toJson($this->call('POST', '/api/users/', $this->anotherGenericUser));

        $response = $this->call(
            "PUT",
            "/api/users/" . $model->users[0]->hash,
            [
                'email' => 'anotherHello@lonelygiraffes.com'
            ]
        );

        $this->assertResponseStatus(422);

        // make sure everything is intact
        $getModel = $this->toJson($this->call('GET', '/api/users/' . $model->users[0]->hash));
        $this->assertEquals($getModel->users[0]->email, 'hello@lonelygiraffes.com');
        $getAnotherModel = $this->toJson($this->call('GET', '/api/users/' . $anotherModel->users[0]->hash));
        $this->assertEquals($getAnotherModel->users[0]->email, 'anotherHello@lonelygiraffes.com');
    }

    /**
     * @test
     * @depends it_can_create_a_new_user
     */
    public function an_administrator_account_can_delete_a_user()
    {

        $mario = $this->registerMario();
        $peach = $this->registerAndLoginAsPeach();

        $response = $this->call("DELETE", "/api/users/" . $mario->hash);
        $this->assertResponseStatus(200);

        $this->setExpectedException('Giraffe\Common\NotFoundModelException');
        $this->repository->get($mario->hash);
    }

    /**
     * @test
     * @depends it_can_create_a_new_user
     */
    public function a_user_cannot_delete_their_own_account()
    {
        $model = $this->toJson($this->call('POST', '/api/users/', $this->genericUser));
        $this->asUser($model->users[0]->hash);

        $response = $this->call("DELETE", "/api/users/" . $model->users[0]->hash);
        $this->assertResponseStatus(403);

        $getModel = $this->toJson($this->call('GET', '/api/users/' . $model->users[0]->hash));
        $this->assertEquals($getModel->users[0]->hash, $model->users[0]->hash);
    }
}