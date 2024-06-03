<?php

namespace App\Transformers\IU\Cart;

use App\Models\CourseModule;
use League\Fractal\TransformerAbstract;

class IuCartEbookModulesTransformer extends TransformerAbstract
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
