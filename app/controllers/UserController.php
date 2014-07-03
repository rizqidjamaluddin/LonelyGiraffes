<?php

use Giraffe\Common\Controller;
use Giraffe\Users\UserModel;
use Giraffe\Users\UserService;
use Dingo\Api\Http\ResponseBuilder;

class UserController extends Controller
{
    /**
     * @param Giraffe\Users\UserService $userService
     */
	public function __construct(UserService $userService) {
		$this->userService = $userService;
        parent::__construct();
	}

	public function store() {
		$model = $this->userService->createUser(Input::all());
        return $this->returnUserModel($model);
	}

	public function destroy($id) {
		$model = $this->userService->deleteUser($id);
        return $this->returnUserModel($model);
	}

	public function show($id) {
		$model = $this->userService->getUser($id);
        return $this->returnUserModel($model);
	}

    public function index() {
        // Currently only for 'email' OR 'name', but not both simultaneously.
        if((!Input::get('email') && !Input::get('name')) ||
            (Input::get('email') && Input::get('name'))
        )
            throw new BadRequestHttpException();

        if(Input::get('email')) {
            $model = $this->userService->getUserByEmail(Input::get('email'));
            $model = $this->returnUserModel($model);
            return $model;
        }

        if(Input::get('name')) {
            $models = $this->userService->getUsersByName(Input::get('name'));
            $models = $this->returnUserModels($models);
            return $models;
        }
    }

    public function update($id)
    {
        $model = $this->userService->updateUser($id, Input::all());
        return $this->returnUserModel($model);
    }

    /**
     * @param UserModel $model
     *
     * @return \Illuminate\Http\Response
     */
    public function returnUserModel(UserModel $model)
    {
        return $this->withItem($model, $model->getTransformer(), 'users');
    }

    /**
     * @param Illuminate\Database\Eloquent\Collection $models
     *
     * @return \Illuminate\Http\Response
     */
    public function returnUserModels(Illuminate\Database\Eloquent\Collection $models) {
        return $this->withCollection($models, $models->first()->getTransformer(), 'users');
    }
}