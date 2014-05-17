<?php  namespace Giraffe\Foundation;

/**
 * Class Repository
 *
 * Repositories serve as entity stores.
 * $entity = $userRepository->find($id);
 * $userRepository->save($entity);
 *
 * Repositories may implement extra methods as alternate search and save mechanisms.
 * $entity = $userRepository->search($keyword);
 *
 * For repositories related to other entities, implement a method on the child repository:
 * $postEntity = $postRepository->find($id);
 * $commentCollection = $commentRepository->findForPost($postEntity); // implement this
 * $commentCollection = $commentRepository->findFor($postEntity);     // provided shortcut to the above
 *
 * Entities will also internally use ->findFor when accessing related properties:
 * $commentCollection = $postEntity->comments;
 *
 * This is different in that this method always pulls in the whole collection, whereas custom repository methods
 * would allow for more complex behavior:
 * $newestComment = $commentRepository->findNewestForPost($postEntity);
 *
 * @package Giraffe\Foundation
 */
abstract class Repository
{

    /**
     * @var Entity
     */
    protected $entityClass;

    public function __construct(Entity $entityClass)
    {
        $this->entityClass = $entityClass;
    }

    /**
     * Find an entity based on an identifier.
     *
     * @param $identifier
     * @return mixed
     */
    abstract public function find($identifier);

    /**
     * Find one or more entities related to the given (external) entity.
     *
     * @param Entity $entity
     *
     * @return mixed
     * @throws \Exception
     */
    public function findFor(Entity $entity)
    {
        $entityClass = ucfirst($entity->getName());
        return call_user_func_array([$this, 'findFor' . $entityClass], [$entity]);
    }

    public function save($entities)
    {
        // save directly if it's not a collection
        if (is_a($entities, 'Giraffe\Foundation\Entity')) {
            $this->saveEntity($entities);
        }

        foreach ($entities as $entity) {
            $this->saveEntity($entity);
        }
    }

    /**
     * Save an entity. Remember to convert any specific data types into their persistence forms (e.g. from a Carbon
     * object to their SQL format).
     *
     * @param Entity $entity
     * @return mixed
     */
    abstract protected function saveEntity(Entity $entity);

    /**
     * Translate from persistence to entity.
     *
     * Translate the stored version of an entity into the actual types desired by the entity. Set this per actual
     * repository, because different persistence types may require different forms of translation. Conversion from
     * native data types to value objects can also take place here.
     *
     * By default, no translation happens.
     *
     * @param Array $attributes
     * @return array
     */
    protected function translateOut(Array $attributes)
    {
        return $attributes;
    }

    /**
     * Translate from entity to persistence.
     *
     * Inverse of translateOut.
     *
     * @param array $attributes
     *
*@return array
     */
    protected function translateIn(Array $attributes)
    {
        return $attributes;
    }
}