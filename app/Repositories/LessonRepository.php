<?php

namespace App\Repositories;

use App\DataObject\QuizData;
use App\DataObject\UserProgressData;
use App\Jobs\IU\UpdateUserEntityProgressJob;
use App\Models\CourseLevel;
use App\Models\Lesson;
use App\Models\LessonNote;
use App\Models\UserProgress;
use App\Models\VideoProgress;
use App\Repositories\IU\IuQuizRepository;
use Illuminate\Support\Facades\DB;

class LessonRepository
{
    private Lesson $lesson;

    private LessonNote $lessonNote;

    private VideoProgress $videoProgress;

    private UserProgress $userProgress;

    private IuQuizRepository $iuQuizRepository;

    public function __construct(
        Lesson $lesson,
        LessonNote $lessonNote,
        VideoProgress $videoProgress,
        UserProgress $userProgress,
        IuQuizRepository $iuQuizRepository
    ) {
        $this->lesson = $lesson;
        $this->lessonNote = $lessonNote;
        $this->videoProgress = $videoProgress;
        $this->userProgress = $userProgress;
        $this->iuQuizRepository = $iuQuizRepository;
    }

    public function get($courseId, $lessonId)
    {
        return $this->lesson
            ->where('course_id', $courseId)
            ->where('id', $lessonId)
            ->first();
    }

    public function getForUser($userId, $courseId, $lessonId)
    {
        $lessonQuery = $this->lesson
            ->select(
                'lessons.*',
                'vp.timestamp as video_progress',
                'up.progress as progress',
                'ln.content as notes_text',
                'ln.updated_at as notes_updated_at',
                'qz.id as quiz_id',
                'cl.value as level',
                'cm.name as course_module_name',
                'module_up.progress as module_progress',
                'module_qz.id as has_module_exam',
            )
            ->where('lessons.course_id', $courseId)
            ->where('lessons.id', $lessonId)
            ->leftJoin('course_modules as cm', 'cm.id', '=', 'lessons.course_module_id')
            ->leftJoin('course_levels as cl', 'cm.course_level_id', '=', 'cl.id')
            ->leftJoin('video_progress as vp', function ($query) use ($userId) {
                $query->on('vp.lesson_id', '=', 'lessons.id')
                    ->where('vp.user_id', $userId);
            })
            ->leftJoin('user_progress as up', function ($query) use ($userId) {
                $query->on('up.entity_id', '=', 'lessons.id')
                    ->where('up.user_id', $userId)
                    ->where('up.entity_type', UserProgressData::ENTITY_LESSON);
            })
            ->leftJoin('user_progress as module_up', function ($query) use ($userId) {
                $query->on('module_up.entity_id', '=', 'cm.id')
                    ->where('module_up.user_id', $userId)
                    ->where('module_up.entity_type', UserProgressData::ENTITY_COURSE_MODULE);
            })
            ->leftJoin('quizzes as qz', function ($query) {
                $query->on('qz.entity_id', '=', 'lessons.id')
                    ->where('qz.entity_type', QuizData::ENTITY_LESSON);
            })
            ->leftJoin('quizzes as module_qz', function ($query) {
                $query->on('module_qz.entity_id', '=', 'cm.id')
                    ->where('module_qz.entity_type', QuizData::ENTITY_COURSE_MODULE);
            })
            ->leftJoin('lesson_notes as ln', function ($query) use ($userId) {
                $query->on('ln.lesson_id', '=', 'lessons.id')
                    ->where('ln.user_id', $userId);
            });

        return $lessonQuery->first();
    }

    public function getLessonNote($userId, $lessonId)
    {
        return $this->lessonNote->where('lesson_id', $lessonId)
            ->where('user_id', $userId)
            ->first();
    }

    public function updateLessonNote($userId, $lessonId, $content)
    {
        return $this->lessonNote->updateOrCreate(
            [
                'user_id' => $userId,
                'lesson_id' => $lessonId,
            ],
            [
                'content' => $content,
            ]
        );
    }

    public function updateVideoProgress($userId, $lessonId, $timestamp)
    {
        $this->videoProgress->updateOrCreate(
            [
                'user_id' => $userId,
                'lesson_id' => $lessonId,
            ],
            [
                'timestamp' => $timestamp,
            ]
        );
    }

