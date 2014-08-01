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
                'post'  => ['read'],
                'event' => ['read'],
                'profile' => ['read'],
            ]
        ];
        $member = array_merge_recursive(
            $guest,
            [
                'self'   => [
                    'buddy'                  => ['delete'],
                    'buddy_request'          => ['create', 'accept', 'delete'],
                    'event'                  => ['create', 'edit', 'update', 'delete'],
                    'notification_container' => ['read', 'delete', 'dismiss_all'],
                    'post'                   => ['delete'],
                    'profile'                => ['create', 'update'],
                    'shout'                  => ['create', 'delete'],
                    'user'                   => ['update', 'deactivate', 'add_buddy', 'read_buddy', 'delete_buddy',
                                                'read_buddy_request'],
                    'test'                   => ['test'],

                ],
                'global' => [
                    'event' => ['comment', 'find_nearby'],
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