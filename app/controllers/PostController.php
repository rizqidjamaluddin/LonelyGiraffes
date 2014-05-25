<?php

use Giraffe\Common\Controller;
use Giraffe\Common\NotImplementedException;

class PostController extends Controller
{

    public function index()
    {
        $this->gatekeeper->iAm($this->auth->getUser());
        throw new NotImplementedException;
    }

    public function addComment($post)
    {
        throw new NotImplementedException;
    }
} 