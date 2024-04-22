<?php

namespace App\Transformers\AF;

use App\Models\Ebook;
use League\Fractal\TransformerAbstract;

class AfLessonEbookTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform(Ebook $ebook)
    {
        return [
            'id'            => $ebook->id,
            'lesson_id'     => $ebook->lesson_id,
            'name'          => $ebook->content,
            'src'           => $ebook->src,
            'created_at'    => $ebook->created_at,
            'updated_at'    => $ebook->updated_at
        ];
    }
}
