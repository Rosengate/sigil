<?php

namespace App\Attributes;

use Exedra\Routeller\Attributes\Series;

#[\Attribute]
class BindModel extends Series
{
    public function __construct($model, $routeParam, $field = 'id')
    {
        parent::__construct(static::class, [
            'class' => $model,
            'param' => $routeParam,
            'field' => $field
        ]);
    }
}
