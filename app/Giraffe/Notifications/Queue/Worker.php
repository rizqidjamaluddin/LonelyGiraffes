<?php  namespace Giraffe\Notifications\Queue;
use DB;
use Giraffe\Users\UserModel;
use Giraffe\Users\UserRepository;

class Worker
{
    /**
     * @var UserRepository
     */
    protected $userRepository;

    protected function init()
    {
        $this->userRepository = \App::make(UserRepository::class);
    }

    /**
     * Grab a user that needs notifying from the queue.
     *
     * By using a dibs tag, we ensure atomicity; no two workers can compete on a task.
     * Each user should only have one row on this table.
     */
    protected function acquireNotifyingTask()
    {
        $this->init();

        $dibs = \Str::quickRandom(16);

        DB::table('notification_queue')
            ->whereNull('dibs')
            ->where('waiting', '1')
            ->where('next_issue', '<', time())
            ->limit(1)
            ->update(['dibs', $dibs]);

        // grab this job
        DB::beginTransaction();
        $task = DB::table('notification_queue')->where('dibs', $dibs)->lockForUpdate()->first();

        /** @var UserModel $user */
        $user = $this->userRepository->get($task['user_id']);


        // fetch notifications to be sent

        // compile notifications

        // mail out

        // update window

        // release dibs

    }

} 