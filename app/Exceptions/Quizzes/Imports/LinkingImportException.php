<?php

namespace App\Exceptions\Quizzes\Imports;

class LinkingImportException extends AbstractQuestionException
{

    public function report()
    {
        //
    }
    public function render($request)
    {
        //
    }

    public function handle()
    {
        //
    }

    public function getRow(): int|string|null
    {
        return $this->row . '-' . $this->row+1;
    }
}
