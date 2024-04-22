<?php

namespace App\Transformers\AF;

use App\Models\Product as Book;
use League\Fractal\TransformerAbstract;

class AfCourseModuleBookTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform(Book $book)
    {
        return [
            'id' => $book->id,
            'name' => $book->name,
            'create_at' => $book->created_at,
            'updated_at' => $book->updated_at,
        ];
    }
}
