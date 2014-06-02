<?php

use Giraffe\Common\Controller;
use Giraffe\Users\UserService;

class UserController extends Controller
{
	/**
	 * @param UserRepository $userRepository [description]
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
} 