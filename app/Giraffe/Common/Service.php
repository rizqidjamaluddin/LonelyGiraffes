<?php  namespace Giraffe\Common;


use App;

abstract class Service
{

    /**
     * @var \Giraffe\Authorization\Gatekeeper
     */
    protected $gatekeeper;

    public function __construct()
    {
        $this->gatekeeper = App::make('Giraffe\Authorization\Gatekeeper');
        $this->log = App::make('Giraffe\Logging\Log');
    }
} 