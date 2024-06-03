<?php

namespace App\Repositories\IU;

use App\DataObject\EbookData;
use App\Models\Course;
use App\Models\Ebook;
use App\Models\EbookAccess;
use App\Traits\FileSystemsCloudTrait;
use App\Traits\UrlTrait;

class IuEbookRepository
{
    use FileSystemsCloudTrait, UrlTrait;

    private Ebook $ebook;

    private Course $course;

    private EbookAccess $ebookAccess;

    public function __construct(Ebook $ebook, Course $course, EbookAccess $ebookAccess)
    {
        $this->ebook = $ebook;
        $this->course = $course;
        $this->ebookAccess = $ebookAccess;
    }

    public function getByLessonId($lessonId)
    {
        return $this->ebook->where('lesson_id', $lessonId)
            ->with('lesson')
            ->first();
    }

    public function getEbookListPerLevel($courseId, $level, $userId)
    {
        return $this->course
            ->select('id', 'name', 'img', 'price')
            ->where('id', $courseId)
            ->with('courseLevel', function ($query) use ($level, $userId) {
                $query->where('value', $level)
                    ->with('courseModules', function ($query) use ($userId) {
                        $query->select('course_modules.*', 'ea.id as purchased')
                            ->where('has_ebook', true)
                            ->leftJoin('ebook_accesses as ea', function ($query) use ($userId) {
                                $query->on('ea.course_module_id', '=', 'course_modules.id')
                                    ->where('ea.user_id', $userId);
                            });
                    });
            })
            ->first();
    }

    public function assignEbookToUser($userId, $courseModuleId)
    {
        return $this->ebookAccess->create([
            'user_id' => $userId,
            'course_module_id' => $courseModuleId,
        ]);
    }

    public function revokeEbookAccessFromUser($userId, $courseModuleId)
    {
        return $this->ebookAccess
            ->where('user_id', $userId)
            ->where('course_module_id', $courseModuleId)
            ->delete();
    }

    /**
     * @param  string  $directory  | path of ebook directory
     *                             - $directory may hold PDF or series of images
     * @return array
     *               - signed temporary URL
     *               - type
     */
    public function generateS3SignedEbook($directory)
    {
        $singedEbook = [];

        $files = $this->getFiles($directory);
        if (empty($files)) {
            return $singedEbook;
        }

        $ebookType = $this->getUrlExtension($files[0]);

        $ebookMeta = $this->getEbookMeta($ebookType);
        if (empty($ebookMeta)) {
            return $singedEbook;
        }

        $expiration = $this->addTimeToCurrentDate($ebookMeta['expiry_time'], $ebookMeta['expiry_time_unit']);

        $signedTemporaryUrls = [];
        foreach ($files as $file) {
            $signedTemporaryUrls[] = $this->signedTemporaryUrl($file, $expiration);
        }

        $singedEbook = (object) [
            'path' => $signedTemporaryUrls,
            'type' => $ebookType == EbookData::PDF ? EbookData::PDF : EbookData::IMAGE,
        ];

        return $singedEbook;
    }

    public function getEbookMeta($ebookType)
    {
        if (! array_key_exists($ebookType, EbookData::getMeta())) {
            return [];
        }

        return EbookData::getMeta()[$ebookType];
    }
}
