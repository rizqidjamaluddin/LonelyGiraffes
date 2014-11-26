<?php

use Giraffe\Authorization\Gatekeeper;
use Giraffe\Common\NotFoundModelException;
use Giraffe\Users\UserService;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class Promote extends Command
{
    const LOG_STREAM = 'LG-Util';

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'lgutil:promote';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Promote/demote a user to a particular role in the user system.";

    /**
     * @var Giraffe\Logging\Log
     */
    protected $log;
    /**
     * @var
     */
    private $gatekeeper;

    /**
     * @var Giraffe\Users\UserService
     */
    private $userService;

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {

        $this->gatekeeper = App::make('Giraffe\Authorization\Gatekeeper');
        /** @var UserService userService */
        $this->userService = App::make('Giraffe\Users\UserService');
        $this->log = App::make('Giraffe\Logging\Log');

        $target = $this->argument('email');
        $role = $this->argument('role');
        $force = $this->option('force');

        $this->log->notice(self::LOG_STREAM, 'promote invoked', ['target' => $target, 'role' => $role]);

        try {
            $user = $this->userService->getUserByEmail($target);
        } catch (NotFoundModelException $e) {
            $this->error("User not found.");
            $this->info('Operation complete.');
            $this->log->notice(self::LOG_STREAM, 'promote failed - user not found');
            return;
        }

        // confirmation
        $this->table(
             ['', ''],
             [
                 ['id', $user->id],
                 ['hash', $user->hash],
                 ['name', $user->name],
                 ['email', $user->email],
                 ['current role', $user->role]
             ]
        );
        $this->info("Will change user role to $role.");
        if (!$force) {
            $confirm = $this->confirm('Proceed with operation?', false);

            if (!$confirm) {
                $this->info('Operation canceled.');
                $this->log->info('promote canceled');
                return;
            }
        }

        $this->gatekeeper->sudo();

        // execute
        switch ($role) {
            case ('member') :
            {
                $this->userService->demoteToMember($user);
                $this->info("User {$user->email} demoted to member level.");
                break;
            }
            case ('admin')  :
            {
                $this->userService->promoteToAdmin($user);
                $this->info("User {$user->email} promoted to administrator level.");
                break;
            }
            default :
                {
                $this->error('Role not valid; use the -h flag for help.');
                $this->log->notice(self::LOG_STREAM, "promote failed - role $role not valid");
                }
        }

        $this->info('Operation complete.');

    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return array(
            array('email', InputArgument::REQUIRED, "A user's email."),
            array('role', InputArgument::OPTIONAL, "The desired role for this user: member, admin.", 'admin'),
        );
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['force', 'f', InputOption::VALUE_NONE, "Skip user account display and confirmation."]
        ];
    }

}
