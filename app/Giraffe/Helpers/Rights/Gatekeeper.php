<?php  namespace Giraffe\Helpers\Rights;

/**
 * Class Gatekeeper
 *
 * Basic use:
 * $gatekeeper->iAm($user)->andMayI('edit', 'posts')->please();
 *
 * Stateful:
 * $gatekeeper->iAm($user);
 * $gatekeeper->mayI('edit', 'posts');
 *
 * Planned (assuming user already set using iAm()):
 * $gatekeeper->put($mod_to_be)->into('moderators')->please();
 * $gatekeeper->remove($inactive_person)->from('moderators')->please();
 * $gatekeeper->allow($some_dude)->to('edit', 'posts')->please();
 * $gatekeeper->ban($poor_guy)->from('edit', 'posts')->please();
 *
 * In the future:
 * $gatekeeper->ban($poor_guy)->from('edit', 'posts')->for('1 month')->and()->removeThem()->from('moderators')->please();
 *
 * Nouns may be plural or singular.
 *
 * @package Giraffe\Helpers\Rights
 */
class Gatekeeper
{

    /**
     * @var array
     */
    protected $query = [];

    protected $request = null;

    protected $authenticated = false;
    protected $authentication = [];

    public function __construct()
    {

    }

    public function iAm($userIdentifier)
    {

    }




    public function mayI($verb, $noun)
    {
        
    }

    public function andMayI($verb, $noun)
    {

    }

    /**
     * Execute command and clear the query details.
     */
    public function please()
    {

    }
    
}