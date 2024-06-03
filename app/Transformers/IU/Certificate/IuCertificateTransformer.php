<?php

namespace App\Transformers\IU\Certificate;

use App\DataObject\CertificateEntityData;
use App\Models\Certificate;
use App\Transformers\IU\CourseHierarchy\IuCourseHierarchyTransformer;
use App\Transformers\IU\CourseHierarchy\IuCourseLevelHierarchyTransformer;
use App\Transformers\IU\CourseHierarchy\IuCourseModuleHierarchyTransformer;
use League\Fractal\TransformerAbstract;

class IuCertificateTransformer extends TransformerAbstract
{
    /**
     * List of resources to automatically include
     */
    protected array $defaultIncludes = [
        'hierarchy',
    ];

    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform(Certificate $certificate)
    {
        return [
            'id' => $certificate->id,
            'type' => $certificate->entity_type,
            'created_at' => $certificate->created_at,
            'updated_at' => $certificate->updated_at,
        ];
    }

    public function includeHierarchy($certificate)
    {
        if ($certificate->entity_type == CertificateEntityData::ENTITY_COURSE) {
            return $this->item($certificate->entity, new IuCourseHierarchyTransformer());
        } elseif ($certificate->entity_type == CertificateEntityData::ENTITY_COURSE_MODULE) {
            return $this->item($certificate->entity, new IuCourseModuleHierarchyTransformer());
        } elseif ($certificate->entity_type == CertificateEntityData::ENTITY_COURSE_LEVEL) {
            return $this->item($certificate->entity, new IuCourseLevelHierarchyTransformer());
        }
    }
}
