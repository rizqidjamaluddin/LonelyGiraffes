<?php  namespace Giraffe\Sockets; 

use Giraffe\Sockets\Response\SocketErrorResponse;
use Giraffe\Sockets\Response\SocketResponse;
use Giraffe\Sockets\Support\UnknownCommandException;
use Giraffe\Users\UserTransformer;

class CallRouter
{
    protected $authenticator;

    public function __construct()
    {
        $this->authenticator = \App::make(Authenticator::class);
    }

    /**
     * @param                             $uri
     * @param array                       $arguments
     * @param AuthenticatedWampConnection $connection
     * @throws UnknownCommandException
     * @return SocketResponse
     */
    public function handle($uri, Array $arguments, AuthenticatedWampConnection $connection)
    {
        // handle built-in routes
        if ($uri == 'whoami') {
            if ($user = $connection->getAuthentication()) {
                return new SocketResponse((new UserTransformer())->transform($user));
            } else {
                return new SocketResponse('GUEST');
            }
        }
        if ($uri == 'authenticate') {
            // to-do: move this to a curl-based call to the webserver instead
            $user = $this->authenticator->attempt($arguments[0]);
            if (!$user) {
                return new SocketErrorResponse('Token not recognized.', 'Authentication Failed');
            }
            $connection->setAuthentication($user);
            return new SocketResponse((new UserTransformer())->transform($user));
        }

        throw new UnknownCommandException;
    }
} 