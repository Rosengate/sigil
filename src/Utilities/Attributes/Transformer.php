<?php

namespace Sigil\Utilities\Attributes;

use Exedra\Routeller\Attributes\State;

#[\Attribute]
class Transformer extends State
{
    public function __construct($class)
    {
        parent::__construct(static::class, $class);
    }
}
