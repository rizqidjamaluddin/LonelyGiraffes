<?php

use Giraffe\Common\Controller;
use Giraffe\Events\EventService;
use Giraffe\Events\EventTransformer;

class EventController extends Controller
{

    /**
     * @var \Giraffe\Events\EventService
     */
    private $eventService;

    public function __construct(EventService $eventService)
    {
        $this->eventService = $eventService;
        parent::__construct();
    }

    public function store() {
        $new = $this->eventService->createEvent($this->gatekeeper->me(), Input::all());
        return $this->withItem($new, new EventTransformer(), 'event');
    }

    public function show($event)
    {
        $model = $this->eventService->getEvent($event);
        return $this->withItem($model, new EventTransformer(), 'event');
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