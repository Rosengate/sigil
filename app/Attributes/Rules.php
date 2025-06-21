<?php

namespace App\Attributes;

use Exedra\Routeller\Attributes\State;

#[\Attribute]
class Rules extends State
{
    public function __construct($rules)
    {
        return parent::__construct(static::class, $rules);
    }
}
