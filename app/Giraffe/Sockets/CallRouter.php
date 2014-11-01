<?php  namespace Giraffe\Sockets; 

class CallRouter
{
    public function handle($uri, Array $arguments)
    {
        // handle built-in routes
        if ($uri == 'whoami') {
            return 'foo';
        }
        if ($uri == 'authenticate') {
            /** @var Authenticator $authenticator */
            $authenticator = \App::make(Authenticator::class);
            // $authenticator->attempt($arguments);
            return $arguments;
        }
        echo $uri;

        return null;
    }
} 