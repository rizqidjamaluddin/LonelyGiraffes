<?php  namespace Giraffe\Sockets; 

use Giraffe\Common\NotFoundModelException;
use Giraffe\Users\UserRepository;

class Authenticator
{

    /**
     * @var \Giraffe\Users\UserRepository
     */
    private $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function attempt($hash, $token)
    {
        try {
            $user = $this->userRepository->getByHash($hash);
        } catch (NotFoundModelException $e) {
            return false;
        }

        

        return true;
    }
} 