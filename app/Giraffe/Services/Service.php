<?php  namespace Giraffe\Services;


use App;

abstract class Service
{

    /**
     * @var \Giraffe\Helpers\Rights\Gatekeeper
     */
    protected $gatekeeper;

    public function __construct()
    {
        $this->gatekeeper = App::make('Giraffe\Helpers\Rights\Gatekeeper');
        $this->log = App::make('Giraffe\Helpers\Logger\Log');
    }
} 