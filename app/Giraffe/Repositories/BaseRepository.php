<?php  namespace Giraffe\Repositories; 

use Giraffe\Exceptions\InvalidCreationException;
use Giraffe\Exceptions\NotFoundModelException;
use stdClass;

interface BaseRepository
{
    /**
     * @param int|string|stdClass $idOrHash
     * @throws NotFoundModelException
     * @return stdClass
     */
    public function get($idOrHash);

    /**
     * @param int $id
     * @throws NotFoundModelException
     * @return stdClass
     */
    public function getById($id);

    /**
     * @param string $hash
     * @throws NotFoundModelException
     * @return stdClass
     */
    public function getByHash($hash);

    /**
     * @param array $attributes
     * @throws InvalidCreationException
     * @return stdClass
     */
    public function create(array $attributes);

    /**
     * @param int $id
     * @throws NotFoundModelException
     * @return mixed
     */
    public function deleteById($id);

    /**
     * @param string $hash
     * @throws NotFoundModelException
     * @return mixed
     */
    public function deleteByHash($hash);

    /**
     * @param array $ids
     * @return int
     */
    public function deleteMany(array $ids);
} 