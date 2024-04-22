<?php

namespace App\Traits\Tests;

trait JSONResponseTestTrait {
    public function LessonAvailabilityTest($array) {

        $course_modules_length = count($array->course_level->course_modules);

        for ($x = 0; $x < $course_modules_length; $x++) {
            $lessons_length = count($array->course_level->course_modules[$x]->lessons);

            for ($y = 0; $y < $lessons_length; $y++) {
                if($array->course_level->course_modules[$x]->lessons[$y]->progress == 100 || $y==0){
                    if ($array->course_level->course_modules[$x]->lessons[$y]->available == 1){
                        continue;
                    } else {
                        return false;
                    }
                }
                if($array->course_level->course_modules[$x]->lessons[$y]->available == 1){
                    if($array->course_level->course_modules[$x]->lessons[$y-1]->progress == 100){
                        continue;
                    }
                    else {
                        return false;
                    }
                }
            }
          }
        return true;
    }

    public function LessonAvailabilityLevelTest($array) {

        $course_modules_length = count($array->course_modules);

        for ($x = 0; $x < $course_modules_length; $x++) {
            $lessons_length = count($array->course_modules[$x]->lessons);

            for ($y = 0; $y < $lessons_length; $y++) {
                if($array->course_modules[$x]->lessons[$y]->progress == 100 || $y==0){
                    if ($array->course_modules[$x]->lessons[$y]->available == 1){
                        continue;
                    } else {
                        return false;
                    }
                }
                if($array->course_modules[$x]->lessons[$y]->available == 1){
                    if($array->course_modules[$x]->lessons[$y-1]->progress == 100){
                        continue;
                    }
                    else {
                        return false;
                    }
                }
            }
          }
        return true;
    }

    public function LessonAvailabilityLevelAllFalseTest($array) {

        $course_modules_length = count($array->course_modules);

        for ($x = 0; $x < $course_modules_length; $x++) {
            $lessons_length = count($array->course_modules[$x]->lessons);

            for ($y = 0; $y < $lessons_length; $y++) {
                if($array->course_modules[$x]->lessons[$y]->available == 1){
                    return false;
                }
            }
          }
        return true;
    }

    public function CourseLevelNotFinishedTest($array) {

        $course_modules_length = count($array->course_modules);

        for ($x = 0; $x < $course_modules_length; $x++) {
            $lessons_length = count($array->course_modules[$x]->lessons);

            for ($y = 0; $y < $lessons_length; $y++) {
                if($array->course_modules[$x]->lessons[$y]->progress != 100){
                    return true;
                }
            }
          }
        return false;
    }
}
