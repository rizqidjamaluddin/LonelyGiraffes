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
        return $this->returnEventModel($new);
    }

    public function show($event)
    {
        $model = $this->eventService->getEvent($event);
        return $this->returnEventModel($model);
    }

    public function delete($event)
    {
        $delete = $this->eventService->deleteEvent($event);
        return $this->returnEventModel($delete);
    }

    public function update($event)
    {
        $edit = $this->eventService->updateEvent($event, Input::all());
        return $this->returnEventModel($edit);
    }

    /**
     * @param $edit
     * @return \Illuminate\Http\Response
     */
    protected function returnEventModel($edit)
    {
        return $this->withItem($edit, new EventTransformer(), 'events');
    }
} 