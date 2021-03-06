<?php  namespace Giraffe\Authorization;

class GiraffePermissionsLookup
{

    public function getGroups()
    {
        return ['guest', 'member', 'mod', 'admin'];
    }

    public function getGroupPermissions()
    {
        $guest = [
            'self'   => [
                'feed' => ['read'],
            ],
            'global' => [
                'post'    => ['read'],
                'event'   => ['read'],
                'profile' => ['read'],
                'shout'   => ['read'],
            ]
        ];
        $member = array_merge_recursive(
            $guest,
            [
                'self'   => [
                    'buddy'                  => ['delete'],
                    'buddy_request'          => ['read', 'create', 'accept', 'delete'],
                    'chatroom'               => ['create', 'read', 'update', 'chat', 'kick'],
                    'chatroom_membership'    => ['delete'],
                    'comment'                => ['create', 'read', 'update'],
                    'event'                  => ['create', 'edit', 'update', 'delete'],
                    'image'                  => ['create', 'delete'],
                    'notification'        => ['read', 'delete', 'dismiss', 'dismiss_all'],
                    'post'                   => ['read_buddies', 'read_nearby', 'delete'],
                    'profile'                => ['create', 'update'],
                    'shout'                  => ['create', 'delete'],
                    'user'                   => ['update', 'deactivate', 'add_buddy', 'read_buddy', 'delete_buddy',
                                                'read_buddy_request', 'read_notifications', 'change-tutorial-flag'],
                    'test'                   => ['test'],

                ],
                'global' => [
                    'event' => ['comment', 'find_nearby', 'join'],
                    'post'  => ['comment'],
                    'shout' => ['comment']
                ]
            ]
        );
        $mod = array_merge_recursive(
            $member,
            [
                'self'   => [
                ],
                'global' => [
                    'post' => ['delete']
                ],
            ]
        );
        $admin = array_merge_recursive(
            $mod,
            [
                'self'   => [
                ],
                'global' => [
                    'user' => ['update', 'deactivate', 'delete'],
                ],
            ]
        );

        return [
            'guest'  => $guest,
            'member' => $member,
            'mod'    => $mod,
            'admin'  => $admin,
        ];
    }

} 