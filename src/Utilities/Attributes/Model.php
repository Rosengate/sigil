<?php

namespace Sigil\Utilities\Attributes;

use Exedra\Routeller\Attributes\State;
use Exedra\Routeller\Contracts\RouteAttribute;

#[\Attribute]
class Model extends State
{
    public function __construct($model, $param, $field = 'id')
    {
        parent::__construct(Model::class, [
            'class' => $model,
            'param' => $param,
            'field' => $field
        ]);
    }
}
