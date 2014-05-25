<?php  namespace Giraffe\Events;
class EventService 
{
    /**
     * @var EventRepository
     */
    private $eventRepository;

    public function __construct(EventRepository $eventRepository)
    {
        $this->eventRepository = $eventRepository;
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
} 