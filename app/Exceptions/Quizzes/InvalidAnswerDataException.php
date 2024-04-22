<?php

namespace App\Exceptions\Quizzes;

use Exception;
use Illuminate\Support\Facades\Lang;

class InvalidAnswerDataException extends Exception
{

    public function report()
    {
        //
    }
    public function render($request)
    {
        return response()->json(['errors' => Lang::get('iu.quiz.invalidAnswersData')], 422);
    }
}
