<?php
use Giraffe\Shouts\ShoutRepository;
use Giraffe\Shouts\ShoutService;
use Giraffe\Users\UserRepository;
use Illuminate\Console\Command;

class MigrateEvents extends Command
{

    protected $name = 'lg:migrate:events';
    protected $description = 'Migrate v1 events to new database';

    public function __construct()
    {
        parent::__construct();
    }

    public function fire()
    {
        $this->confirm("Migrating events from version 1. Continue?");
        $oldUserLookup = (new \Illuminate\Support\Collection(DB::connection('v1_mysql')->table('users')->get(['id', 'email'])))->keyBy('id');
        $events = DB::connection('v1_mysql')->table('events')->get(['id', 'text', 'user_id', 'created_at']);

        /** @var \Giraffe\Authorization\Gatekeeper $gatekeeper */
        $gatekeeper = \App::make(\Giraffe\Authorization\Gatekeeper::class);
        $gatekeeper->sudo();

        /** @var UserRepository $userRepository */
        $userRepository = \App::make(UserRepository::class);

        /** @var ShoutService $shoutService */
        $shoutService = \App::make(ShoutService::class);

        /** @var ShoutRepository $shoutRepository */
        $shoutRepository = \App::make(ShoutRepository::class);

        $inc = 0; $fails = 0;
        foreach ($events as $event) {
            $inc++;
            try {
                $user =  $userRepository->getByEmail($oldUserLookup[$event['user_id']]['email']);
                $shout = $shoutService->createShout($user, $event['text']);
                $shout->timestamps = false;
                $shout->created_at = $event['created_at'];
                $shoutRepository->save($shout);

                $this->info('Created event for '.$user->email. ': ' . Str::limit($event['text']));
            } catch (Exception $e) {
                $fails++;
                $this->error('Unable to create event (#'.$event['id'].'): ' . $e->getMessage());
            }

        }
        $this->info("Import complete: $inc events ($fails fails).");

    }

    protected function getArguments()
    {
        return array();
    }

    protected function getOptions()
    {
        return array();
    }
} 
