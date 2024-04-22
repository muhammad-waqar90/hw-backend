<?php

namespace App\DataObject;

class QuizQuestionDifficultyData
{
    const EASY_STRING = 'easy';
    const DIFFICULT_STRING = 'difficult';

    const EASY_INT = 1;
    const DIFFICULT_INT = 2;

    static function getStringConstants () : array
    {
        return [self::EASY_STRING, self::DIFFICULT_STRING];
    }

    static function difficultyStringToInt(string $value): int
    {
       if($value === self::EASY_STRING)
           return self::EASY_INT;
       if($value === self::DIFFICULT_STRING)
           return self::DIFFICULT_INT;

        throw new \Exception('Difficulty string not found');
    }
}
