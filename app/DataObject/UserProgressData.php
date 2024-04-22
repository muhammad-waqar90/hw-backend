<?php


namespace App\DataObject;


class UserProgressData
{
    const ENTITY_LESSON = 'lesson';
    const ENTITY_COURSE_MODULE = 'course_module';
    const ENTITY_COURSE_LEVEL = 'course_level';
    const ENTITY_COURSE = 'course';

    const MODIFIER_FOR_ENTITY_WITH_QUIZ = 0.8;
    const MIN_PROGRESS_TO_ACCESS_QUIZ = 80;
    const COMPLETED_PROGRESS = 100;
}
