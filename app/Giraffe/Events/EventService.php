<?php  namespace Giraffe\Events;

use Carbon\Carbon;
use Giraffe\Common\InvalidCreationException;
use Giraffe\Common\Service;
use Giraffe\Feed\PostGeneratorHelper;
use Giraffe\Feed\PostRepository;
use Giraffe\Geolocation\LocationService;
use Giraffe\Parser\Parser;
use Giraffe\Users\UserRepository;
use Giraffe\Users\UserService;
use Str;

class EventService extends Service
{
    /**
     * @var EventRepository
     */
    private $eventRepository;
    /**
     * @var EventAttendeeRepository
     */
    private $attendeeRepository;
    /**
     * @var EventInvitationRepository
     */
    private $invitationRepository;
    /**
     * @var EventRequestRepository
     */
    private $requestRepository;

    /**
     * @var UserRepository
     */
    private $userRepository;
    /**
     * @var Parser
     */
    private $parser;
    /**
     * @var PostGeneratorHelper
     */
    private $postGeneratorHelper;
    /**
     * @var LocationService
     */
    private $locationService;
    /**
     * @var PostRepository
     */
    private $postRepository;

    public function __construct(
        EventRepository $eventRepository,
        EventAttendeeRepository $attendeeRepository,
        EventInvitationRepository $invitationRepository,
        EventRequestRepository $requestRepository,
        UserRepository $userRepository,
        Parser $parser,
        PostGeneratorHelper $postGeneratorHelper,
        PostRepository $postRepository,
        LocationService $locationService
    ) {
        parent::__construct();

        $this->eventRepository = $eventRepository;
        $this->attendeeRepository = $attendeeRepository;
        $this->invitationRepository = $invitationRepository;
        $this->requestRepository = $requestRepository;
        $this->userRepository = $userRepository;
        $this->parser = $parser;
        $this->postGeneratorHelper = $postGeneratorHelper;
        $this->locationService = $locationService;
        $this->postRepository = $postRepository;
    }

    public function createEvent($user, $data)
    {
        $this->gatekeeper->mayI('create', 'event')->please();

        $data = array_only($data, ['name', 'body', 'url', 'location', 'city', 'state', 'country', 'timestamp']);

        // validate incoming data
        try {
        $data['timestamp'] = Carbon::parse($data['timestamp'])->setTimezone('utc');

        } catch (\Exception $e) {
            throw new InvalidCreationException('Invalid date given.');
        }

        $data['hash'] = Str::random(32);
        $data['html_body'] = $this->parser->parseRich($data['body']);
        $user = $this->userRepository->getByHash($user);
        $data['user_id'] = $user->id;

        if ($cacheString = $this->locationService->getCacheStringFromAttributesArray($data)) {
            $data['cell'] = $cacheString;
        }

        $event = $this->eventRepository->create($data);
        $this->postGeneratorHelper->generate($event);
        return $event;
    }

    public function getEvent($hash)
    {
        return $this->eventRepository->getByHash($hash);
    }

    public function deleteEvent($hash)
    {
        $event = $this->eventRepository->getByHash($hash);
        $this->gatekeeper->mayI('delete', $event)->please();
        $this->postRepository->deleteForPostable($event);
        $this->eventRepository->deleteByHash($hash);
        return $event;
    }

    public function updateEvent($hash, $data)
    {
        $event = $this->eventRepository->getByHash($hash);
        $this->gatekeeper->mayI('update', $event)->please();

        $data = array_only($data, ['name', 'body', 'url', 'location', 'city', 'state', 'country', 'timestamp']);
        if (array_key_exists('body', $data)) {
            $data['html_body'] = $this->parser->parseRich($data['body']);
        }
        return $this->eventRepository->update($hash, $data);
    }

    public function createRequest($event, $requestingUser)
    {
        $this->gatekeeper->mayI('create', 'event_request')->forThis($event)->please();
        $this->requestRepository->create([]);

    }

    public function findNearbyUser($user)
    {
        $this->gatekeeper->mayI('find_nearby', 'event')->please();
        $user = $this->userRepository->getByHash($user);
        return $this->locationService->getNearbyFromRepository($user, $this->eventRepository);

    }

    public function getEventParticipants($event)
    {
        /** @var EventModel $event */
        $event = $this->eventRepository->getByHash($event);
        $this->gatekeeper->mayI('read', $event)->please();
        $participants = $event->getParticipants();
        return $participants;
    }

    public function joinEvent($event, $me)
    {
        /** @var EventModel $event */
        $event = $this->eventRepository->getByHash($event);
        $this->gatekeeper->mayI('join', $event)->please();
        $me = $this->userRepository->getByHash($me);
        $event->addParticipant($me);
        return $event;
    }

} 