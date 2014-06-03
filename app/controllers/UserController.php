<?php

use Giraffe\Common\Controller;
use Giraffe\Users\UserService;

class UserController extends Controller
{
    /**
     * @param Giraffe\Users\UserService $userService
     */
	public function __construct(UserService $userService) {
		$this->userService = $userService;
	}

	public function store() {
		return $this->userService->createUser(Input::all());
	}

	public function destroy($id) {
		return $this->userService->deleteUser($id);
	}

	public function show($id) {
		return $this->userService->getUser($id);
	}

    public function update($id)
    {
        $input = Input::all();
        if (array_key_exists('password', $input)) {
            return $this->userService->changePassword($id, $input['password']);
        }

        // handle other updates
        return $this->userService->updateUser($id, $input);
    }
} 