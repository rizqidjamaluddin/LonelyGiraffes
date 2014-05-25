<?php  namespace Giraffe\Common;


use App;

class Controller extends \Controller
{
    /**
     * @var \Dingo\Api\Auth\Shield
     */
    protected $auth;
    /**
     * @var \Giraffe\Authorization\Gatekeeper
     */
    protected $gatekeeper;

    public function __construct()
    {
        $this->auth = App::make('Dingo\Api\Auth\Shield');
        $this->gatekeeper = App::make('Giraffe\Authorization\Gatekeeper');

        $this->gatekeeper->iAm($this->auth->user());
    }
} 