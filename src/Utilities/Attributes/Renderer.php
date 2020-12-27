<?php

namespace Sigil\Utilities\Attributes;

use Exedra\Routeller\Attributes\Series;

#[\Attribute]
class Renderer extends Series
{
    /**
     * Renderer constructor.
     * @param string $class a class name that extends Sigil\Contracts\Renderer
     */
    public function __construct(string $class)
    {
        parent::__construct(static::class, $class);
    }
}
