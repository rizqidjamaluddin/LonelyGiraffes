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
            'self' => [
            ],
            'global' => [
                'post' => ['read']
            ]
        ];
        $member = array_merge_recursive(
            $guest,
            [
                'self' => [
                    'post' => ['delete'],
                ],
                'global' => [
                    'post' => ['comment']
                ]
            ]
        );
        $mod = array_merge_recursive(
            $member,
            [
                'self' => [
                ],
                'global' => [
                    'post' => ['delete']
                ],
            ]
        );
        $admin = array_merge_recursive(
            $mod,
            [
                'self' => [
                ],
                'global' => [
                ],
            ]
        );

        return [
            'guest' => $guest,
            'member' => $member,
            'mod' => $mod,
            'admin' => $admin,
        ];
    }

} 