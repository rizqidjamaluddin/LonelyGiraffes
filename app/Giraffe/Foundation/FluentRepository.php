<?php  namespace Giraffe\Foundation;

use DB;
use Giraffe\Exceptions\NotFoundEntityException;

/**
 * Class FluentRepository
 *
 * Base repository for Laravel's Query Builder.
 *
 * Includes a shortcut for retrieving related queries (parent_id refers to the column name):
 * $this->fetchParent($entity, 'parent_id');            // parent_id on the entity's field, using id field on this repo
 * $this->fetchChild($entity, 'parent_id')              // parent_id on this repo's table, using id field on entity
 * $this->fetchChildren($entity, 'parent_id)            // plural version of fetchChild (1-1 vs 1-n)
 * $this->fetchMorphingChildren($entity, 'parent_id');
 *
 * @package Giraffe\Foundation
 */
abstract class FluentRepository extends Repository
{


    /**
     * @var string
     */
    protected $tableName;

    public function __construct(Entity $entity, $tableName)
    {
        parent::__construct($entity);
        $this->tableName = $tableName;
    }


    public function find($identifier, $column = 'id')
    {
        if (is_array($identifier)) {
            return DB::table($this->tableName)->whereIn($column, $identifier)->get();
        }

        // use more efficient ->first() if it's only searching for one item
        $result = DB::table($this->tableName)->where($column, $identifier)->first();
        if (!$result) {
            throw new NotFoundEntityException;
        }
        return new $this->entityClass($result);
    }

    protected function saveEntity(Entity $entity)
    {
        // pass on clean entities
        if (!$entity->isDirty()) return $entity;

        DB::table($this->tableName)->update($entity->getProperties());
        return $entity;
    }

    /*
     * -- Related class operations
     */

}