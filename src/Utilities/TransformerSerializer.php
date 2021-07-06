<?php

namespace Sigil\Utilities;

use League\Fractal\Serializer\ArraySerializer;

class TransformerSerializer extends ArraySerializer
{
    /**
     * Serialize a collection.
     *
     * @param string $resourceKey
     * @param array  $data
     *
     * @return array
     */
    public function collection($resourceKey, array $data)
    {
        return $resourceKey ? [$resourceKey] : $data;
    }
}
