<?php

use Giraffe\Common\Controller;
use Giraffe\Events\EventService;
use Giraffe\Events\EventTransformer;
use Giraffe\Users\UserTransformer;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

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

    public function index()
    {
        if (Input::exists('nearby')) {
            $nearby = $this->eventService->findNearbyUser($this->gatekeeper->me());
            return $this->withCollection($nearby, new EventTransformer(), 'events');
        }

        throw new BadRequestHttpException;
    }

    public function store() {
	$in = Input::all();
	$in['body'] = Input::get('body');
        $new = $this->eventService->createEvent($this->gatekeeper->me(), $in);
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

    public function showParticipants($event)
    {
        $participants = $this->eventService->getEventParticipants($event);
        return $this->withCollection($participants, new UserTransformer(), 'participants');
    }

    public function join($event)
    {
        $this->eventService->joinEvent($event, $this->gatekeeper->me());
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
