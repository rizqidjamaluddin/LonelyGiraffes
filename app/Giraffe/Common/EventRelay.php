<?php  namespace Giraffe\Common; 

class EventRelay 
{
    /**
     * Queue event to be sent out by relay. It does NOT immediately send it out, and waits for dispatch() first.
     *
     * @param Event $event
     */
    public function trigger(Event $event)
    {

    }

    /**
     * Issue any events queued by trigger(), plus any given as an argument.
     *
     * @param Event $event
     */
    public function dispatch(Event $event = null)
    {
        
    }

    /**
     * Cancel any queued events within this relay.
     */
    public function cancel()
    {

    }

    /**
     * Assign an action to trigger when an event takes place.
     *
     * @param          $event
     * @param callable $callback
     */
    public function on($event, Callable $callback)
    {

    }

    public function listen(EventListener $listener)
    {
        $listener->subscribe($this);
        return $listener;
    }
} 