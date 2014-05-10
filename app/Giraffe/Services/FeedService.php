<?php  namespace Giraffe\Services;

use Eloquent;
use Giraffe\Helpers\Geolocation\LocationHelper;
use Giraffe\Helpers\Parser\Parser;
use Giraffe\Helpers\Rights\Gatekeeper;
use Giraffe\Models\PostModel;
use Giraffe\Models\UserModel;
use Giraffe\Repositories\PostRepository;
use Giraffe\Repositories\ShoutRepository;
use Giraffe\Repositories\UserRepository;

class FeedService
{

    /**
     * @var \Giraffe\Helpers\Rights\Gatekeeper
     */
    private $gatekeeper;
    /**
     * @var \Giraffe\Repositories\PostRepository
     */
    private $postRepository;
    /**
     * @var \Giraffe\Repositories\UserRepository
     */
    private $userRepository;
    /**
     * @var \Giraffe\Repositories\ShoutRepository
     */
    private $shoutRepository;
    /**
     * @var \Giraffe\Helpers\Parser\Parser
     */
    private $parser;
    /**
     * @var \Giraffe\Helpers\Geolocation\LocationHelper
     */
    private $locationHelper;

    public function __construct(
        Gatekeeper $gatekeeper,
        Parser $parser,
        LocationHelper $locationHelper,
        PostRepository $postRepository,
        UserRepository $userRepository,
        ShoutRepository $shoutRepository
    ) {
        $this->gatekeeper = $gatekeeper;
        $this->postRepository = $postRepository;
        $this->userRepository = $userRepository;
        $this->shoutRepository = $shoutRepository;
        $this->parser = $parser;
        $this->locationHelper = $locationHelper;
    }

    public function addCommentOnPost($user, $post, $comment)
    {

    }

    /**
     * Create a new post.
     *
     * Expected metadata:
     * type: Type of post, defaults to shout if not set or unknown
     * place: {country, state, city}, {country, state}, {country}
     * location: (Event) human-readable location of event
     * url: (Event) URL of event
     *
     * @param        $user
     * @param string $post
     * @param array  $metadata
     *
     * @return PostModel
     */
    public function createPost($user, $post, array $metadata)
    {
        $this->gatekeeper->mayI('create', 'post');

        /** @var UserModel $user */
        $user = $this->userRepository->get($user);

        // figure out location
        if (array_key_exists('location', $metadata)) {
            $country = array_key_exists('country', $metadata['location']) ? $metadata['location']['country'] : null;
            $state = array_key_exists('state', $metadata['location']) ? $metadata['location']['state'] : null;
            $city = array_key_exists('city', $metadata['location']) ? $metadata['location']['city'] : null;
            $cell = $this->locationHelper->convertPlaceToCell($country, $state, $city);
        } else {
            // use user's location if none given
            $country = property_exists($user, 'country') ? $user->country : null;
            $state = property_exists($user, 'state') ? $user->state : null;
            $city = property_exists($user, 'city') ? $user->city : null;
            $cell = property_exists($user, 'cell') ? $user->cell : null;
        }


        $type = array_key_exists('type', $metadata) ? $metadata['type'] : null;
        switch ($type) {
            default:
                {
                $htmlBody = $this->parser->parseComment($post);
                $postable = $this->shoutRepository->create(
                    [
                        'user_id'   => $user->id,
                        'body'      => $post,
                        'html_body' => $htmlBody
                    ]
                );
                break;
                }
        }

        $post = $this->postRepository->createWithPostable(
            [
                'user_id' => $user->id,
                'country' => $country,
                'state'   => $state,
                'city'    => $city,
                'cell'    => $cell
            ],
            $postable
        );
        return $post;
    }

    public function getBuddyFeed($user)
    {

    }

    public function getGlobalFeed($user)
    {

    }

    public function getLocalFeed($user)
    {

    }

    /**
     * @param $post
     *
     * @return Eloquent
     */
    public function getPost($post)
    {
        return $this->postRepository->get($post);
    }
} 