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
     */
    public function it_requires_name_email_and_password_in_registration()
    {
        // missing name
        $this->call('POST', '/api/users', ['email' => 'valid@example.com', 'password' => '12345']);
        $this->assertResponseStatus(422);

        // missing password
        $this->call('POST', '/api/users', ['email' => 'valid@example.com', 'name' => 'John']);
        $this->assertResponseStatus(422);

        // missing password and name
        $this->call('POST', '/api/users', ['email' => 'valid@example.com']);
        $this->assertResponseStatus(422);

        // check to make sure it's not registered
        $check = $this->call('GET', '/api/users', ['email' => 'valid@example.com']);
        $this->assertResponseOk();
        $this->assertEquals(count($this->toJson($check)->users), 0);
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
        $this->assertEquals('Lonely Giraffe', $getModel->users[0]->name);
        $this->assertEquals('M', $getModel->users[0]->gender);
    }

    /**
     * @test
     */
    public function a_users_email_is_only_visible_when_logged_in_as_them()
    {
        $mario = $this->registerMario()->hash;

        $fetchPublic = $this->callJson('GET', "/api/users/$mario");
        $this->assertTrue(!array_key_exists('email', (array) $fetchPublic->users[0]));

        $this->asUser($mario);
        $fetchPrivate = $this->callJson('GET', "/api/users/$mario");
        $this->assertEquals($this->mario['email'], $fetchPrivate->users[0]->email);
    }

    /**
     * @test
     * @depends         it_can_find_a_user
     * @outputBuffering enabled
     */
    public function it_can_find_a_user_by_email()
    {
        $model = $this->toJson($this->call("POST", "/api/users/", $this->genericUser));
        $getModel = $this->callJson("GET", "/api/users", ['email' => $this->genericUser['email']]);

        $this->assertResponseStatus(200);
        $this->assertEquals('Lonely Giraffe', $getModel->users[0]->name);
        $this->assertEquals('M', $getModel->users[0]->gender);

        //It should fail when it needs to fail
        $fail =$this->call("GET", "/api/users", array('email' => '@lonelygiraffes.x'));
        $this->assertResponseStatus(200);
        $this->assertEquals(count($this->toJson($fail)->users), 0);
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
        $this->assertEquals('Lonely Giraffe', $getModels->users[0]->name);
        $this->assertEquals('M', $getModels->users[0]->gender);


        // Retrieve n users
        $getModels = $this->toJson($this->call("GET", "/api/users", array('name' => $model2->users[0]->name)));
        $this->assertResponseStatus(200);
        $this->assertEquals(2, count($getModels->users));
        $this->assertEquals('Lonesome Penguin', $getModels->users[0]->name);
        $this->assertEquals('F', $getModels->users[0]->gender);
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
    public function it_can_find_a_user_only_by_hash() {
        $model = $this->toJson($this->call("POST", "/api/users/", $this->genericUser));

        $raw_user = $this->service->getUser($model->users[0]->hash);

        // ID
        $this->toJson($this->call("GET", "/api/users/" . $raw_user->id));
        $this->assertResponseStatus(404);

        // Name
        $this->toJson($this->call("GET", "/api/users/" . $model->users[0]->name));
        $this->assertResponseStatus(404);

        // Hash
        $this->toJson($this->call("GET", "/api/users/" . $model->users[0]->hash));
        $this->assertResponseStatus(200);
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

        $hash = $anotherModel->users[0]->hash;
        $response = $this->call(
            "PUT",
            "/api/users/" . $hash,
            [
                'email' => 'evil@example.com'
            ]
        );

        $this->assertResponseStatus(403);

        $this->asUser($hash);
        $getModel = $this->toJson($this->call('GET', '/api/users/' . $hash));
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

        $hash = $model->users[0]->hash;
        $response = $this->call(
            'PUT',
            '/api/users/' . $hash,
            [
                'email' => 'new@lonelygiraffes.com'
            ]
        );

        $this->assertResponseStatus(200);

        $this->asUser($hash);
        $getModel = $this->toJson($this->call('GET', '/api/users/' . $hash));
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
        $this->asUser($model->users[0]->hash);
        $getModel = $this->toJson($this->call('GET', '/api/users/' . $model->users[0]->hash));
        $this->assertEquals($getModel->users[0]->email, 'hello@lonelygiraffes.com');
        $this->asUser($anotherModel->users[0]->hash);
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

        $fetch = $this->call("GET", "/api/users/" . $mario->hash);
        $this->assertResponseStatus(404);

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

    /**
     * @test
     * @see Issue #2
     */
    public function user_genders_are_optional()
    {
        $data = array_only($this->genericUser, ['name', 'password', 'email']);
        $insert = $this->toJson($this->call('POST', '/api/users', $data))->users[0];
        $this->assertResponseOk();
        $this->assertEquals($insert->name, $this->genericUser['name']);

        $check = $this->toJson($this->call('GET', '/api/users/' . $insert->hash))->users[0];
        $this->assertResponseOk();
        $this->assertEquals($check->name, $this->genericUser['name']);
    }

    /**
     * @test
     */
    public function users_have_a_relationship_of_self_on_their_endpoint()
    {
        $mario = $this->registerAndLoginAsMario();
        $endpoint = $this->callJson('GET', "/api/users/" . $mario->hash);
        $this->assertResponseOk();
        $this->assertTrue(in_array('self', $endpoint->users[0]->relationships));
    }

    /**
     * @test
     */
    public function clients_can_find_their_acting_user_with_me()
    {
        $mario = $this->registerAndLoginAsMario();
        $endpoint = $this->callJson('GET', "/api/users/?me");
        $this->assertResponseOk();
        $this->assertTrue(in_array('self', $endpoint->users[0]->relationships));
        $this->assertEquals($mario->hash, $endpoint->users[0]->hash);

        // check that guests get a 401 if they do this
        $this->asGuest();
        $this->callJson('GET', "/api/users/?me");
        $this->assertResponseStatus(401);
    }

    /**
     * @test
     */
    public function users_can_have_tutorial_flags()
    {
        $mario = $this->registerAndLoginAsMario();
        $endpoint = $this->callJson('GET', "/api/users/?me");
        $this->assertEquals(1, $endpoint->users[0]->tutorial_flag);

        $this->callJson('POST', "/api/users/{$mario->hash}/end-tutorial-mode");
        $this->assertResponseOk();

        $e2 = $this->callJson('GET', "/api/users/{$mario->hash}");
        $this->assertEquals(0, $e2->users[0]->tutorial_flag);
        // this should also work
        $e2 = $this->callJson('GET', "/api/users/?me");
        $this->assertEquals(0, $e2->users[0]->tutorial_flag);

        $this->callJson('POST', "/api/users/{$mario->hash}/tutorial-mode");
        $this->assertResponseOk();

        $endpoint = $this->callJson('GET', "/api/users/?me");
        $this->assertEquals(1, $endpoint->users[0]->tutorial_flag);

    }

    /**
     * @test
     */
    public function users_cannot_change_other_users_tutorial_flags()
    {
        $mario = $this->registerAndLoginAsMario();
        $endpoint = $this->callJson('GET', "/api/users/{$mario->hash}");
        $this->assertEquals(1, $endpoint->users[0]->tutorial_flag);

        // try to disable it as bowser
        $bowser = $this->registerAndLoginAsBowser();
        $this->callJson('POST', "/api/users/{$mario->hash}/end-tutorial-mode");
        $this->assertResponseStatus(403);

        // try to disable it as a guest
        $this->asGuest();
        $this->callJson('POST', "/api/users/{$mario->hash}/end-tutorial-mode");
        $this->assertResponseStatus(401);

        // check, should still be on
        $this->asUser($mario->hash);
        $endpoint = $this->callJson('GET', "/api/users/{$mario->hash}");
        $this->assertEquals(1, $endpoint->users[0]->tutorial_flag);

        // end tutorial mode properly
        $this->callJson('POST', "/api/users/{$mario->hash}/end-tutorial-mode");
        $this->assertResponseOk();

        // try to enable it as bowser
        $this->asUser($bowser->hash);
        $this->callJson('POST', "/api/users/{$mario->hash}/tutorial-mode");
        $this->assertResponseStatus(403);

        // try to enable it as a guest
        $this->asGuest();
        $this->callJson('POST', "/api/users/{$mario->hash}/tutorial-mode");
        $this->assertResponseStatus(401);

        // should still be off
        $this->asUser($mario->hash);
        $endpoint = $this->callJson('GET', "/api/users/{$mario->hash}");
        $this->assertEquals(0, $endpoint->users[0]->tutorial_flag);
    }

    /**
     * @test
     */
    public function users_cannot_see_other_users_tutorial_flags()
    {
        $mario = $this->registerAndLoginAsMario();
        $endpoint = $this->callJson('GET', "/api/users/?me");
        $this->assertEquals(1, $endpoint->users[0]->tutorial_flag);

        $this->registerAndLoginAsBowser();
        $endpoint = $this->callJson('GET', "/api/users/{$mario->hash}");
        $this->assertTrue(!array_key_exists('tutorial_flag', (array) $endpoint->users[0]));

    }
}