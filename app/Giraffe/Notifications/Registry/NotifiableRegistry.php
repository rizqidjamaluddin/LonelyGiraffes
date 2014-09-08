<?php  namespace Giraffe\Notifications\Registry;

use Giraffe\Common\Repository;

class NotifiableRegistry
{

    protected $registry = [];

    /**
     * Register new types of notifications with the registry.
     *
     * @param string     $notifiable Class name of notifiable class (use Foo::class syntax)
     * @param Repository $repository Repository corresponding with registered entity
     */
    public function register($notifiable, Repository $repository)
    {
        $this->registry[$notifiable::getType()] = $repository;
    }

    /**
     * @param $type
     * @return Repository
     */
    public function resolveRepository($type)
    {
        if (array_key_exists($type, $this->registry)) {
            return $this->registry[$type];
        } else {
            throw new UndefinedNotificationException;
        }
    }
} 