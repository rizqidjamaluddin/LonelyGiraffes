<?php

class FeedServiceTest extends TestCase
{

    const POST_REPOSITORY = 'Giraffe\Repositories\PostRepository';
    const TEST = 'Giraffe\Services\FeedService';
    const SHOUT_REPOSITORY = 'Giraffe\Repositories\ShoutRepository';
    const USER_REPOSITORY = 'Giraffe\Repositories\UserRepository';

    public function disarm()
    {
        $gatekeeper = App::make('Giraffe\Helpers\Rights\Gatekeeper');
        $gatekeeper->disarm();
    }
    
}