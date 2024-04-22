<?php

namespace App\Repositories\AF;

use App\Models\Lesson;
use Batch;

class AfLessonRepository
{
    private Lesson $lesson;

    public function __construct(Lesson $lesson)
    {
        $this->lesson = $lesson;
    }

    public function createLesson(
        $courseId,
        $moduleId,
        $orderId, // listing order of lessons
        $name,
        $thumbnail,
        $description,
        $video,
        $published
    ) {
        return $this->lesson->create([
            'course_id'         => $courseId,
            'course_module_id'  => $moduleId,
            'order_id'          => $orderId,
            'name'              => $name,
            'img'               => $thumbnail,
            'description'       => $description,
            'video'             => $video,
            'published'         => $published,
            'content'           => ''
        ]);
    }

    public function getLesson($courseId, $moduleId, $lessonId)
    {
        return $this->lesson
            ->where('id', $lessonId)
            ->where('course_id', $courseId)
            ->where('course_module_id', $moduleId)
            ->first();
    }

    public function updateLesson(
        $id,
        $orderId, // listing order of lessons
        $name,
        $thumbnail,
        $description,
        $video,
        $published
    ) {
        return $this->lesson
            ->where('id', $id)
            ->update([
                'order_id'      => $orderId,
                'name'          => $name,
                'img'           => $thumbnail,
                'description'   => $description,
                'video'         => $video,
                'published'     => $published,
            ]);
    }

    public function checkIfAllLessonsExist($courseId, $moduleId, $lessonIds)
    {
        $lessons = $this->lesson
            ->whereIn('id', $lessonIds)
            ->where('course_id', $courseId)
            ->where('course_module_id', $moduleId)
            ->count();

        return $lessons == count($lessonIds);
    }

    public function deleteLesson($ids)
    {
        return $this->lesson->whereIn('id', $ids)->delete();
    }

    public function sortLesson($lessons)
    {
        return Batch::update(new Lesson, $lessons, 'id');
    }

    public static function getThumbnailS3StoragePath()
    {
        return 'courses/modules/lessons/thumbnails/';
    }

    public function getLessonById($lessonId, $detail = false)
    {
        return $this->lesson
            ->where('id', $lessonId)
            ->when($detail, function ($q) {
                $q->with('courseModule', function ($q) {
                    $q->with('courseLevel', function ($q) {
                        $q->with('course');
                    });
                });
            })
            ->first();
    }

    public function getAllLessonByModuleId($moduleId)
    {
        return $this->lesson
            ->where('course_module_id', $moduleId)
            ->get();
    }

    public function publishLessons($lessonIds)
    {
        return $this->lesson
            ->whereIn('id', $lessonIds)
            ->update([
                'published' => 1
            ]);
    }

    public function getLastLessonByModuleId($moduleId)
    {
        return $this->lesson
            ->where('course_module_id', $moduleId)
            ->orderBy('order_id', 'DESC')
            ->first();
    }

    public function getLessonsHaveNoQuiz($courseId)
    {
        return $this->lesson
            ->select('id', 'name', 'course_module_id')
            ->where('course_id', $courseId)
            ->doesnthave('quiz')
            ->with('courseModule', function ($query) {
                $query->addSelect('id', 'name');
            })
            ->orderBy('course_module_id', 'ASC')
            ->orderBy('id', 'ASC')
            ->get();
    }

    public function checkIfLessonUpdateValid($moduleId, $orderId, $published)
    {
        $lessons = $this->lesson
            ->where('course_module_id', $moduleId)
            ->where('published', $published)
            ->where('order_id', $orderId)
            ->count();

        return $lessons == 0;
    }

    public function checkIfLessonSortValid($lessons)
    {
        $lessonStatuses = array_column($lessons, 'published');
        $publishedLessonStatuses = array_slice($lessonStatuses, 0, array_sum($lessonStatuses));

        return array_sum($lessonStatuses) == array_sum($publishedLessonStatuses);
    }
}
