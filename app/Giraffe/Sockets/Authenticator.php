<?php  namespace Giraffe\Sockets; 

use Giraffe\Common\NotFoundModelException;
use Giraffe\Users\UserRepository;
use League\OAuth2\Server\Storage\SessionInterface;

class Authenticator
{

    /**
     * @var \Giraffe\Users\UserRepository
     */
    private $userRepository;

    /**
     * @var \League\OAuth2\Server\Storage\SessionInterface
     */
    private $sessionManager;

    public function __construct(UserRepository $userRepository, SessionInterface $sessionManager)
    {
        $this->userRepository = $userRepository;
        $this->sessionManager = $sessionManager;
    }

    /**
     * @param $token
     *
     * @return bool|\Giraffe\Users\UserModel
     */
    public function attempt($token)
    {

        $validate = $this->sessionManager->validateAccessToken($token);

        if (!$validate) {
            return false;
        }

        $type = $validate['owner_type'];
        $userId = $validate['owner_id'];

        if ($type != 'user') {
            return false;
        }

        try {
            $user = $this->userRepository->getById($userId);
        } catch (NotFoundModelException $e) {
            return false;
        }

        return $user;
    }
} 