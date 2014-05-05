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
 * Important: Gatekeeper DOES NOT do authentication. It does not check to make sure the user is who they claim to be.
 * It's meant for access control and rights management.
 *
 * @package Giraffe\Helpers\Rights
 */
class Gatekeeper
{

    const REQUEST_NOT_SET    = 0;
    const REQUEST_PERMISSION = 1;


    /**
     * @var array
     */
    protected $query = [];

    protected $request = null;

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