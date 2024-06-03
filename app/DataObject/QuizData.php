<?php

namespace App\DataObject;

class QuizData
{
    const ENTITY_LESSON = 'lesson';

    const ENTITY_COURSE_MODULE = 'course_module';

    const ENTITY_COURSE_LEVEL = 'course_level';

    const ENTITY_COURSE = 'course';

    const QUESTION_MCQ_SINGLE = 'mcqSingle';

    const QUESTION_MCQ_MULTIPLE = 'mcqMultiple';

    const QUESTION_MISSING_WORD = 'missingWord';

    const QUESTION_LINKING = 'linking';

    const STATUS_IN_PROGRESS = 'in_progress';

    const STATUS_SUBMITTED = 'submitted';

    const STATUS_COMPLETED = 'completed';

    const DEFAULT_PASSING_SCORE = 65;

    const QUIZ_EXAM_ALLOWED_ATTEMPTS = 2;
}
