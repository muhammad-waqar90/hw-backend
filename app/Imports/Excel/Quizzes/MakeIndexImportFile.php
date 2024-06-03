<?php

namespace App\Imports\Excel\Quizzes;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class MakeIndexImportFile implements FromArray, WithHeadings
{
    use Exportable;

    private $lessonId;

    private $moduleId;

    private $levelId;

    private $questions;

    private $noOfQuestions;

    private $duration;

    private $price;

    public function __construct(
        $lessonId,
        $moduleId,
        $levelId,
        $fileName,
        int $sampleSize,
        int $duration,
        $price
    ) {
        $this->lessonId = $lessonId;
        $this->moduleId = $moduleId;
        $this->levelId = $levelId;
        $this->questions = $fileName;
        $this->noOfQuestions = $sampleSize;
        $this->duration = $duration;
        $this->price = $price;
    }

    public function array(): array
    {
        return [
            [
                $this->lessonId,
                $this->moduleId,
                $this->levelId,
                $this->questions,
                $this->noOfQuestions,
                $this->duration,
                $this->price,
            ],
        ];
    }

    public function headings(): array
    {
        return [
            'lesson_id',
            'module_id',
            'level_id',
            'questions',
            'number_of_questions',
            'duration',
            'price',
        ];
    }
}
