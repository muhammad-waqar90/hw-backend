<?php

namespace App\Transformers\GU\Cart;

use App\Models\CourseModule;
use League\Fractal\TransformerAbstract;

class GuCartEbookModulesTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform(CourseModule $courseModule)
    {
        return [
            'id' => $courseModule->id,
            'name' => $courseModule->name,
            'price' => $courseModule->ebook_price,
            'disabled' => (bool) $courseModule->purchased,
        ];
    }
}
