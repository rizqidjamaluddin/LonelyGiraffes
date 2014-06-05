<?php  namespace Giraffe\Authorization; 

interface ProtectedResource
{
    /**
     * @return string
     */
    public function getResourceName();

} 