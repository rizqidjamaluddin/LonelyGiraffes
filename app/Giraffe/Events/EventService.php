<?php  namespace Giraffe\Events;

use Giraffe\Common\Service;
use Giraffe\Feed\PostGeneratorHelper;
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

    public function __construct(
        EventRepository $eventRepository,
        EventAttendeeRepository $attendeeRepository,
        EventInvitationRepository $invitationRepository,
        EventRequestRepository $requestRepository,
        UserRepository $userRepository,
        Parser $parser,
        PostGeneratorHelper $postGeneratorHelper
    ) {
        parent::__construct();

        $this->eventRepository = $eventRepository;
        $this->attendeeRepository = $attendeeRepository;
        $this->invitationRepository = $invitationRepository;
        $this->requestRepository = $requestRepository;
        $this->userRepository = $userRepository;
        $this->parser = $parser;
        $this->postGeneratorHelper = $postGeneratorHelper;
    }

    public function createEvent($user, $data)
    {
        $this->gatekeeper->mayI('create', 'event')->please();

        $data = array_only($data, ['name', 'body', 'url', 'location', 'city', 'state', 'country', 'timestamp']);
        $data['hash'] = Str::random(32);
        $data['html_body'] = $this->parser->parseComment($data['body']);
        $user = $this->userRepository->getByHash($user);
        $data['user_id'] = $user->id;

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
        $this->eventRepository->deleteByHash($hash);
        return $event;
    }

    public function updateEvent($hash, $data)
    {
        $event = $this->eventRepository->getByHash($hash);
        $this->gatekeeper->mayI('update', $event)->please();

        $data = array_only($data, ['name', 'body', 'url', 'location', 'city', 'state', 'country', 'timestamp']);
        if (array_key_exists('body', $data)) {
            $data['html_body'] = $this->parser->parseComment($data['body']);
        }
        return $this->eventRepository->update($hash, $data);
    }

    public function createRequest($event, $requestingUser)
    {
        $this->gatekeeper->mayI('create', 'event_request')->forThis($event)->please();
        $this->requestRepository->create([]);

    }
} 