<?php

namespace App\Traits\Tests;

use App\Models\UserProgress;

use App\DataObject\UserProgressData;


trait FinishedLessonTrait {
    public function finishLessonReadyToAccessQuiz($data, $user) {
        UserProgress::factory()->entityLessonWithId($this->data->lesson->id)->withUserId($this->user->id)->withProgress(UserProgressData::MIN_PROGRESS_TO_ACCESS_QUIZ)->create();

        // 80 (min_progress_to_access_quiz) / 2 (lessons) / 80%
        $progress = ((80/2) / 100) * 80;
        UserProgress::factory()->entityCourseModuleWithId($this->data->courseModule->id)->withUserId($this->user->id)->withProgress($progress)->create();

        // 80 (min_progress_to_access_quiz) / 3 (course_modules)
        $progress = 80 / 3;
        UserProgress::factory()->entityCourseLevelWithId($this->data->courseLevel->id)->withUserId($this->user->id)->withProgress($progress)->create();

        // ???
        $progress = 9;
        UserProgress::factory()->entityCourseWithId($this->data->course->id)->withUserId($this->user->id)->withProgress($progress)->create();

    }
}
