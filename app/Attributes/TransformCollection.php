<?php

namespace App\Attributes;

use Exedra\Routeller\Attributes\State;

#[\Attribute]
class TransformCollection extends State
{
    public function __construct($transformer)
    {
        return parent::__construct(static::class, $transformer);;
    }
}
