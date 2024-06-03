<?php

namespace App\Repositories\AF;

use App\Models\CourseModule;
use Batch;

class AfCourseModuleRepository
{
    private CourseModule $courseModule;

    public function __construct(CourseModule $courseModule)
    {
        $this->courseModule = $courseModule;
    }

    public function createModule(
        $courseId,
        $levelId,
        $orderId, // listing order of modules
        $name,
        $description,
        $thumbnail,
        $videoPreview,
        $ebookPrice,
    ) {

        return $this->courseModule->create([
            'course_id' => $courseId,
            'course_level_id' => $levelId,
            'order_id' => $orderId,
            'name' => $name,
            'description' => $description,
            'img' => $thumbnail,
            'video_preview' => $videoPreview,
            'ebook_price' => $ebookPrice,
        ]);

    }

    public function getModule($id, $levelId, $courseId)
    {
        return $this->courseModule
            ->where('id', $id)
            ->where('course_id', $courseId)
            ->where('course_level_id', $levelId)
            ->first();
    }

    public function updateModule(
        $id,
        $orderId,
        $name,
        $description,
        $thumbnail,
        $videoPreview,
        $ebookPrice
    ) {
        return $this->courseModule
            ->where('id', $id)
            ->update([
                'order_id' => $orderId,
                'name' => $name,
                'description' => $description,
                'img' => $thumbnail,
                'video_preview' => $videoPreview,
                'ebook_price' => $ebookPrice,
            ]);
    }

    public function checkIfAllModulesExist($moduleIds, $levelId, $courseId)
    {
        $modules = $this->courseModule
            ->whereIn('id', $moduleIds)
            ->where('course_id', $courseId)
            ->where('course_level_id', $levelId)
            ->count();

        return $modules == count($moduleIds);
    }

    public function sortModule($modules)
    {
        return Batch::update(new CourseModule, $modules, 'id');
    }

    public function deleteModule($ids)
    {
        return $this->courseModule->whereIn('id', $ids)->delete();
    }

    public static function getThumbnailS3StoragePath()
    {
        return 'courses/modules/thumbnails/';
    }

    public function getModuleById($moduleId)
    {
        return $this->courseModule
            ->where('id', $moduleId)
            ->first();
    }

    public function updateModuleHasEbook($moduleId, $hasEbook)
    {
        return $this->courseModule
            ->where('id', $moduleId)
            ->update([
                'has_ebook' => $hasEbook,
            ]);
    }

    public function checkIfAnyModuleHasEbook($levelId)
    {
        $modules = $this->courseModule
            ->where('course_level_id', $levelId)
            ->where('has_ebook', 1)
            ->count();

        return $modules > 0;
    }
}
