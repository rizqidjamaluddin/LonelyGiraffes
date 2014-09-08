<?php

use Giraffe\Notifications\NotificationService;
use Giraffe\Notifications\SystemNotification\SystemNotificationModel;
use Giraffe\Users\UserRepository;
use Giraffe\Users\UserService;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class Notify extends Command {

	protected $name = 'lg:util:notify';

	protected $description = 'Send a system notification to a user.';

	public function __construct()
	{
		parent::__construct();
	}

	public function fire()
	{
        $body = $this->argument('body');
		$title = $this->argument('title');

        /** @var NotificationService $service */
        $service = \App::make(NotificationService::class);

        /** @var UserRepository $userRepository */
        $userRepository = \App::make(UserRepository::class);

        $user = $userRepository->getByHash($this->argument('hash'));

        $service->queue(SystemNotificationModel::make($body, $title), $user);

        return 0;
	}

	protected function getArguments()
	{
		return array(
            array('hash', InputArgument::REQUIRED, "Target user's hash."),
			array('body', InputArgument::REQUIRED, 'Notification text to send.'),
			array('title', InputArgument::OPTIONAL, 'Notification title to send'),
		);
	}

	protected function getOptions()
	{
		return array(
		);
	}

}
