<?php  namespace Giraffe\Common;

abstract class Endpoint
{

    /**
     * URL root, relative to the project base URL, of this endpoint.
     * E.g. /api/users
     *
     * @var string
     */
    protected $root;

} 