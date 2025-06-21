<?php

namespace App\Attributes;

use Exedra\Routeller\Attributes\State;

#[\Attribute]
class TransformItem extends State
{
    public function __construct($transformer)
    {
        return parent::__construct(static::class, $transformer);;
    }
}
