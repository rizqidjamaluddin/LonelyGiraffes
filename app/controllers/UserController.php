<?php

use Giraffe\Authorization\GatekeeperUnauthorizedException;
use Giraffe\Common\Controller;
use Giraffe\Common\NotFoundModelException;
use Giraffe\Users\UserModel;
use Giraffe\Users\UserService;
use Dingo\Api\Http\ResponseBuilder;
use Giraffe\Users\UserTransformer;
use Illuminate\Support\Collection;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class UserController extends Controller
{
    protected $key = 'users';

    /**
     * @param Giraffe\Users\UserService $userService
     */
    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
        parent::__construct();
    }

    public function store()
    {
        $model = $this->userService->createUser(Input::all());
        return $this->returnUserModel($model);
    }

    public function destroy($id)
    {
        $model = $this->userService->deleteUser($id);
        return $this->returnUserModel($model);
    }

    public function show($id)
    {
        $model = $this->userService->getUser($id);
        return $this->returnUserModel($model);
    }

    public function index()
    {

        if (Input::exists('me')) {
            try {
                $user = $this->userService->getUser($this->gatekeeper->me());
                return $this->returnUserModel($user);
            } catch (NotFoundModelException $e) {
                throw new GatekeeperUnauthorizedException;
            }
        }

        if (Input::get('email')) {
            try {
                $model = $this->userService->getUserByEmail(Input::get('email'));
                $model = $this->returnUserModel($model);
                return $model;
            } catch (NotFoundModelException $e) {
                return $this->returnUserModels(new Collection());
            }
        }

        if (Input::get('name')) {
            $models = $this->userService->getUsersByName(Input::get('name'));
            $models = $this->returnUserModels($models);
            return $models;
        }

        if (Input::exists('nearby')) {
            $models = $this->userService->getNearbyUsers($this->gatekeeper->me());
            return $this->returnUserModels($models);
        }

        throw new BadRequestHttpException();
    }

    public function update($id)
    {
        $model = $this->userService->updateUser($id, Input::all());
        return $this->returnUserModel($model);
    }

    public function enterTutorialMode($user)
    {
        $this->userService->enableTutorialMode($user);
        return ['message' => 'Tutorial mode enabled.'];
    }

    public function endTutorialMode($user)
    {
        $this->userService->disableTutorialMode($user);
        return ['message' => 'Tutorial mode disabled.'];
    }

    /**
     * @param UserModel $model
     *
     * @return \Illuminate\Http\Response
     */
    public function returnUserModel(UserModel $model)
    {
        return $this->withItem($model, new UserTransformer());
    }

    /**
     * @param Collection $models
     *
     * @return \Illuminate\Http\Response
     */
    public function returnUserModels(Collection $models)
    {
        return $this->withCollection($models, new UserTransformer());
    }
}