    public function updateLessonProgressOnVideoView($userId, $lessonId)
    {
        $lessonProgress = $this->userProgress->select('user_progress.*', 'lessons.course_module_id')
            ->where('entity_id', $lessonId)
            ->where('entity_type', UserProgressData::ENTITY_LESSON)
            ->where('user_id', $userId)
            ->leftJoin('lessons', 'lessons.id', '=', 'user_progress.entity_id')
            ->first();

        if ($lessonProgress && $lessonProgress->progress >= UserProgressData::MIN_PROGRESS_TO_ACCESS_QUIZ) {
            return $lessonProgress->progress;
        }

        $lessonHasQuiz = $this->iuQuizRepository->getEntityHasQuiz($lessonId, QuizData::ENTITY_LESSON);
        $progress = $lessonHasQuiz ? UserProgressData::MIN_PROGRESS_TO_ACCESS_QUIZ : UserProgressData::COMPLETED_PROGRESS;

        if ($lessonProgress) {
            $lessonProgress->progress = $progress;
            $lessonProgress->save();
        } else {
            $lessonProgress = $this->userProgress->create([
                'entity_id' => $lessonId,
                'entity_type' => UserProgressData::ENTITY_LESSON,
                'user_id' => $userId,
                'progress' => $progress,
            ]);
            $lesson = $this->lesson->find($lessonId);
            $lessonProgress->course_module_id = $lesson->course_module_id;
        }

        UpdateUserEntityProgressJob::dispatch($userId, $lessonProgress->course_module_id, UserProgressData::ENTITY_COURSE_MODULE)->onQueue('high');

        return $lessonProgress->progress;
    }

    public static function getUserLevelProgress($userId, $courseId, $value)
    {
        return CourseLevel::select('up.progress')
            ->where('course_id', $courseId)
            ->where('value', $value)
            ->leftJoin('user_progress as up', function ($query) use ($userId) {
                $query->on('up.entity_id', '=', 'course_levels.id')
                    ->where('up.user_id', $userId)
                    ->where('up.entity_type', UserProgressData::ENTITY_COURSE_LEVEL);
            })
            ->first();
    }

    public function getUserPreviousLesson($userId, $lesson)
    {
        return $this->lesson
            ->select('lessons.*', 'up.progress as progress')
            ->where('lessons.course_id', $lesson->course_id)
            ->where('lessons.course_module_id', $lesson->course_module_id)
            ->where('lessons.order_id', $lesson->order_id - 1)
            ->leftJoin('user_progress as up', function ($query) use ($userId) {
                $query->on('up.entity_id', '=', 'lessons.id')
                    ->where('up.user_id', $userId)
                    ->where('up.entity_type', UserProgressData::ENTITY_LESSON);
            })
            ->first();
    }

    public function getOngoingLessons($userId, $courseId, $firstModuleId = null)
    {
        return $this->lesson
            ->select(
                'lessons.id',
                'lessons.course_id',
                'lessons.course_module_id',
                'lessons.name as lesson_name',
                'lessons.order_id as order_id',
                'lessons.published',
                'cm.name as module_name',
                'cm.course_level_id',
                'cl.name as level_name'
            )
            ->where('lessons.course_id', $courseId)
            ->when($firstModuleId, function ($query) use ($firstModuleId) {
                $query->where('lessons.course_module_id', $firstModuleId)
                    ->where('lessons.order_id', 1);
            })
            ->when(! $firstModuleId, function ($query) use ($userId) {
                $query->addSelect('up.progress as progress')
                    ->rightJoin('user_progress as up', function ($query) use ($userId) {
                        $query->on('up.entity_id', '=', 'lessons.id')
                            ->where('up.user_id', $userId)
                            ->where('up.entity_type', UserProgressData::ENTITY_LESSON)
                            ->where('up.progress', '<', UserProgressData::COMPLETED_PROGRESS);
                    });
            })
            ->join('course_modules as cm', function ($query) {
                $query->on('cm.id', '=', 'lessons.course_module_id');
            })
            ->join('course_levels as cl', function ($query) {
                $query->on('cl.id', '=', 'cm.course_level_id');
            })
            ->get();
    }

    public function getAllLessonsOfModule($userId, $courseId, $courseModuleId)
    {
        return $this->lesson->select('lessons.*', 'up.progress as progress', 'uqz.user_quiz_failed_id', 'qz.id as quiz_id')
            ->where('lessons.course_id', $courseId)
            ->where('lessons.course_module_id', $courseModuleId)
            ->leftJoin('user_progress as up', function ($query) use ($userId) {
                $query->on('up.entity_id', '=', 'lessons.id')
                    ->where('up.entity_type', UserProgressData::ENTITY_LESSON)
                    ->where('up.user_id', $userId);
            })
            ->leftJoinSub(
                DB::table('user_quizzes as uqz')
                    ->select('entity_id', DB::raw('MAX(id) as user_quiz_failed_id'))
                    ->where('user_id', $userId)
                    ->where('status', QuizData::STATUS_COMPLETED)
                    ->where('score', '<', QuizData::DEFAULT_PASSING_SCORE)
                    ->where('entity_type', QuizData::ENTITY_LESSON)
                    ->groupBy('entity_id'), 'uqz', function ($join) {
                        $join->on('lessons.id', '=', 'uqz.entity_id');
                    }
            )
            ->leftJoin('quizzes as qz', function ($query) {
                $query->on('qz.entity_id', '=', 'lessons.id')
                    ->where('qz.entity_type', QuizData::ENTITY_LESSON);
            })
            ->get();
    }
}
