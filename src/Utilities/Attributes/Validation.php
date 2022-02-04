<?php

namespace Sigil\Utilities\Attributes;

use Exedra\Routeller\Attributes\State;

#[\Attribute]
class Validation extends State
{
    public function __construct(array $rules)
    {
        parent::__construct(static::class, $rules);
    }
}
