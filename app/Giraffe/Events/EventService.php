<?php  namespace Giraffe\Events;

use Giraffe\Common\Service;
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

    public function __construct(
        EventRepository $eventRepository,
        EventAttendeeRepository $attendeeRepository,
        EventInvitationRepository $invitationRepository,
        EventRequestRepository $requestRepository
    ) {
        $this->eventRepository = $eventRepository;
        $this->attendeeRepository = $attendeeRepository;
        $this->invitationRepository = $invitationRepository;
        $this->requestRepository = $requestRepository;
    }

    public function createEvent($data)
    {
        $data['hash'] = Str::random(32);
        return $this->eventRepository->create($data);
    }

    public function get($event)
    {
        return $this->eventRepository->get($event);
    }

    public function delete($event)
    {
        $event = $this->eventRepository->get($event);
        return $this->eventRepository->delete($event);
    }

    public function createRequest($event, $requestingUser)
    {
        $this->gatekeeper->mayI('create', 'event_request')->forThis($event)->please();
        $this->requestRepository->create([]);

    }
} 