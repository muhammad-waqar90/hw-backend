<?php

namespace App\Traits;

use App\DataObject\CertificateEntityData;

trait HierarchyTrait
{
    /**
     * get the course name
     *
     * @return entityname
     */
    public function getCourseHierarchyNameCertificate($hierarchy, $type)
    {
        if ($type === CertificateEntityData::ENTITY_COURSE) {
            return $hierarchy['hierarchy_name'];
        } elseif ($type === CertificateEntityData::ENTITY_COURSE_LEVEL) {
            return $hierarchy['hierarchy_name'].' - '.$hierarchy['parent']['hierarchy_name'];
        } elseif ($type === CertificateEntityData::ENTITY_COURSE_MODULE) {
            return $hierarchy['hierarchy_name'].' - '.$hierarchy['parent']['hierarchy_name'].' - '.$hierarchy['parent']['parent']['hierarchy_name'];
        }
    }

    public function getEntityHierarchyNameExport($hierarchy)
    {
        $transformedEntityName = '';
        while (array_key_exists('hierarchy_name', $hierarchy)) {
            $transformedEntityName = $hierarchy['hierarchy_name'].' '.$transformedEntityName;
            $hierarchy = $hierarchy['parent'] ?? [];
        }

        return $transformedEntityName;
    }
}
