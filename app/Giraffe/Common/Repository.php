<?php  namespace Giraffe\Common;

use Giraffe\Common\InvalidCreationException;
use Giraffe\Common\NotFoundModelException;
use stdClass;

interface Repository
{
    /**
     * @param int|string|stdClass $idOrHash
     *
*@throws \Giraffe\Common\NotFoundModelException
     * @return stdClass
     */
    public function get($idOrHash);

    /**
     * @param int $id
     *
*@throws \Giraffe\Common\NotFoundModelException
     * @return stdClass
     */
    public function getById($id);

    /**
     * @param string $hash
     *
*@throws \Giraffe\Common\NotFoundModelException
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
     *
* @throws \Giraffe\Common\NotFoundModelException
     * @return mixed
     */
    public function deleteByHash($hash);

    /**
     * @param array $ids
     * @return int
     */
    public function deleteMany(array $ids);
} 