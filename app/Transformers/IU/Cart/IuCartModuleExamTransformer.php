<?php

namespace App\Transformers\IU\Cart;

use App\Models\CourseModule;
use League\Fractal\TransformerAbstract;

class IuCartModuleExamTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @param CourseModule $courseModule
     * @return array
     */
    public function transform(CourseModule $courseModule)
    {
        return [
            'id'    => $courseModule->quizId,
            'course_module_id' => $courseModule->courseModuleId,
            'name'  => $courseModule->name,
            'price' => $courseModule->price,
            'disabled' => !!$courseModule->purchased
        ];
    }
}
