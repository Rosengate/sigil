<?php

namespace Sigil\Utilities\Attributes;

use Exedra\Routeller\Attributes\State;
use Exedra\Routeller\Contracts\RouteAttribute;

#[\Attribute]
class Model extends State
{
    public function __construct($model, $param)
    {
        parent::__construct(Model::class, [
            'class' => $model,
            'field' => $param
        ]);
    }
}
