<?php  namespace Giraffe\Notifications\Queue;

use DB;
use Giraffe\Notifications\Notification;
use Giraffe\Users\UserModel;

class QueuePusher
{

    public function push(Notification $notification)
    {

    }

    protected function raiseUserWaitingFlag(UserModel $userModel)
    {
        DB::beginTransaction();
        // check for an existing row for this user first
        // this will wait for the worker transaction if one is in progress
        $task = DB::table('notification_queue')->where('user_id', $userModel->id)->lockForUpdate()->first();

        // if there are no waiting notifications, bump the next_issue; otherwise ignore it

        // raise waiting flag

        // add row if it doesn't exist
        if (!$task) {
            DB::table('notification_queue')->insert(
              [
                  [
                    'user_id' => $userModel->id
                    // 'next_issue' => $userModel->getNextNotificationIssue(),
                    // 'waiting' => 1
                  ]
              ]
            );
        }
    }

} 