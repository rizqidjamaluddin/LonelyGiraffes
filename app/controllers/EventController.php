<?php

use Giraffe\Common\Controller;
use Giraffe\Events\EventService;

class EventController extends Controller
{

    /**
     * @var \Giraffe\Events\EventService
     */
    private $eventService;

    public function __construct(EventService $eventService)
    {
        $this->eventService = $eventService;
    }

    public function store() {
        return $this->eventService->createEvent(Input::all());
    }

    public function show($event)
    {
        return $this->eventService->getEvent($event);
    }

    public function delete($event)
    {
        return $this->eventService->deleteEvent($event);
    }

    public function update($event)
    {
        return $this->eventService->updateEvent($event, Input::all());
    }
} 