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
            ],
            'global' => [
                'post' => ['read'],
                'event' => ['read'],
            ]
        ];
        $member = array_merge_recursive(
            $guest,
            [
                'self'   => [
                    'event' => ['create', 'edit', 'update', 'delete'],
                    'notification_container' => ['read', 'delete', 'dismiss_all'],
                    'post'                   => ['delete'],
                    'shout'                  => ['post', 'delete'],
                    'user'                   => ['update', 'deactivate'],
                    'test'                   => ['test'],

                ],
                'global' => [
                    'event' => ['comment'],
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