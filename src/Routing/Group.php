<?php

namespace Sigil\Routing;

class Group extends \Exedra\Routing\Group
{
    protected $routellerClass;

    public function setController($class)
    {
        $this->routellerClass = $class;
    }

    public function getController()
    {
        return $this->routellerClass;
    }
}
