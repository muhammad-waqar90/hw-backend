<?php

namespace App\Transformers\IU\Quiz;

use App\Models\Quiz;
use League\Fractal\TransformerAbstract;

class IuQuizPreviewTransformer extends TransformerAbstract
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
            'duration' => $quiz->duration,
        ];
    }
}
