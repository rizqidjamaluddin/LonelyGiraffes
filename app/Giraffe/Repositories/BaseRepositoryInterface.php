<?php  namespace Giraffe\Repositories; 

use stdClass;

interface BaseRepositoryInterface
{
    public function getById($id);
    public function getByHash($hash);

    public function create(array $attributes);

    public function deleteById($id);
    public function deleteByHash($hash);
    public function deleteMany(array $ids);
} 