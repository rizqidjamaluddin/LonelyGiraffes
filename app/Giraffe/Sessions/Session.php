<?php  namespace Giraffe\Sessions;

use Giraffe\Authorization\ProtectedResource;
use Giraffe\Common\ConfigurationException;
use Giraffe\Users\UserModel;

/**
 * Representation of a single OAuth Session - not a "model" so as not to conflate with Eloquent models. These are more
 * complex and implicate multiple tables.
 *
 * Incomplete and unused.
 *
 * @unstable
 */
class Session implements ProtectedResource
{

    const SESSIONS_TABLE = 'oauth_sessions';
    const ACCESS_TOKENS_TABLE = 'oauth_access_tokens';

    /**
     * @var integer
     */
    protected $userId = null;

    /**
     * @var string
     */
    protected $client = null;

    public function __construct($userId, $client)
    {
        $this->userId = $userId;
        $this->client = $client;
    }

    public static function findByAccessToken($accessToken)
    {
        $token = \DB::table(self::ACCESS_TOKENS_TABLE)->where('access_token', $accessToken)->first();
        if (!$token) {
            throw new TokenNotFoundException;
        }
        $session = \DB::table(self::SESSIONS_TABLE)->where('id', $token->session_id)->first();

        if ($session->owner_type !== 'user') {
            throw new ConfigurationException('Session service does not support Client Credential flows');
        }

        return new static($session->owner_id, $session->client_id);
    }

    public function findByUser($userId)
    {
        $sessions = \DB::table(self::SESSIONS_TABLE)->where('owner_id', $userId)->where('owner_type', 'user')->get();
        $results = [];
    }

    /**
     * Lowercase name of this resource.
     *
     * @return string
     */
    public function getResourceName()
    {
        return 'session';
    }

    /**
     * @param \Giraffe\Users\UserModel $user
     *
     * @return bool
     */
    public function checkOwnership(UserModel $user)
    {
        return $user->id === $this->userId;
    }
}