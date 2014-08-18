<?php  namespace Giraffe\Common;

/**
 * Class HashHashEloquent
 *
 * Support use of a 'hash' column in eloquent to fetch rows.
 * Column should be a
 *
 * @package Giraffe\Contracts
 */
trait HasEloquentHash
{
    public $hasHash = true;

    /**
     * Find object by hash. Throw ModelNotFoundException if not existing.
     *
     * @param string $hash
     *  mixed
     */
    public function findHash($hash)
    {
        return $this->where('hash', '=', $hash)->firstOrFail();
    }

    public function deleteByHash($hash)
    {
        return $this->where('hash', '=', $hash)->delete();
    }

    public function setHashAttribute($value)
    {
        $this->attributes['hash'] = (string) $value;
    }

}