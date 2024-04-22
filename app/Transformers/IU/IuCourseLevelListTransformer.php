<?php

namespace App\Transformers\IU;

use App\Models\CourseLevel;
use League\Fractal\TransformerAbstract;

class IuCourseLevelListTransformer extends TransformerAbstract
{    
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform(CourseLevel $courseLevel)
    {
        return [
            'id'    => $courseLevel->id,
            'name'  => $courseLevel->name,
            'value' => $courseLevel->value,
        ];
    }
}
