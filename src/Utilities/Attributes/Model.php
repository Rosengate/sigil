<?php

namespace Ormi\Utilities\Attributes;

use Exedra\Routeller\Contracts\RouteAttribute;
use Exedra\Routeller\StateAttribute;

#[\Attribute]
class Model extends StateAttribute
{
    private $field;
    private $class;

    public function __construct($name, $class)
    {
        $this->field = $name;
        $this->class = $class;
    }

    public function value()
    {
        return [
            'field' => $this->field,
            'class' => $this->class
        ];
    }
}
