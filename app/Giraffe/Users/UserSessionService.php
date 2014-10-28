<?php  namespace Giraffe\Users;

use League\OAuth2\Server\Storage\SessionInterface;

/**
 * Deal with details regarding OAuth tables. This class is not under test as it is assumed to work with the OAuth
 * package in use.
 *
 * @package Giraffe\Users
 */
class UserSessionService
{

    /**
     * @var \League\OAuth2\Server\Storage\SessionInterface
     */
    private $session;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    public function logoutEverywhere(UserModel $userModel, $clientId)
    {
        $this->session->deleteSession($clientId, 'user', $userModel->id);
    }

    public function cleanLogout(UserModel $userModel, $accessToken)
    {
        // get desired access token first
        $token = \DB::table('oauth_session_access_tokens')
                    ->where('access_token', $accessToken)
                    ->get();

        // save session id
        $sessionId = $token['session_id'];

        // destroy it
        \DB::table('oauth_session_access_tokens')
           ->delete($token['id']);

        // check if token is in use elsewhere
        $tokenCount = \DB::table('oauth_session_access_tokens')
                         ->where('session_id', $sessionId)
                         ->count();

        $authCodeCount = \DB::table('oauth_session_authcodes')
                            ->where('session_id', $sessionId)
                            ->count();

        // destroy session entirely if there are no other tokens using it
        if ($tokenCount == 0 && $authCodeCount == 0) {
            \DB::table('oauth_sessions')
                ->delete($sessionId);
        }

    }

} 