<?php

namespace App\Transformers;

use League\Fractal\Serializer\ArraySerializer;

class Serializer extends ArraySerializer
{
    public function collection(?string $resourceKey, array $data): array
    {
        return $resourceKey ? [$resourceKey] : $data;
    }

//    /**
//     * Serialize a collection.
//     *
//     * @param string $resourceKey
//     * @param array  $data
//     *
//     * @return array
//     */
//    public function collection(?string $resourceKey, array $data)
//    {
//        return $resourceKey ? [$resourceKey] : $data;
//    }
}
