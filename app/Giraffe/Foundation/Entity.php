<?php  namespace Giraffe\Foundation;

use App;
use Giraffe\Exceptions\DirtyEntityException;
use Giraffe\Exceptions\InvalidEntityPropertyException;
use Serializable;

/**
 * Class Entity
 *
 * Base domain-level entity.
 *
 * Properties can be accessed normally; setting values should use custom methods built on entity implementations.
 * Setters are NOT automatically available. Please invoke ->setProperty() in the setters.
 * $entity = $userRepository->find($id);
 * echo 'Old username: ' . $entity->username; 
 * $entity->setUsername($new_username);         // implement this
 * $userRepository->save($entity);
 *
 * Related entities are registered in the a field, in property => RepositoryClass form:
 * protected $relationEntities = ['comments' => 'CommentRepository'];
 *
 * Related property names should not conflict with field names; if they do, related entities should be prioritized.
 *
 * No magic handling for aggregates is provided. Load them like normal entities on your repository implementation.
 *
 * Need validation? Write a validator class and write your own isValid method or whatever!
 *
 * @property $id int
 * @package Giraffe\Foundation
 */
abstract class Entity implements Serializable
{
    /**
     * List of property names. This differs from the properties array in that all fields are, by default, optional,
     * so it's possible that an entity's property for a particular field will simply be null.
     * @var array
     */
    protected $fields = [];

    /**
     * Main storage of entity properties.
     * @var array
     */
    protected $properties = [];

    /**
     * @var array
     */
    protected $dirty = [];

    /**
     * @var array
     */
    protected $relationEntities = [];

    /**
     * @var array
     */
    protected $relations = [];

    public function __construct()
    {
    }

    /**
     * Set a property on this entity, marking it as dirty if it changed. Call this from custom "setSomething()"
     * methods.
     *
     * @param $property
     * @param $value
     *
     * @throws \Giraffe\Exceptions\InvalidEntityPropertyException
     * @return $this
     */
    protected function setProperty($property, $value)
    {
        if (!array_key_exists($property, $this->fields)) {
            throw new InvalidEntityPropertyException;
        }

        // don't mark as dirty if property did not change
        if ($this->properties[$property] == $value) {
            return $this;
        }

        $this->properties[$property] = $value;
        $this->dirty[$property] = true;

        return $this;
    }

    public function getProperties()
    {
        return $this->properties;
    }

    public function getRelationEntities()
    {
        $collection = [];
        foreach ($this->relations as $aggregate) {
            foreach ($aggregate as $entity) {
                $collection[] = $entity;
            }
        }
        return $collection;
    }

    public function isDirty()
    {
        foreach ($this->dirty as $dirty_field) {
            if ($dirty_field) return true;
        }
        return false;
    }

    public function getName()
    {
        return get_class($this);
    }

    function __get($property)
    {
        // fetch any aggregates lazily
        if (array_key_exists($property, $this->relationEntities)) {
            if (array_key_exists($property, $this->relations)) {
                // collection already exists, use it
                return $this->relations[$property];
            } else {
                // load collection from repository
                $relatedRepository = App::make($this->relationEntities[$property]);
                $this->relations[$property] = $relatedRepository->findFor($this);
                return $this->relations[$property];
            }
        }

        if (!array_key_exists($property, $this->fields)) {
            throw new InvalidEntityPropertyException;
        }

        if (array_key_exists($property, $this->properties)) {
            return $this->properties[$property];
        }

        // null indicates an existing, but unset, property
        return null;
    }

    /**
     * Recursively serialize all properties.
     * Only properties (including aggregates) are serialized, not related classes. This prevents a cached entity from
     * preserving a stale collection of other entities, which could go out of sync with different copies of themselves.
     *
     * @throws \Giraffe\Exceptions\DirtyEntityException
     * @return string|void
     */
    public function serialize()
    {
        if ($this->isDirty()) {
            throw new DirtyEntityException;
        }

        $serializable = [];

        foreach ($this->properties as $property => $value) {
            $serializable[$property] = serialize($value);
        }

        return serialize($serializable);

    }


    public function unserialize($serialized)
    {
        $serialized = unserialize($serialized);

        foreach ($this->fields as $field) {
            $this->properties[$field] = array_key_exists($field, $serialized) ? $serialized[$field] : null;
        }
    }
}