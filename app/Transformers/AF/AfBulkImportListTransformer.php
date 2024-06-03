<?php

namespace App\Transformers\AF;

use App\Models\BulkImportStatus;
use League\Fractal\TransformerAbstract;

class AfBulkImportListTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform(BulkImportStatus $bulkImportStatus)
    {
        return [
            'id' => $bulkImportStatus->id,
            'uploaded_by' => $bulkImportStatus->admin->name.'('.$bulkImportStatus->admin->adminProfile->email.')',
            'status' => $bulkImportStatus->status,
            'errors' => $bulkImportStatus->errors,
            'updated_at' => $bulkImportStatus->updated_at,
        ];
    }
}
