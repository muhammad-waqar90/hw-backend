<?php

namespace App\Transformers\IU\Course;

use App\Models\Ebook;
use League\Fractal\TransformerAbstract;

class IuEbookTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @param Ebook $ebook
     * @return array
     */
    public function transform(Ebook $ebook)
    {
        return [
            'id'    => $ebook->id,
            'name'  => $ebook->content,
            'src'   => $ebook->src,
        ];
    }
}
