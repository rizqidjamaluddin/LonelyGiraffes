<?php  namespace Giraffe\Common;


use App;
use Giraffe\Logging\Log;

abstract class Service
{

    /**
     * @var \Giraffe\Authorization\Gatekeeper
     */
    protected $gatekeeper;

    /**
     * @var EventRelay
     */
    protected $relay;

    /**
     * @var Log
     */
    protected $log;

    public function __construct()
    {
        $this->gatekeeper = App::make('Giraffe\Authorization\Gatekeeper');
        $this->log = App::make('Giraffe\Logging\Log');
        $this->relay = App::make('Giraffe\Common\EventRelay');
    }
} 