<?php

namespace App\Transformers\AF;

use App\Models\Quiz;
use League\Fractal\TransformerAbstract;

class AfQuizTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform(Quiz $quiz)
    {
        return [
            'id' => $quiz->id,
            'entity_id' => $quiz->entity_id,
            'entity_type' => $quiz->entity_type,
            'price' => $quiz->price,
            'duration' => $quiz->duration,
            'num_of_questions' => $quiz->num_of_questions,
            'created_at' => $quiz->created_at,
            'updated_at' => $quiz->updated_at,
        ];
    }
}
