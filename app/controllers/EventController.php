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
        return $this->eventService->get($event);
    }

    public function delete($event)
    {
        $event = $this->eventService->get($event);
        return;
    }
} 