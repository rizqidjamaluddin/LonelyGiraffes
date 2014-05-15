<?php  namespace Giraffe\Foundation;

use App;
use Exception;
use Giraffe\Exceptions\DirtyEntityException;
use Giraffe\Exceptions\InvalidEntityPropertyException;

/**
 * Class Entity
 *
 * Base domain-level entity.
 *
 * Properties can be accessed normally; setting values should use custom methods built on entity implementations.
 * Setters are NOT automatically available.
 * $entity = $userRepository->find($id);
 * echo 'Old username: ' . $entity->username;
 * $entity->setUsername($new_username);
 * $userRepository->save($entity);
 *
 * Aggregates are registered in the aggregates field, in property => RepositoryClass form:
 * protected $relationEntities = ['comments' => 'CommentRepository'];
 *
 * Aggregate names should not conflict with field names; if they do, aggregates should be prioritized.
 *
 *@package Giraffe\Foundation
 */
abstract class Entity
{
    /**
     * @var string
     */
    protected $className = null;

    /**
     * @var array
     */
    protected $fields = [];

    /**
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

    public function __construct(Array $properties)
    {
        // fill in properties
        foreach ($this->fields as $field) {
            $this->properties[$field] = array_key_exists($field, $properties) ? $properties[$field] : null;
        }
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
        if (is_null($this->className)) throw new Exception('Entity name not given');
        return $this->className;
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

    function __sleep()
    {
        // dirty entities cannot be serialized; save before serializing
        if ($this->isDirty()) throw new DirtyEntityException;
    }

    function __wakeup()
    {
        // TODO: Implement __wakeup() method.
    }

} 