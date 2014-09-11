<?php  namespace Giraffe\Common;

use Illuminate\Events\Dispatcher;
use Illuminate\Support\Collection;

class EventRelay
{
    /**
     * @var Collection
     */
    protected $queue;

    /**
     * @var Dispatcher
     */
    private $dispatcher;

    public function __construct(Dispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
        $this->queue = new Collection();
    }

    /**
     * Queue event to be sent out by relay. It does NOT immediately send it out, and waits for dispatch() first.
     *
     * @param Event $event
     */
    public function trigger(Event $event)
    {
        $this->queue->push($event);
    }

    /**
     * Issue any events queued by trigger(), plus any given as an argument.
     *
     * @param Event $event
     */
    public function dispatch(Event $event = null)
    {
        if ($event) {
            $this->trigger($event);
        }
        $dispatcher = $this->dispatcher;
        $this->queue->each(
            function (Event $event) use ($dispatcher) {
                $dispatcher->fire($event->getName(), $event);
            }
        );
        $this->cancel();
    }

    /**
     * Cancel any queued events within this relay.
     */
    public function cancel()
    {
        $this->queue = new Collection;
    }

    /**
     * Assign an action to trigger when an event takes place.
     *
     * @param          $event
     * @param callable $callback
     */
    public function on($event, Callable $callback)
    {
        $this->dispatcher->listen($event, $callback);
    }

    public function listen(EventListener $listener)
    {
        $listener->subscribe($this);
        return $listener;
    }
} 